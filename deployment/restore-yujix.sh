#!/bin/bash
###############################################################################
# Restore Script for Yujix API
# Restores MySQL database and application files from backup
#
# Usage:
#   ./restore-yujix.sh <backup-timestamp>
#   Example: ./restore-yujix.sh 20260111-020000
###############################################################################

set -e

# Configuration
BACKUP_DIR="/var/backups/yujix"
DB_NAME="yujix_db"
DB_USER="yujix_user"
SHARED_DIR="/var/www/yujix/shared"

# Check if backup timestamp provided
if [ -z "$1" ]; then
  echo "Usage: $0 <backup-timestamp>"
  echo ""
  echo "Available backups:"
  echo ""
  echo "Daily Backups:"
  ls -lh "$BACKUP_DIR/daily/database/" | grep "\.sql\.gz" | awk '{print $9}' | sed 's/yujix-db-/  - /' | sed 's/\.sql\.gz//'
  echo ""
  echo "Weekly Backups:"
  ls -lh "$BACKUP_DIR/weekly/database/" | grep "\.sql\.gz" | awk '{print $9}' | sed 's/yujix-db-/  - /' | sed 's/\.sql\.gz//'
  exit 1
fi

TIMESTAMP="$1"
DB_BACKUP_FILE=""
FILES_BACKUP_FILE=""

# Find backup files (check both daily and weekly)
for TYPE in "daily" "weekly"; do
  if [ -f "$BACKUP_DIR/$TYPE/database/yujix-db-$TIMESTAMP.sql.gz" ]; then
    DB_BACKUP_FILE="$BACKUP_DIR/$TYPE/database/yujix-db-$TIMESTAMP.sql.gz"
    FILES_BACKUP_FILE="$BACKUP_DIR/$TYPE/files/yujix-files-$TIMESTAMP.tar.gz"
    break
  fi
done

if [ -z "$DB_BACKUP_FILE" ]; then
  echo "❌ Backup not found for timestamp: $TIMESTAMP"
  exit 1
fi

echo "==========================================="
echo "Yujix API Restore - $(date)"
echo "==========================================="
echo "Database backup: $DB_BACKUP_FILE"
echo "Files backup: $FILES_BACKUP_FILE"
echo ""

# Confirmation
read -p "⚠️  This will overwrite the current database and files. Continue? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
  echo "Restore cancelled."
  exit 0
fi

# Read database password from .env
DB_PASSWORD=$(grep DB_PASSWORD "$SHARED_DIR/.env" | cut -d '=' -f2)

# 1. Restore Database
echo ""
echo "Restoring database..."
gunzip < "$DB_BACKUP_FILE" | mysql --user="$DB_USER" --password="$DB_PASSWORD"
echo "✅ Database restored"

# 2. Restore Files
echo ""
echo "Restoring files..."
if [ -f "$FILES_BACKUP_FILE" ]; then
  # Create backup of current files before restore
  CURRENT_BACKUP="/tmp/yujix-current-backup-$(date +%s).tar.gz"
  tar -czf "$CURRENT_BACKUP" "$SHARED_DIR/.env" "$SHARED_DIR/storage" 2>/dev/null || true
  echo "Current files backed up to: $CURRENT_BACKUP"

  # Restore from backup
  tar -xzf "$FILES_BACKUP_FILE" -C /

  # Fix permissions
  chown -R deploy:www-data "$SHARED_DIR/storage"
  find "$SHARED_DIR/storage" -type d -exec chmod 775 {} \; 2>/dev/null || true
  find "$SHARED_DIR/storage" -type f -exec chmod 664 {} \; 2>/dev/null || true

  echo "✅ Files restored"
else
  echo "⚠️  Files backup not found, skipping file restore"
fi

# 3. Clear caches
echo ""
echo "Clearing application caches..."
cd /var/www/yujix/current
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✅ Caches cleared"

# 4. Restart services
echo ""
echo "Restarting services..."
systemctl reload php8.2-fpm
supervisorctl restart yujix-worker:*
echo "✅ Services restarted"

echo ""
echo "==========================================="
echo "✅ Restore completed successfully"
echo "==========================================="
echo "Restored from: $TIMESTAMP"
echo "Database: Restored"
echo "Files: Restored"
echo ""
echo "Please verify the application is working correctly:"
echo "  https://yujix.com"
echo "==========================================="

exit 0
