#!/bin/bash
###############################################################################
# Restore from Backblaze B2 Encrypted Backups
#
# This script:
# 1. Lists available B2 backups
# 2. Downloads selected backup from B2
# 3. Decrypts with GPG
# 4. Restores database and files
#
# Usage:
#   ./restore-from-b2.sh                    # List available backups
#   ./restore-from-b2.sh <timestamp>        # Restore specific backup
#   Example: ./restore-from-b2.sh 20260111-020000
#
# Prerequisites:
# - b2-cli installed and authorized
# - GPG key with private key available
###############################################################################

set -e

# Configuration
BACKUP_DIR="/var/backups/yujix"
B2_BUCKET="yujix-production-backups"
GPG_RECIPIENT="admin@yujix.com"
DB_NAME="yujix_db"
DB_USER="yujix_user"
SHARED_DIR="/var/www/yujix/shared"
TEMP_DIR="/tmp/b2-restore-$$"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"
}

warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

cleanup() {
    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
    fi
}

trap cleanup EXIT

# Check prerequisites
if ! command -v b2 &> /dev/null; then
    error "b2 CLI is not installed. Install with: pip3 install b2"
    exit 1
fi

if ! command -v gpg &> /dev/null; then
    error "GPG is not installed. Install with: apt install gnupg"
    exit 1
fi

if ! b2 get-account-info &> /dev/null; then
    error "B2 is not authorized. Run: b2 authorize-account <key-id> <application-key>"
    exit 1
fi

# Check if timestamp provided
if [ -z "$1" ]; then
    echo "==========================================="
    echo "Available Backups in Backblaze B2"
    echo "==========================================="
    echo ""
    echo "Database Backups:"
    echo "-----------------"
    b2 ls --recursive "b2://$B2_BUCKET/database/" 2>/dev/null | grep "yujix-db-" | while read -r line; do
        FILENAME=$(echo "$line" | awk '{print $NF}')
        TIMESTAMP=$(echo "$FILENAME" | sed 's/.*yujix-db-//;s/.sql.gz.gpg//')
        SIZE=$(echo "$line" | awk '{print $3}')
        DATE=$(echo "$line" | awk '{print $4, $5}')
        echo "  - $TIMESTAMP (Size: $SIZE, Date: $DATE)"
    done
    echo ""
    echo "Files Backups:"
    echo "--------------"
    b2 ls --recursive "b2://$B2_BUCKET/files/" 2>/dev/null | grep "yujix-files-" | while read -r line; do
        FILENAME=$(echo "$line" | awk '{print $NF}')
        TIMESTAMP=$(echo "$FILENAME" | sed 's/.*yujix-files-//;s/.tar.gz.gpg//')
        SIZE=$(echo "$line" | awk '{print $3}')
        DATE=$(echo "$line" | awk '{print $4, $5}')
        echo "  - $TIMESTAMP (Size: $Size, Date: $DATE)"
    done
    echo ""
    echo "==========================================="
    echo "Usage: $0 <timestamp>"
    echo "Example: $0 20260111-020000"
    echo "==========================================="
    exit 0
fi

TIMESTAMP="$1"

# Verify backup exists in B2
DB_B2_PATH="database/yujix-db-${TIMESTAMP}.sql.gz.gpg"
FILES_B2_PATH="files/yujix-files-${TIMESTAMP}.tar.gz.gpg"

if ! b2 ls --recursive "b2://$B2_BUCKET/$DB_B2_PATH" 2>/dev/null | grep -q "yujix-db-${TIMESTAMP}"; then
    error "Database backup not found in B2: $DB_B2_PATH"
    echo "Run '$0' without arguments to list available backups"
    exit 1
fi

if ! b2 ls --recursive "b2://$B2_BUCKET/$FILES_B2_PATH" 2>/dev/null | grep -q "yujix-files-${TIMESTAMP}"; then
    error "Files backup not found in B2: $FILES_B2_PATH"
    echo "Run '$0' without arguments to list available backups"
    exit 1
fi

log "==========================================="
log "Restore from Backblaze B2 - $(date)"
log "==========================================="
log "Backup timestamp: $TIMESTAMP"
log "Database: $DB_B2_PATH"
log "Files: $FILES_B2_PATH"
log ""

