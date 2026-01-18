#!/bin/bash
###############################################################################
# Automated Backup Script for Yujix API
# Backs up MySQL database and application files
# Retention: 7 daily backups, 4 weekly backups
#
# Setup:
# 1. Copy to server: /root/backup-yujix.sh
# 2. Make executable: chmod +x /root/backup-yujix.sh
# 3. Add to crontab for daily execution:
#    0 2 * * * /root/backup-yujix.sh > /var/log/yujix-backup.log 2>&1
###############################################################################

set -e

# Configuration
BACKUP_DIR="/var/backups/yujix"
DB_NAME="yujix_db"
DB_USER="yujix_user"
APP_DIR="/var/www/yujix/current"
SHARED_DIR="/var/www/yujix/shared"
RETENTION_DAYS=7
RETENTION_WEEKS=4

# Create backup directory structure
mkdir -p "$BACKUP_DIR/daily/database"
mkdir -p "$BACKUP_DIR/daily/files"
mkdir -p "$BACKUP_DIR/weekly/database"
mkdir -p "$BACKUP_DIR/weekly/files"

# Timestamp
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
DAY_OF_WEEK=$(date +"%u")  # 1=Monday, 7=Sunday

echo "==========================================="
echo "Yujix API Backup - $(date)"
echo "==========================================="

# Read database password from .env
DB_PASSWORD=$(grep DB_PASSWORD "$SHARED_DIR/.env" | cut -d '=' -f2)

# 1. Database Backup (daily)
echo "Backing up database..."
mysqldump \
  --user="$DB_USER" \
  --password="$DB_PASSWORD" \
  --single-transaction \
  --routines \
  --triggers \
  --databases "$DB_NAME" \
  | gzip > "$BACKUP_DIR/daily/database/yujix-db-$TIMESTAMP.sql.gz"

DB_BACKUP_SIZE=$(du -h "$BACKUP_DIR/daily/database/yujix-db-$TIMESTAMP.sql.gz" | cut -f1)
echo "✅ Database backup complete: $DB_BACKUP_SIZE"

# 2. Application Files Backup (daily - only critical files)
echo "Backing up application files..."
tar -czf "$BACKUP_DIR/daily/files/yujix-files-$TIMESTAMP.tar.gz" \
  --exclude="storage/logs/*" \
  --exclude="storage/framework/cache/*" \
  --exclude="storage/framework/sessions/*" \
  --exclude="storage/framework/views/*" \
  --exclude="node_modules" \
  --exclude=".git" \
  "$SHARED_DIR/.env" \
  "$SHARED_DIR/storage" \
  "$APP_DIR/public/storage" 2>/dev/null || true

FILES_BACKUP_SIZE=$(du -h "$BACKUP_DIR/daily/files/yujix-files-$TIMESTAMP.tar.gz" | cut -f1)
echo "✅ Files backup complete: $FILES_BACKUP_SIZE"

# 3. Weekly Backup (every Sunday)
if [ "$DAY_OF_WEEK" = "7" ]; then
  echo "Creating weekly backup..."
  cp "$BACKUP_DIR/daily/database/yujix-db-$TIMESTAMP.sql.gz" \
     "$BACKUP_DIR/weekly/database/yujix-db-$TIMESTAMP.sql.gz"
  cp "$BACKUP_DIR/daily/files/yujix-files-$TIMESTAMP.tar.gz" \
     "$BACKUP_DIR/weekly/files/yujix-files-$TIMESTAMP.tar.gz"
  echo "✅ Weekly backup created"
fi

# 4. Cleanup old backups
echo "Cleaning up old backups..."

# Remove daily backups older than retention period
find "$BACKUP_DIR/daily/database" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR/daily/files" -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

# Keep only the specified number of weekly backups
cd "$BACKUP_DIR/weekly/database" && ls -t | tail -n +$((RETENTION_WEEKS + 1)) | xargs -r rm -f
cd "$BACKUP_DIR/weekly/files" && ls -t | tail -n +$((RETENTION_WEEKS + 1)) | xargs -r rm -f

echo "✅ Cleanup complete"

# 5. Backup Summary
echo ""
echo "==========================================="
echo "Backup Summary"
echo "==========================================="
echo "Database backup: $DB_BACKUP_SIZE"
echo "Files backup: $FILES_BACKUP_SIZE"
echo ""
echo "Daily backups retained: $(find "$BACKUP_DIR/daily/database" -name "*.sql.gz" | wc -l)"
echo "Weekly backups retained: $(find "$BACKUP_DIR/weekly/database" -name "*.sql.gz" | wc -l)"
echo ""
echo "Total backup size: $(du -sh "$BACKUP_DIR" | cut -f1)"
echo ""
echo "Backup location: $BACKUP_DIR"
echo "Latest backup: $TIMESTAMP"
echo "==========================================="
echo "✅ Backup completed successfully"
echo "==========================================="

# Optional: Send notification (uncomment if you want email alerts)
# echo "Backup completed: Database=$DB_BACKUP_SIZE, Files=$FILES_BACKUP_SIZE" | mail -s "Yujix Backup Success" admin@yujix.com

exit 0
