#!/bin/bash
###############################################################################
# Backblaze B2 Off-Site Encrypted Backup Script for Yujix API
#
# This script:
# 1. Runs local backup (backup-yujix.sh)
# 2. Encrypts backups with GPG
# 3. Uploads to Backblaze B2
# 4. Manages B2 retention (30 days)
#
# Prerequisites:
# - Backblaze B2 account with application key
# - b2-cli installed: pip3 install b2
# - GPG key generated for encryption
#
# Setup:
# 1. Install b2-cli: pip3 install b2
# 2. Generate GPG key: gpg --generate-key (use admin@yujix.com)
# 3. Authorize B2: b2 authorize-account <key-id> <application-key>
# 4. Copy to server: /root/backup-to-b2.sh
# 5. Make executable: chmod +x /root/backup-to-b2.sh
# 6. Add to crontab (replace daily local backup):
#    0 2 * * * /root/backup-to-b2.sh >> /var/log/yujix-backup.log 2>&1
###############################################################################

set -e

# Configuration
BACKUP_DIR="/var/backups/yujix"
B2_BUCKET="yujix-production-backups"
GPG_RECIPIENT="admin@yujix.com"
B2_RETENTION_DAYS=30
LOG_FILE="/var/log/yujix-backup.log"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

log "==========================================="
log "Backblaze B2 Encrypted Backup Started"
log "==========================================="

# Check if b2 CLI is installed
if ! command -v b2 &> /dev/null; then
    error "b2 CLI is not installed. Install with: pip3 install b2"
    exit 1
fi

# Check if GPG is installed
if ! command -v gpg &> /dev/null; then
    error "GPG is not installed. Install with: apt install gnupg"
    exit 1
fi

# Check if GPG key exists
if ! gpg --list-keys "$GPG_RECIPIENT" &> /dev/null; then
    error "GPG key for $GPG_RECIPIENT not found. Generate with: gpg --generate-key"
    exit 1
fi

# Check if B2 is authorized
if ! b2 get-account-info &> /dev/null; then
    error "B2 is not authorized. Run: b2 authorize-account <key-id> <application-key>"
    exit 1
fi

# Step 1: Run local backup
log "Step 1: Running local backup..."
if [ -f "/root/backup-yujix.sh" ]; then
    /root/backup-yujix.sh
else
    error "Local backup script not found at /root/backup-yujix.sh"
    exit 1
fi

# Get the latest backup timestamp
LATEST_DB_BACKUP=$(ls -t "$BACKUP_DIR/daily/database/yujix-db-"*.sql.gz | head -1)
LATEST_FILES_BACKUP=$(ls -t "$BACKUP_DIR/daily/files/yujix-files-"*.tar.gz | head -1)

if [ ! -f "$LATEST_DB_BACKUP" ]; then
    error "No database backup found"
    exit 1
fi

if [ ! -f "$LATEST_FILES_BACKUP" ]; then
    error "No files backup found"
    exit 1
fi

# Extract timestamp from filename
TIMESTAMP=$(basename "$LATEST_DB_BACKUP" | sed 's/yujix-db-//;s/.sql.gz//')
log "Backup timestamp: $TIMESTAMP"

# Step 2: Encrypt database backup
log "Step 2: Encrypting database backup..."
DB_ENCRYPTED="${LATEST_DB_BACKUP}.gpg"
gpg --encrypt --recipient "$GPG_RECIPIENT" --trust-model always --output "$DB_ENCRYPTED" "$LATEST_DB_BACKUP"

DB_ORIG_SIZE=$(du -h "$LATEST_DB_BACKUP" | cut -f1)
DB_ENC_SIZE=$(du -h "$DB_ENCRYPTED" | cut -f1)
log "Database encrypted: $DB_ORIG_SIZE -> $DB_ENC_SIZE"

# Step 3: Encrypt files backup
log "Step 3: Encrypting files backup..."
FILES_ENCRYPTED="${LATEST_FILES_BACKUP}.gpg"
gpg --encrypt --recipient "$GPG_RECIPIENT" --trust-model always --output "$FILES_ENCRYPTED" "$LATEST_FILES_BACKUP"

FILES_ORIG_SIZE=$(du -h "$LATEST_FILES_BACKUP" | cut -f1)
FILES_ENC_SIZE=$(du -h "$FILES_ENCRYPTED" | cut -f1)
log "Files encrypted: $FILES_ORIG_SIZE -> $FILES_ENC_SIZE"