# Confirmation
read -p "⚠️  This will overwrite the current database and files. Continue? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

# Create temp directory
mkdir -p "$TEMP_DIR"

# Step 1: Download database from B2
log ""
log "Step 1: Downloading database from B2..."
DB_ENCRYPTED="$TEMP_DIR/yujix-db-${TIMESTAMP}.sql.gz.gpg"
b2 download-file-by-name "$B2_BUCKET" "$DB_B2_PATH" "$DB_ENCRYPTED"
DB_ENC_SIZE=$(du -h "$DB_ENCRYPTED" | cut -f1)
log "✅ Database downloaded: $DB_ENC_SIZE"

# Step 2: Download files from B2
log ""
log "Step 2: Downloading files from B2..."
FILES_ENCRYPTED="$TEMP_DIR/yujix-files-${TIMESTAMP}.tar.gz.gpg"
b2 download-file-by-name "$B2_BUCKET" "$FILES_B2_PATH" "$FILES_ENCRYPTED"
FILES_ENC_SIZE=$(du -h "$FILES_ENCRYPTED" | cut -f1)
log "✅ Files downloaded: $FILES_ENC_SIZE"

# Step 3: Decrypt database
log ""
log "Step 3: Decrypting database..."
DB_DECRYPTED="$TEMP_DIR/yujix-db-${TIMESTAMP}.sql.gz"
gpg --decrypt --output "$DB_DECRYPTED" "$DB_ENCRYPTED"
DB_DEC_SIZE=$(du -h "$DB_DECRYPTED" | cut -f1)
log "✅ Database decrypted: $DB_DEC_SIZE"

# Step 4: Decrypt files
log ""
log "Step 4: Decrypting files..."
FILES_DECRYPTED="$TEMP_DIR/yujix-files-${TIMESTAMP}.tar.gz"
gpg --decrypt --output "$FILES_DECRYPTED" "$FILES_ENCRYPTED"
FILES_DEC_SIZE=$(du -h "$FILES_DECRYPTED" | cut -f1)
log "✅ Files decrypted: $FILES_DEC_SIZE"

# Read database password from .env
DB_PASSWORD=$(grep DB_PASSWORD "$SHARED_DIR/.env" | cut -d '=' -f2)

# Step 5: Restore database
log ""
log "Step 5: Restoring database..."
gunzip < "$DB_DECRYPTED" | mysql --user="$DB_USER" --password="$DB_PASSWORD"
log "✅ Database restored"

# Step 6: Restore files
log ""
log "Step 6: Restoring files..."

# Create backup of current files before restore
CURRENT_BACKUP="/tmp/yujix-current-backup-$(date +%s).tar.gz"
tar -czf "$CURRENT_BACKUP" "$SHARED_DIR/.env" "$SHARED_DIR/storage" 2>/dev/null || true
log "Current files backed up to: $CURRENT_BACKUP"

# Restore from backup
tar -xzf "$FILES_DECRYPTED" -C /

# Fix permissions
chown -R deploy:www-data "$SHARED_DIR/storage"
find "$SHARED_DIR/storage" -type d -exec chmod 775 {} \; 2>/dev/null || true
find "$SHARED_DIR/storage" -type f -exec chmod 664 {} \; 2>/dev/null || true

log "✅ Files restored"

# Step 7: Clear caches
log ""
log "Step 7: Clearing application caches..."
cd /var/www/yujix/current
php artisan config:clear
php artisan cache:clear
php artisan view:clear
log "✅ Caches cleared"

# Step 8: Restart services
log ""
log "Step 8: Restarting services..."
systemctl reload php8.2-fpm
supervisorctl restart yujix:* 2>/dev/null || true
log "✅ Services restarted"

log ""
log "==========================================="
log "✅ Restore completed successfully"
log "==========================================="
log "Restored from: $TIMESTAMP"
log "Database: $DB_DEC_SIZE"
log "Files: $FILES_DEC_SIZE"
log ""
log "Please verify the application is working correctly:"
log "  https://yujix.com"
log "==========================================="

exit 0
