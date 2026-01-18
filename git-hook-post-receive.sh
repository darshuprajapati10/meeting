#!/bin/bash

# Git Post-Receive Hook for Automatic Deployment
# Production server par .git/hooks/post-receive file mein copy karein

# Production directory path (adjust karein)
PROJECT_DIR="/path/to/production"

cd "$PROJECT_DIR" || exit

# Pull latest code
git --git-dir="$PROJECT_DIR/.git" --work-tree="$PROJECT_DIR" checkout -f

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --quiet

# Install npm dependencies
npm install --omit=dev --quiet

# Build assets (IMPORTANT!)
npm run build

# Clear Laravel caches
php artisan cache:clear --quiet
php artisan config:clear --quiet
php artisan view:clear --quiet
php artisan route:clear --quiet
php artisan optimize:clear --quiet

# Set permissions
chmod -R 755 public/build/

# Restart PHP-FPM (if needed)
sudo service php8.2-fpm restart 2>/dev/null || sudo service php8.3-fpm restart 2>/dev/null || true

echo "✅ Deployment complete!"



