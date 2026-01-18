# Pricing Page Deployment Guide

## ✅ Build Status
**Build completed successfully!**
- New Pricing file: `public/build/assets/Pricing-B7q8ICWC.js` (13.00 kB)
- Build manifest: `public/build/manifest.json` (updated)
- Change: Removed "(Muft)" from FREE plan name

## 🚀 Deployment Steps

### Option A: Upload Build Files Only (Fastest - Recommended)

#### 1. Upload Build Files to Production Server

**Required Files:**
```
public/build/manifest.json
public/build/assets/Pricing-B7q8ICWC.js
public/build/assets/app-DxMr87Q2.js (main app bundle - may have changed)
public/build/assets/app-Dr_q_Tfh.css (CSS bundle)
```

**OR upload entire directory (Recommended):**
```
public/build/ (entire directory)
```

**Using rsync (recommended):**
```bash
rsync -avz --delete public/build/ user@production-server:/path/to/production/public/build/
```

**Using scp:**
```bash
scp -r public/build/* user@production-server:/path/to/production/public/build/
```

#### 2. Clear Production Caches

SSH into your production server and run:
```bash
cd /path/to/production
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

#### 3. Verify File Permissions

```bash
chmod -R 755 public/build/
chown -R www-data:www-data public/build/  # Adjust user/group as needed
```

### Option B: Use Deployment Script (If you have SSH access)

If you have SSH access to production, you can use the deployment script:

1. **Upload the updated Pricing.vue file to production:**
   ```bash
   scp resources/js/Pages/Pricing.vue user@production-server:/path/to/production/resources/js/Pages/Pricing.vue
   ```

2. **SSH into production and run:**
   ```bash
   ssh user@production-server
   cd /path/to/production
   chmod +x deploy-production.sh
   ./deploy-production.sh
   ```

### Option C: Manual Build on Production Server

1. **SSH into production:**
   ```bash
   ssh user@production-server
   cd /path/to/production
   ```

2. **Pull latest code:**
   ```bash
   git pull origin main
   ```

3. **Install dependencies (if needed):**
   ```bash
   npm install --production
   ```

4. **Build assets:**
   ```bash
   npm run build
   ```

5. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   php artisan optimize:clear
   ```

## ✅ Verification Steps

After deployment, verify the changes:

1. **Visit:** https://yujix.com/pricing
2. **Hard refresh:** 
   - Mac: `Cmd + Shift + R`
   - Windows/Linux: `Ctrl + Shift + R`
3. **Check:**
   - ✅ Should show 2 plans: **FREE** and **PRO** (not "FREE (Muft)")
   - ✅ Prices in Indian Rupees (₹): ₹0/month and ₹999/month
   - ✅ Annual toggle should show ₹833/month for PRO
   - ✅ Features list should match the codebase

## 🔍 Troubleshooting

### If changes don't appear:

1. **Check if files were uploaded:**
   ```bash
   ls -lh /path/to/production/public/build/assets/Pricing-*.js
   ```
   Should show: `Pricing-B7q8ICWC.js`

2. **Check browser cache:**
   - Clear browser cache completely
   - Try incognito/private mode
   - Check browser console for 404 errors

3. **Check Laravel cache:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Check file permissions:**
   ```bash
   ls -la public/build/assets/Pricing-*.js
   ```
   Should be readable by web server

5. **Check web server logs:**
   ```bash
   tail -f /var/log/nginx/error.log  # For Nginx
   tail -f /var/log/apache2/error.log  # For Apache
   ```

## 📝 Summary

- ✅ Local build completed: `Pricing-B7q8ICWC.js`
- ✅ Change: Removed "(Muft)" from FREE plan
- ⏳ **Next step:** Upload `public/build/` directory to production server
- ⏳ **Then:** Clear production caches
- ⏳ **Finally:** Verify at https://yujix.com/pricing

