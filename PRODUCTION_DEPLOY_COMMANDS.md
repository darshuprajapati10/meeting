# 🚀 Production Deployment Commands

## Quick Deploy (Production Server Par)

Production server par SSH karke ye commands run karein:

```bash
cd /path/to/production

# Step 1: Pull latest code
git pull origin main

# Step 2: Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Step 3: Install npm dependencies
npm install --omit=dev

# Step 4: Build assets (IMPORTANT!)
npm run build

# Step 5: Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

# Step 6: Set permissions
chmod -R 755 public/build/

# Step 7: Restart PHP-FPM
sudo service php8.2-fpm restart
# Ya agar different version ho:
# sudo service php8.3-fpm restart
```

## Ya Script Use Karein

1. **Script upload karein:**
   - `deploy-production.sh` file production server par upload karein

2. **Script run karein:**
   ```bash
   cd /path/to/production
   chmod +x deploy-production.sh
   ./deploy-production.sh
   ```

## Verify Deployment

1. Visit: **https://yujix.com/pricing**
2. Hard refresh: `Cmd+Shift+R` (Mac) ya `Ctrl+Shift+R` (Windows)
3. Should see:
   - ✅ 2 plans: **FREE (Muft)** + **PRO**
   - ✅ Prices in **₹ (Indian Rupees)**
   - ✅ FREE: **₹0/month**
   - ✅ PRO: **₹999/month** ya **₹9,999/year**

---

**Status:** ✅ Script ready! Production server par run karein.



