# Quick Deployment Checklist - Shipping Page

## ✅ Pre-Deployment (Local)

- [x] Shipping.vue file restored with correct content
- [x] Production build completed (`npm run build`)
- [x] Build file created: `Shipping-CADm0iBn.js` (7.43 KB)

## 📤 Deployment Steps

### Step 1: Upload Build Files
- [ ] Upload `public/build/` directory to production server
- [ ] OR upload these specific files:
  - [ ] `public/build/manifest.json`
  - [ ] `public/build/assets/Shipping-CADm0iBn.js`
  - [ ] `public/build/assets/app-D5rtGQk_.js`
  - [ ] `public/build/assets/app-BQAnke0F.css`

### Step 2: SSH into Production Server
- [ ] Connect to production server via SSH

### Step 3: Clear Caches
- [ ] Run: `php artisan cache:clear`
- [ ] Run: `php artisan config:clear`
- [ ] Run: `php artisan view:clear`
- [ ] Run: `php artisan route:clear`
- [ ] Run: `php artisan optimize:clear`

### Step 4: Verify Files
- [ ] Check file exists: `ls -lh public/build/assets/Shipping-CADm0iBn.js`
- [ ] Verify manifest: `cat public/build/manifest.json | grep Shipping`
- [ ] Should show: `"file": "assets/Shipping-CADm0iBn.js"`

### Step 5: Test
- [ ] Visit: https://yujix.com/shipping
- [ ] Hard refresh: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows)
- [ ] Open DevTools → Network tab → Check "Disable cache"
- [ ] Verify page shows "Digital Service Delivery" section
- [ ] Verify page does NOT show "JIGOMIT LLP" or physical shipping info

## 🔍 Verification Checklist

After deployment, verify:
- [ ] Page loads without errors
- [ ] Shows "Digital Service Delivery" as first section
- [ ] Shows "How to Access YUJIX" section
- [ ] Shows "Account Activation" section
- [ ] Shows "Need Help Accessing Your Account?" section
- [ ] Shows "Physical Merchandise" notice
- [ ] Network tab shows `Shipping-CADm0iBn.js` loading (not old file)
- [ ] No 404 errors in console
- [ ] No JavaScript errors in console

## 🚨 If Still Not Working

1. [ ] Delete old Shipping files: `rm public/build/assets/Shipping-PI9R17Oy.js` (or any old Shipping-*.js)
2. [ ] Re-upload all build files
3. [ ] Clear caches again
4. [ ] Check file permissions: `chmod -R 755 public/build/`
5. [ ] Check server logs: `tail -f storage/logs/laravel.log`
6. [ ] Try incognito/private browsing mode
7. [ ] Clear CDN/proxy cache if using one

---

**Build File:** `Shipping-CADm0iBn.js`  
**Build Date:** December 24, 2025  
**Status:** Ready for deployment

