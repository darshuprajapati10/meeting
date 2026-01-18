# Production Deployment - Shipping Page Update

## Issue
Production site at https://yujix.com/shipping is showing old content (JIGOMIT LLP physical shipping policy) instead of the restored digital service delivery content.

## Correct Content (Local - Restored)
✅ Digital Service Delivery (SaaS platform)
✅ How to Access YUJIX (Web Platform, Mobile Apps, System Requirements)
✅ Account Activation information
✅ Support section
✅ Physical Merchandise notice

## Build Status
✅ Production build completed successfully
- Build file: `public/build/assets/Shipping-CADm0iBn.js` (7.43 KB)
- Build manifest: `public/build/manifest.json` (updated)
- Build time: December 24, 2025

## Deployment Steps

### Option A: Deploy Pre-built Assets (Recommended - Fastest)

#### 1. Upload Build Files to Production Server

**Required Files:**
```
public/build/manifest.json
public/build/assets/Shipping-CADm0iBn.js
public/build/assets/app-D5rtGQk_.js (main app bundle - updated)
public/build/assets/app-BQAnke0F.css (CSS bundle)
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

### Option B: Rebuild on Production Server

#### 1. Pull Latest Code
```bash
cd /path/to/production
git pull origin main
```

#### 2. Install Dependencies (if needed)
```bash
npm install
```

#### 3. Build Assets
```bash
npm run build
```

### 2. Clear Production Caches (Required for both options)

SSH into your production server and run:
```bash
cd /path/to/production
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### 3. Verify File Permissions

Ensure web server can read the files:
```bash
chmod -R 755 public/build/
chown -R www-data:www-data public/build/  # Adjust user/group as needed
```

### 4. Verify Deployment

**On Production Server:**
```bash
# Check if new Shipping file exists
ls -lh /path/to/production/public/build/assets/Shipping-CADm0iBn.js

# Verify manifest.json references correct file
cat /path/to/production/public/build/manifest.json | grep -A 3 Shipping
```

**Expected output should show:**
```json
"resources/js/Pages/Shipping.vue": {
  "file": "assets/Shipping-CADm0iBn.js",
  ...
}
```

### 5. Test Production Site

1. Visit: https://yujix.com/shipping
2. **Hard refresh:** `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
3. **Open DevTools (F12)** and check:
   - Network tab: Look for `Shipping-CADm0iBn.js` loading (not old file)
   - Console tab: No 404 errors or JavaScript errors
4. Verify the Shipping page shows:
   - ✅ "Digital Service Delivery" section (first section)
   - ✅ "How to Access YUJIX" section
   - ✅ "Account Activation" section
   - ✅ "Need Help Accessing Your Account?" section
   - ✅ "Physical Merchandise" notice
   - ❌ NOT showing "JIGOMIT LLP" or "International Shipping" or "Domestic Shipping"

## Troubleshooting

### If changes still don't appear:

1. **Verify files were uploaded correctly:**
   ```bash
   # On production server
   ls -lh /path/to/production/public/build/assets/Shipping-*.js
   ```
   Should show: `Shipping-CADm0iBn.js` (7.43 KB)
   
   If you see old files like `Shipping-PI9R17Oy.js`, delete them:
   ```bash
   rm /path/to/production/public/build/assets/Shipping-PI9R17Oy.js
   ```

2. **Check manifest.json is correct:**
   ```bash
   # On production server
   cat /path/to/production/public/build/manifest.json | grep -A 3 Shipping
   ```
   Should show: `"file": "assets/Shipping-CADm0iBn.js"`

3. **Clear ALL caches again:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   php artisan optimize:clear
   
   # If using OPcache
   php artisan opcache:clear  # or restart PHP-FPM
   ```

4. **Check browser cache:**
   - Open DevTools (F12)
   - Go to Network tab
   - ✅ Check "Disable cache"
   - Hard refresh: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows)
   - Or use Incognito/Private browsing mode

5. **Verify the correct file is being loaded:**
   - Open DevTools → Network tab
   - Reload page
   - Look for `Shipping-*.js` file
   - Should see `Shipping-CADm0iBn.js` (NOT old file)

6. **Check file permissions:**
   ```bash
   chmod -R 755 /path/to/production/public/build/
   chown -R www-data:www-data /path/to/production/public/build/
   ```

7. **Check server logs for errors:**
   ```bash
   tail -f /path/to/production/storage/logs/laravel.log
   ```

8. **If using CDN or reverse proxy:**
   - Clear CDN cache
   - Clear reverse proxy cache (Varnish, Cloudflare, etc.)

## File Locations

**Local (ready to deploy):**
- `/Users/dhavalkumarmesavaniya/Herd/meetui/public/build/`

**Production (needs update):**
- `/path/to/production/public/build/` (your production path)

## Quick Deploy Command (if using rsync/scp)

```bash
# Example using rsync
rsync -avz public/build/ user@production-server:/path/to/production/public/build/

# Example using scp
scp -r public/build/* user@production-server:/path/to/production/public/build/
```

## After Deployment

1. ✅ Clear all caches
2. ✅ Test the shipping page at https://yujix.com/shipping
3. ✅ Verify content shows "Digital Service Delivery" (not physical shipping)
4. ✅ Check mobile view if applicable
5. ✅ Verify all sections are displaying correctly

---

**Last Updated:** December 24, 2025
**Build Hash:** Shipping-CADm0iBn.js

