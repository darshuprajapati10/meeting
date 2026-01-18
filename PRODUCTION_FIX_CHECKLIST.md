# Production Fix Checklist for Cancellation Refunds Page

## Errors Fixed:

### 1. Component Import Error
**Error:** `Cannot read properties of undefined (reading 'default')`

**Fix Applied:**
- Updated `resources/js/app.js` with improved component resolution
- Added better error handling and fallback paths
- Ensured async/await is handled correctly for lazy-loaded components

### 2. Missing Icon Files
**Error:** `404 android-chrome-192x192.png` and manifest icon errors

**Fix Applied:**
- Updated `public/site.webmanifest` to use `favicon.ico` instead of missing PNG files
- This removes the 404 errors for icon files

## Files Changed:

1. ✅ `resources/js/app.js` - Improved component resolution
2. ✅ `public/site.webmanifest` - Updated icon references
3. ✅ `app/Http/Middleware/HandleInertiaRequests.php` - Added cache busting

## Deployment Steps:

### 1. Build Assets (LOCAL)
```bash
npm run build
```

This creates new production assets in `public/build/`

### 2. Files to Deploy to Production:

**Required Files:**
- `public/build/` directory (entire directory with all new assets)
- `public/site.webmanifest` (updated manifest file)
- `resources/js/app.js` (source file - only if you rebuild on server)
- `app/Http/Middleware/HandleInertiaRequests.php` (cache busting fix)

**Optional but Recommended:**
- All Vue component files in `resources/js/Pages/` (if rebuilding on server)
- `vite.config.js` (if rebuilding on server)

### 3. Production Server Actions:

**Option A: Deploy Pre-built Assets (Recommended)**
1. Upload the entire `public/build/` directory to production
2. Upload `public/site.webmanifest` to production
3. Upload `app/Http/Middleware/HandleInertiaRequests.php` to production
4. Clear Laravel cache: `php artisan cache:clear`
5. Clear config cache: `php artisan config:clear`
6. Restart web server if needed

**Option B: Rebuild on Production Server**
1. Upload all source files (`resources/js/`, `vite.config.js`, etc.)
2. Run `npm install` (if dependencies changed)
3. Run `npm run build`
4. Upload `app/Http/Middleware/HandleInertiaRequests.php`
5. Clear Laravel cache

### 4. Verification:

After deployment, check:
- ✅ https://yujix.com/cancellation-refunds loads without errors
- ✅ No console errors about undefined properties
- ✅ No 404 errors for icon files
- ✅ Browser console shows no JavaScript errors
- ✅ Other pages still work: `/terms-and-conditions`, `/shipping`, `/privacy`

### 5. Cache Clearing:

**On Production Server:**
```bash
# Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# If using OPcache
php artisan optimize:clear
```

**Browser Cache:**
- Hard refresh: Ctrl+Shift+R (Windows/Linux) or Cmd+Shift+R (Mac)
- Or clear browser cache

## Important Notes:

1. **Old Build Files**: The production server is currently serving old build files (`app--JdxCL5I.js`). You MUST upload the new build files from `public/build/` to replace them.

2. **Asset Versioning**: The updated middleware now uses manifest hash for cache busting, which will force browsers to load new assets after deployment.

3. **Icon Files**: The manifest now references `favicon.ico` instead of PNG files. If you want proper PWA icons later, create:
   - `android-chrome-192x192.png`
   - `android-chrome-512x512.png`
   Then update the manifest accordingly.

## Troubleshooting:

If errors persist after deployment:

1. **Check Build Manifest**: Verify `public/build/manifest.json` includes `CancellationRefunds` entry
2. **Check File Permissions**: Ensure web server can read `public/build/` files
3. **Check Asset URLs**: Verify Vite is generating correct asset URLs in production
4. **Check Server Logs**: Look for PHP/JavaScript errors in Laravel logs
5. **Verify Route**: Check that `/cancellation-refunds` route exists in `routes/web.php`

## Rollback Plan:

If issues occur:
1. Restore previous `public/build/` directory from backup
2. Restore previous `public/site.webmanifest`
3. Restore previous `resources/js/app.js` (if changed on server)
4. Clear caches

