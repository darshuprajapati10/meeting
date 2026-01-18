# 🔄 Automatic Deployment Setup Guide

## Problem
Pehle automatic deployment tha (git push ke baad 1 minute mein update), ab nahi ho raha.

## Solution: Git Hook Setup

### Step 1: Production Server Par Hook Setup

SSH se production server par connect karein:

```bash
cd /path/to/production

# Hook directory check karein
ls -la .git/hooks/

# Post-receive hook banayein
nano .git/hooks/post-receive
```

### Step 2: Hook Script Content

Yeh content paste karein (ya `git-hook-post-receive.sh` file se copy karein):

```bash
#!/bin/bash

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

# Restart PHP-FPM
sudo service php8.2-fpm restart 2>/dev/null || sudo service php8.3-fpm restart 2>/dev/null || true

echo "✅ Deployment complete!"
```

### Step 3: Hook Ko Executable Banayein

```bash
chmod +x .git/hooks/post-receive
```

### Step 4: Test Karein

Local par:

```bash
git add .
git commit -m "Test automatic deployment"
git push origin main
```

Production par automatically:
1. Code pull hoga
2. Dependencies install hongi
3. Assets build honge
4. Caches clear honge
5. PHP-FPM restart hoga

---

## Alternative: Manual Deploy Script

Agar git hook setup nahi karna chahte, to:

1. `deploy-production.sh` file production par upload karein
2. Manually run karein jab bhi deploy karna ho:

```bash
cd /path/to/production
./deploy-production.sh
```

---

## Quick Fix (Abhi Ke Liye)

Production server par SSH karke:

```bash
cd /path/to/production
git pull origin main
npm install --omit=dev
npm run build
php artisan optimize:clear
sudo service php8.2-fpm restart
```

---

**Status:** ✅ Setup files ready! Production server par configure karein.



