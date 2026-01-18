#!/bin/bash
###############################################################################
# Instant Rollback Script for Yujix API
# Reverts to previous release in <5 seconds
###############################################################################

set -e

DEPLOY_BASE="/var/www/yujix"
RELEASES_DIR="${DEPLOY_BASE}/releases"
CURRENT_LINK="${DEPLOY_BASE}/current"
ROLLBACK_LOG="${DEPLOY_BASE}/.deploy/rollback.log"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$ROLLBACK_LOG"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$ROLLBACK_LOG"
}

# Get current release
CURRENT_RELEASE=$(basename $(readlink -f "$CURRENT_LINK" 2>/dev/null) 2>/dev/null || echo "none")

log "=========================================="
log "Rollback Initiated"
log "Current release: $CURRENT_RELEASE"
log "=========================================="

# Find previous release
PREVIOUS_RELEASE=$(ls -t "$RELEASES_DIR" 2>/dev/null | grep -v "^${CURRENT_RELEASE}$" | head -1)

if [ -z "$PREVIOUS_RELEASE" ]; then
    error "No previous release found!"
    error "Available releases:"
    ls -t "$RELEASES_DIR" 2>/dev/null || echo "  None"
    exit 1
fi

log "Rolling back to: $PREVIOUS_RELEASE"

# Atomic symlink swap
ln -sfn "${RELEASES_DIR}/${PREVIOUS_RELEASE}" "${CURRENT_LINK}.tmp"
mv -Tf "${CURRENT_LINK}.tmp" "$CURRENT_LINK"

log "Symlink switched to previous release"

# Reload PHP-FPM
log "Reloading PHP-FPM..."
sudo systemctl reload php8.2-fpm

# Restart queue workers
log "Restarting queue workers..."
sudo supervisorctl restart yujix-worker:*

log "=========================================="
log "Rollback Complete!"
log "Active release: $PREVIOUS_RELEASE"
log "=========================================="

# Health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://yujix.com/api/health 2>/dev/null || echo "000")
if [ "$HTTP_CODE" -eq 200 ]; then
    log "✅ Health check passed (HTTP $HTTP_CODE)"
else
    error "⚠️  Health check failed (HTTP $HTTP_CODE)"
    error "Manual verification required"
fi

echo ""
echo "Verify application: https://yujix.com"
echo ""

exit 0
