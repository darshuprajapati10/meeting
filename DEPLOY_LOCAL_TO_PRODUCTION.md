# 🚀 Deploy Local to Production - Complete Guide

## ✅ Current Status

**Local (meetui.test):**
- ✅ 2 plans: FREE and PRO
- ✅ Prices in ₹ (Indian Rupees): ₹0/month and ₹999/month
- ✅ Annual pricing: ₹833/month (₹9,999/year)
- ✅ Build file: `Pricing-B7q8ICWC.js` (13KB)
- ✅ All features correct

**Production (yujix.com):**
- ❌ Showing OLD content: 3 plans (Starter, Professional, Enterprise)
- ❌ Wrong prices: USD ($0, $15, $39)
- ❌ Wrong structure

**Goal:** Make production exactly match local!

---

## 📦 Files Ready for Deployment

✅ **Build completed successfully:**
- `public/build/assets/Pricing-B7q8ICWC.js` (13.00 kB)
- `public/build/manifest.json` (updated)
- All other assets built

---

## 🚀 Deployment Steps

### Step 1: Upload Build Files to Production

**Method A: Using SCP (Recommended)**
```bash
# From your local machine
cd /Users/dhavalkumarmesavaniya/Herd/meetui
scp -r public/build/* user@production-server:/path/to/production/public/build/
```

**Method B: Using rsync (Best for large deployments)**
```bash
rsync -avz --delete public/build/ user@production-server:/path/to/production/public/build/
```

**Method C: Using FTP/SFTP Client**
1. Connect to production server via FTP/SFTP
2. Navigate to: `/path/to/production/public/build/`
3. Upload ALL files from `public/build/` directory
4. **Important:** Replace all existing files

### Step 2: SSH into Production Server

```bash
ssh user@your-production-server
cd /path/to/production
```

### Step 3: Verify Files Were Uploaded

```bash
# Check Pricing file exists
ls -lh public/build/assets/Pricing-*.js

# Should show: Pricing-B7q8ICWC.js (13K)
# Check timestamp is recent (today's date)
```

### Step 4: Clear ALL Laravel Caches (CRITICAL!)

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### Step 5: Set File Permissions

```bash
chmod -R 755 public/build/
chown -R www-data:www-data public/build/  # Adjust user/group as needed
```

### Step 6: Restart Web Server (Optional but Recommended)

```bash
# For Nginx
sudo systemctl restart nginx

# For Apache
sudo systemctl restart apache2

# For PHP-FPM
sudo systemctl restart php8.2-fpm  # or php8.3-fpm
```

---

## ✅ Verification Checklist

After deployment, verify each item:

1. **Visit:** https://yujix.com/pricing
2. **Hard refresh:** 
   - Mac: `Cmd + Shift + R`
   - Windows: `Ctrl + Shift + R`
   - Or use Incognito/Private mode

3. **Check Pricing Plans:**
   - ✅ Should show **2 plans** (not 3)
   - ✅ Plan names: **FREE** and **PRO** (not Starter/Professional/Enterprise)
   - ✅ FREE plan shows: "FREE" (not "FREE (Muft)")

4. **Check Prices:**
   - ✅ FREE: **₹0/month** (not $0)
   - ✅ PRO: **₹999/month** (not $15)
   - ✅ Annual toggle: PRO shows **₹833/month** (₹9,999/year)

5. **Check Features:**
   - ✅ FREE: 20 meetings/month, 50 contacts, etc.
   - ✅ PRO: Unlimited meetings, unlimited contacts, etc.

6. **Check Currency:**
   - ✅ All prices in **₹ (Indian Rupees)**
   - ✅ No USD ($) symbols anywhere

---

## 🔍 Troubleshooting

### If old content still shows:

#### 1. Check File Upload
```bash
# On production server
ls -lh public/build/assets/Pricing-*.js
cat public/build/manifest.json | grep Pricing
```
Should show: `Pricing-B7q8ICWC.js`

#### 2. Check Browser Cache
- Clear browser cache completely
- Try incognito/private mode
- Check browser console (F12) for 404 errors
- Look for old file names in Network tab

#### 3. Check Laravel Manifest
```bash
# On production server
cat public/build/manifest.json | grep -A 5 Pricing
```
Should reference: `Pricing-B7q8ICWC.js`

#### 4. Check Web Server Cache
```bash
# Clear Nginx cache
sudo rm -rf /var/cache/nginx/*
sudo systemctl reload nginx

# Clear Apache cache
sudo a2enmod cache
sudo service apache2 restart
```

#### 5. Check File Timestamps
```bash
ls -lht public/build/assets/Pricing-*.js
```
Should show recent timestamp (today's date/time)

#### 6. Check Web Server Logs
```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Apache error log
sudo tail -f /var/log/apache2/error.log
```

#### 7. Force Rebuild on Production (Last Resort)
```bash
# On production server
cd /path/to/production
npm install --production
npm run build
php artisan optimize:clear
```

---

## 📋 Quick Deployment Command Summary

**One-liner for deployment (if you have SSH access):**
```bash
# From local machine
cd /Users/dhavalkumarmesavaniya/Herd/meetui && \
scp -r public/build/* user@production-server:/path/to/production/public/build/ && \
ssh user@production-server "cd /path/to/production && php artisan optimize:clear"
```

---

## 🎯 Expected Result

After successful deployment:

**Production (https://yujix.com/pricing) should show:**
- ✅ 2 pricing plans: FREE and PRO
- ✅ Prices in ₹: ₹0/month and ₹999/month
- ✅ Annual pricing: ₹833/month (₹9,999/year)
- ✅ Indian market features
- ✅ Razorpay payment mentions
- ✅ Exact same content as local (http://meetui.test/pricing)

---

## 📝 Summary

**What to Deploy:**
- `public/build/` directory (entire folder)

**What to Run on Production:**
- `php artisan optimize:clear` (clears all caches)

**What to Verify:**
- Visit https://yujix.com/pricing
- Should match local exactly

**Time Required:** 5-10 minutes

---

## ✅ Success Criteria

Production pricing page should be **identical** to local:
- Same number of plans (2)
- Same plan names (FREE, PRO)
- Same prices (₹0, ₹999)
- Same features list
- Same currency (₹)

If all criteria match, deployment is successful! 🎉

