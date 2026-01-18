#!/bin/bash
##############################################################################
# One-Time Migration Script: Transform to Atomic Deployment Structure
# This script migrates from single-directory to releases/current pattern
# Run once on production server to enable zero-downtime deployments
##############################################################################

set -e

echo "=========================================="
echo "Migrating to Atomic Deployment Structure"
echo "=========================================="

# 1. Create backup
echo "Step 1: Creating backup..."
BACKUP_FILE="/tmp/yujix-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
tar -czf "$BACKUP_FILE" /var/www/yujix
echo "Backup created: $BACKUP_FILE"

# 2. Create new directory structure
echo "Step 2: Creating new directory structure..."
mkdir -p /var/www/yujix-new/releases
mkdir -p /var/www/yujix-new/shared/storage
mkdir -p /var/www/yujix-new/.deploy

# 3. Move storage to shared
echo "Step 3: Moving storage to shared location..."
mv /var/www/yujix/storage /var/www/yujix-new/shared/

# 4. Copy .env to shared
echo "Step 4: Copying .env to shared location..."
cp /var/www/yujix/.env /var/www/yujix-new/shared/.env

# 5. Create first release from current code
echo "Step 5: Creating initial release from current code..."
FIRST_RELEASE=$(date +"%Y%m%d-%H%M%S")
cp -a /var/www/yujix /var/www/yujix-new/releases/${FIRST_RELEASE}

# 6. Remove storage and .env from release (will be symlinked)
echo "Step 6: Removing storage and .env from release..."
rm -rf /var/www/yujix-new/releases/${FIRST_RELEASE}/storage
rm -f /var/www/yujix-new/releases/${FIRST_RELEASE}/.env

# 7. Create symlinks in release
echo "Step 7: Creating symlinks in release..."
cd /var/www/yujix-new/releases/${FIRST_RELEASE}
ln -sfn ../../shared/storage storage
ln -sfn ../../shared/.env .env

# 8. Set permissions
echo "Step 8: Setting permissions..."
chown -R deploy:www-data /var/www/yujix-new
find /var/www/yujix-new/shared/storage -type d -exec chmod 775 {} \;
find /var/www/yujix-new/shared/storage -type f -exec chmod 664 {} \;

# 9. Create current symlink
echo "Step 9: Creating current symlink..."
cd /var/www/yujix-new
ln -sfn releases/${FIRST_RELEASE} current

echo "Step 10: Please manually update Nginx and Supervisor configs before proceeding"
echo "  - Nginx: root /var/www/yujix/current/public;"
echo "  - Nginx: fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;"
echo "  - Nginx: fastcgi_param DOCUMENT_ROOT \$realpath_root;"
echo "  - Supervisor: command=php /var/www/yujix/current/artisan..."
echo "  - Supervisor: stdout_logfile=/var/www/yujix/shared/storage/logs/worker.log"
echo ""
read -p "Have you updated the configs? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Aborting migration. Please update configs first."
    exit 1
fi

# 11. Test nginx config
echo "Step 11: Testing Nginx configuration..."
sudo nginx -t

# 12. ATOMIC SWITCH
echo "Step 12: Performing atomic switch..."
mv /var/www/yujix /var/www/yujix-old
mv /var/www/yujix-new /var/www/yujix

# 13. Reload services
echo "Step 13: Reloading services..."
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart yujix-worker:*

# 14. Verify
echo "Step 14: Verifying deployment..."
sleep 3
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://yujix.com/api/health || echo "000")

if [ "$HTTP_CODE" -eq 200 ]; then
    echo "=========================================="
    echo "✅ Migration successful!"
    echo "=========================================="
    echo "Current release: $FIRST_RELEASE"
    echo "Backup: $BACKUP_FILE"
    echo "Old directory: /var/www/yujix-old (can be deleted after verification)"
    echo ""
    echo "Next steps:"
    echo "1. Test application thoroughly"
    echo "2. Test deployment with: cd /var/www/yujix && ./deploy.sh"
    echo "3. Test rollback with: cd /var/www/yujix && ./rollback.sh"
    echo "4. Delete /var/www/yujix-old when confident"
else
    echo "=========================================="
    echo "⚠️  Health check failed (HTTP $HTTP_CODE)"
    echo "=========================================="
    echo "Rolling back..."
    mv /var/www/yujix /var/www/yujix-failed
    mv /var/www/yujix-old /var/www/yujix
    sudo systemctl reload nginx
    sudo systemctl reload php8.2-fpm
    echo "Rollback complete. Check logs for errors."
    exit 1
fi
