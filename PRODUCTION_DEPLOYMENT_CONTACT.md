# Production Deployment - Contact Page Update

## Issue
Production site at https://yujix.com/contact is showing old information:
- ❌ support@yujix.com and sales@yujix.com
- ❌ +1 (555) 123-4567
- ❌ 123 Innovation Drive, San Francisco, CA 94105

## Correct Information (Local)
- ✅ Email: info@yujix.com
- ✅ Phone: +91 9265299142
- ✅ Hours: Mon-Fri, 10am-7pm EST
- ✅ Address: 403, LINK, 100 FT RCC ROAD, Serenity Space Rd, near JLR Showroom, Upper, Gota, Ahmedabad, Gujarat 382481

## Build Status
✅ Production build completed successfully
- Build file: `public/build/assets/Contact-D76aDT-m.js` (15K)
- Build manifest: `public/build/manifest.json` (updated)
- Build time: December 25, 2025

## Deployment Steps

### 1. Upload Build Files to Production Server

**Required Files:**
```
public/build/manifest.json
public/build/assets/Contact-D76aDT-m.js
public/build/assets/app-CVdekW79.js (main app bundle)
```

**OR upload entire directory:**
```
public/build/ (entire directory)
```

### 2. Clear Production Caches

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

### 4. Test Production Site

1. Visit: https://yujix.com/contact
2. Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
3. Check browser console for any 404 errors
4. Verify the Contact page shows:
   - Email: info@yujix.com
   - Phone: +91 9265299142
   - Hours: Mon-Fri, 10am-7pm EST
   - Address: Ahmedabad, Gujarat address

## Troubleshooting

### If changes still don't appear:

1. **Check if files were uploaded:**
   ```bash
   ls -lh /path/to/production/public/build/assets/Contact-*.js
   ```
   Should show: `Contact-D76aDT-m.js`

2. **Check browser cache:**
   - Open DevTools (F12)
   - Go to Network tab
   - Check "Disable cache"
   - Hard refresh

3. **Check if old files are cached:**
   - Look for old Contact component files in `public/build/assets/`
   - Delete any old Contact-*.js files (keep only Contact-D76aDT-m.js)

4. **Verify manifest.json:**
   ```bash
   cat public/build/manifest.json | grep Contact
   ```
   Should show: `"file": "assets/Contact-D76aDT-m.js"`

5. **Check server logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

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
2. ✅ Test the contact page
3. ✅ Verify all information is correct
4. ✅ Check mobile view if applicable

---

**Last Updated:** December 25, 2025
**Build Hash:** Contact-D76aDT-m.js

