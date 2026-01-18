#!/bin/bash
###############################################################################
# Zero-Downtime Atomic Deployment Script for Yujix API
# Uses releases directory pattern with atomic symlink switching
###############################################################################

set -e

# Configuration
DEPLOY_BASE="/var/www/yujix"
RELEASES_DIR="${DEPLOY_BASE}/releases"
SHARED_DIR="${DEPLOY_BASE}/shared"
CURRENT_LINK="${DEPLOY_BASE}/current"
DEPLOY_LOG="${DEPLOY_BASE}/.deploy/deployment.log"
KEEP_RELEASES=5
HEALTH_CHECK_URL="https://yujix.com/api/health"
HEALTH_CHECK_RETRIES=5
HEALTH_CHECK_DELAY=2

# Timestamp for this release
RELEASE_NAME=$(date +"%Y%m%d-%H%M%S")
RELEASE_PATH="${RELEASES_DIR}/${RELEASE_NAME}"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Logging function
log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$DEPLOY_LOG"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$DEPLOY_LOG"
}

warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$DEPLOY_LOG"
}

# Health check function
health_check() {
    local url=$1
    local retries=$2
    local delay=$3

    for i in $(seq 1 $retries); do
        log "Health check attempt $i/$retries..."

        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -m 5 "$url" || echo "000")

        if [ "$HTTP_CODE" -eq 200 ]; then
            log "Health check passed (HTTP $HTTP_CODE)"
            return 0
        fi

        if [ $i -lt $retries ]; then
            warn "Health check failed (HTTP $HTTP_CODE), retrying in ${delay}s..."
            sleep $delay
        fi
    done

    error "Health check failed after $retries attempts"
    return 1
}

# Rollback function
rollback() {
    error "Deployment failed! Initiating rollback..."

    # Find previous release
    PREVIOUS_RELEASE=$(ls -t "$RELEASES_DIR" 2>/dev/null | grep -v "^${RELEASE_NAME}$" | head -1)

    if [ -z "$PREVIOUS_RELEASE" ]; then
        error "No previous release found for rollback!"
        error "Manual intervention required!"
        return 1
    fi

    log "Rolling back to: $PREVIOUS_RELEASE"
    ln -sfn "${RELEASES_DIR}/${PREVIOUS_RELEASE}" "$CURRENT_LINK"

    # Reload PHP-FPM
    sudo systemctl reload php8.2-fpm

    # Restart workers with old release
    sudo supervisorctl restart yujix:* || true
    sleep 3

    log "Rollback complete"

    # Health check after rollback
    if health_check "$HEALTH_CHECK_URL" 3 2; then
        log "Application restored successfully"
    else
        error "Application still unhealthy after rollback!"
        error "Manual intervention required!"
    fi

    # Clean up failed release
    if [ -d "$RELEASE_PATH" ]; then
        log "Removing failed release: $RELEASE_NAME"
        rm -rf "$RELEASE_PATH"
    fi
}

# Trap errors and rollback
trap 'rollback' ERR

###############################################################################
# DEPLOYMENT STEPS
###############################################################################

log "=========================================="
log "Zero-Downtime Deployment Started"
log "Release: ${RELEASE_NAME}"
log "=========================================="

# Step 1: Create directory structure
log "Step 1: Creating release directory..."
mkdir -p "$RELEASES_DIR"
mkdir -p "${DEPLOY_BASE}/.deploy"

# Ensure shared directories exist
mkdir -p "$SHARED_DIR/storage"/{app,framework,logs}
mkdir -p "$SHARED_DIR/storage/framework"/{cache,sessions,views}
mkdir -p "$SHARED_DIR/storage/app/firebase"

# Ensure shared .env exists
if [ ! -f "${SHARED_DIR}/.env" ]; then
    error "Shared .env file not found at ${SHARED_DIR}/.env"
    exit 1
fi

# Step 2: Clone code into new release
log "Step 2: Cloning code to release directory..."
git clone --depth 1 --branch main git@gitlab.com:ongoingcloud/ongoing-meet-api.git "$RELEASE_PATH"

cd "$RELEASE_PATH"

# Step 3: Create symlinks to shared resources
log "Step 3: Creating symlinks to shared resources..."
rm -rf "${RELEASE_PATH}/storage"
ln -sfn "${SHARED_DIR}/storage" "${RELEASE_PATH}/storage"

rm -f "${RELEASE_PATH}/.env"
ln -sfn "${SHARED_DIR}/.env" "${RELEASE_PATH}/.env"

# Step 4: Install PHP dependencies
log "Step 4: Installing Composer dependencies..."
composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --quiet

# Step 5: Assets (pre-built and committed)
log "Step 5: Using pre-built assets from repository"

# Step 6: Set permissions
log "Step 6: Setting permissions..."
chown -R deploy:www-data "$RELEASE_PATH"
chmod -R 755 "$RELEASE_PATH"
chmod -R 775 "${RELEASE_PATH}/bootstrap/cache"

# Ensure shared storage has correct permissions
chown -R deploy:www-data "${SHARED_DIR}/storage"
find "${SHARED_DIR}/storage" -type d -exec chmod 775 {} \; 2>/dev/null || true
find "${SHARED_DIR}/storage" -type f -exec chmod 664 {} \; 2>/dev/null || true

# Step 7: Run database migrations (BEFORE switching!)
log "Step 7: Running database migrations..."
php artisan migrate --force --no-interaction

# Step 8: Clear and cache configuration (in new release)
log "Step 8: Optimizing release..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

# Step 9: Atomic symlink swap
log "Step 9: Switching to new release (ATOMIC OPERATION)..."

# This is the critical moment - atomic symlink swap
ln -sfn "$RELEASE_PATH" "${CURRENT_LINK}.tmp"
mv -Tf "${CURRENT_LINK}.tmp" "$CURRENT_LINK"

log "Symlink switched to new release"

# Step 10: Reload PHP-FPM gracefully (NOT restart!)
log "Step 10: Reloading PHP-FPM gracefully..."
sudo systemctl reload php8.2-fpm

log "PHP-FPM reloaded"

# Step 11: Force restart queue workers via supervisor
log "Step 11: Restarting queue workers via supervisor..."
sudo supervisorctl restart yujix:* || true

# Wait for workers to start with new release
sleep 5

cd "$CURRENT_LINK"
WORKER_COUNT=$(sudo supervisorctl status yujix:* | grep RUNNING | wc -l)
log "Active queue workers: $WORKER_COUNT"

# Step 12: Post-deployment health check
log "Step 12: Running health checks..."
sleep 2  # Brief pause for services to stabilize

if ! health_check "$HEALTH_CHECK_URL" "$HEALTH_CHECK_RETRIES" "$HEALTH_CHECK_DELAY"; then
    error "Health check failed after deployment"
    rollback
    exit 1
fi

log "Health checks passed"

# Step 13: Cleanup old releases
log "Step 13: Cleaning up old releases..."
cd "$RELEASES_DIR"
RELEASES_TO_DELETE=$(ls -t | tail -n +$((KEEP_RELEASES + 1)))

if [ -n "$RELEASES_TO_DELETE" ]; then
    for old_release in $RELEASES_TO_DELETE; do
        log "Removing old release: $old_release"
        rm -rf "${RELEASES_DIR}/${old_release}"
    done
else
    log "No old releases to clean up"
fi

log "=========================================="
log "Deployment Completed Successfully!"
log "Release: ${RELEASE_NAME}"
log "Current release: $(readlink $CURRENT_LINK)"
log "=========================================="

# Disable rollback trap on success
trap - ERR

exit 0