# Step 4: Upload database to B2
log "Step 4: Uploading database to Backblaze B2..."
B2_DB_PATH="database/yujix-db-${TIMESTAMP}.sql.gz.gpg"

if b2 file upload --no-progress "$B2_BUCKET" "$DB_ENCRYPTED" "$B2_DB_PATH"; then
    log "✅ Database uploaded to B2: $B2_DB_PATH"
else
    error "Failed to upload database to B2"
    rm -f "$DB_ENCRYPTED" "$FILES_ENCRYPTED"
    exit 1
fi

# Step 5: Upload files to B2
log "Step 5: Uploading files to Backblaze B2..."
B2_FILES_PATH="files/yujix-files-${TIMESTAMP}.tar.gz.gpg"

if b2 file upload --no-progress "$B2_BUCKET" "$FILES_ENCRYPTED" "$B2_FILES_PATH"; then
    log "✅ Files uploaded to B2: $B2_FILES_PATH"
else
    error "Failed to upload files to B2"
    rm -f "$DB_ENCRYPTED" "$FILES_ENCRYPTED"
    exit 1
fi

# Step 6: Cleanup local encrypted files
log "Step 6: Cleaning up local encrypted files..."
rm -f "$DB_ENCRYPTED" "$FILES_ENCRYPTED"
log "✅ Local encrypted files removed"

# Step 7: Cleanup old B2 backups (keep last 30 days)
log "Step 7: Cleaning up old B2 backups (retention: ${B2_RETENTION_DAYS} days)..."

# Get cutoff timestamp (30 days ago)
CUTOFF_DATE=$(date -d "${B2_RETENTION_DAYS} days ago" +"%Y%m%d" 2>/dev/null || date -v-${B2_RETENTION_DAYS}d +"%Y%m%d")

# List and delete old database backups
b2 ls --recursive "b2://$B2_BUCKET/database/" 2>/dev/null | while read -r line; do
    FILENAME=$(echo "$line" | awk '{print $NF}')
    # Extract date from filename (YYYYMMDD)
    FILE_DATE=$(echo "$FILENAME" | grep -oP '\d{8}' | head -1)

    if [ -n "$FILE_DATE" ] && [ "$FILE_DATE" -lt "$CUTOFF_DATE" ]; then
        log "Deleting old backup: $FILENAME (date: $FILE_DATE)"
        FILE_ID=$(b2 ls --long --recursive "$B2_BUCKET" "$FILENAME" | awk '{print $1}')
        if [ -n "$FILE_ID" ]; then
            b2 delete-file-version "$FILENAME" "$FILE_ID" 2>/dev/null || true
        fi
    fi
done

# List and delete old files backups
b2 ls --recursive "b2://$B2_BUCKET/files/" 2>/dev/null | while read -r line; do
    FILENAME=$(echo "$line" | awk '{print $NF}')
    # Extract date from filename (YYYYMMDD)
    FILE_DATE=$(echo "$FILENAME" | grep -oP '\d{8}' | head -1)

    if [ -n "$FILE_DATE" ] && [ "$FILE_DATE" -lt "$CUTOFF_DATE" ]; then
        log "Deleting old backup: $FILENAME (date: $FILE_DATE)"
        FILE_ID=$(b2 ls --long --recursive "$B2_BUCKET" "$FILENAME" | awk '{print $1}')
        if [ -n "$FILE_ID" ]; then
            b2 delete-file-version "$FILENAME" "$FILE_ID" 2>/dev/null || true
        fi
    fi
done

log "✅ B2 cleanup complete"

# Step 8: Backup summary
log ""
log "==========================================="
log "Backup Summary"
log "==========================================="
log "Timestamp: $TIMESTAMP"
log "Database: $DB_ORIG_SIZE (encrypted: $DB_ENC_SIZE)"
log "Files: $FILES_ORIG_SIZE (encrypted: $FILES_ENC_SIZE)"
log "B2 Bucket: $B2_BUCKET"
log "Retention: ${B2_RETENTION_DAYS} days"
log ""

# Count B2 backups
B2_DB_COUNT=$(b2 ls --recursive "b2://$B2_BUCKET/database/" 2>/dev/null | wc -l)
B2_FILES_COUNT=$(b2 ls --recursive "b2://$B2_BUCKET/files/" 2>/dev/null | wc -l)
log "B2 database backups: $B2_DB_COUNT"
log "B2 files backups: $B2_FILES_COUNT"
log ""
log "==========================================="
log "✅ Encrypted off-site backup completed successfully"
log "==========================================="

exit 0
