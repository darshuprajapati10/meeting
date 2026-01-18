# Yujix API - Complete Deployment Guide

This guide walks you through deploying the Yujix Meeting Management API to your Digital Ocean droplet.

## Prerequisites

Before starting, ensure you have:

- [ ] Digital Ocean droplet running Ubuntu 22.04 or later
- [ ] Root SSH access to the server (root@157.245.97.43)
- [ ] Domain `yujix.com` with DNS A record pointing to `157.245.97.43`
- [ ] Firebase `service-account.json` file ready
- [ ] Razorpay live API keys
- [ ] GitHub repository push access

---

## Phase 1: Initial Server Setup

### Step 1: Upload and Run Server Setup Script

From your local machine:

```bash
# Make the script executable
chmod +x setup-server.sh

# Copy script to server
scp setup-server.sh root@157.245.97.43:/root/

# SSH into server
ssh root@157.245.97.43

# Run the setup script
bash /root/setup-server.sh
```

The script will:
- Install Nginx, PHP 8.2, MySQL, Redis, Node.js, Composer, Supervisor
- Create MySQL database and user
- Create deploy user
- Configure firewall (UFW)
- Set up basic security

**IMPORTANT:** Save the database password shown at the end - you'll need it for the .env file.

### Step 2: Configure Nginx

```bash
# Copy the Nginx configuration
sudo nano /etc/nginx/sites-available/yujix
```

Copy the content from `deployment/nginx-yujix.conf` file in the repository.

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/yujix /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## Phase 2: Deploy Application

### Step 3: Clone Repository

Switch to deploy user and clone the repository:

```bash
# Switch to deploy user
su - deploy

# Clone repository
cd /var/www/yujix
git clone https://github.com/YOUR_USERNAME/yujixapi.git .

# Or if repository already exists
git pull origin main
```

### Step 4: Configure Environment

```bash
# Create .env file
nano /var/www/yujix/.env
```

Use the template from `deployment/.env.production.template` and fill in:
- Database password (from Step 1)
- Firebase project ID
- Razorpay live keys
- Mail configuration
- Any other service credentials

**Set secure permissions:**
```bash
chmod 600 /var/www/yujix/.env
```

### Step 5: Upload Firebase Credentials

From your local machine:

```bash
# Upload service-account.json to server
scp /path/to/service-account.json deploy@157.245.97.43:/var/www/yujix/storage/app/firebase/
```

On the server:

```bash
# Set permissions
chmod 600 /var/www/yujix/storage/app/firebase/service-account.json
chown deploy:www-data /var/www/yujix/storage/app/firebase/service-account.json
```

### Step 6: Install Dependencies and Build

```bash
cd /var/www/yujix

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install --omit=dev

# Build frontend assets
npm run build

# Generate application key
php artisan key:generate

# Set permissions
sudo chown -R deploy:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

### Step 7: Run Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed subscription plans
php artisan db:seed --class=SubscriptionPlanSeeder --force

# Verify database
php artisan tinker
>>> \App\Models\SubscriptionPlan::count();
>>> exit
```

### Step 8: Optimize for Production

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## Phase 3: Configure Background Services

### Step 9: Set Up Queue Workers (Supervisor)

```bash
# Create Supervisor configuration
sudo nano /etc/supervisor/conf.d/yujix-worker.conf
```

Copy the content from `deployment/supervisor-yujix-worker.conf` file.

```bash
# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yujix-worker:*

# Check status
sudo supervisorctl status
```

You should see 2 worker processes running.

### Step 10: Set Up Scheduler (Cron)

```bash
# Edit crontab for deploy user
crontab -e
```

Add this line:

```cron
* * * * * cd /var/www/yujix && php artisan schedule:run >> /var/www/yujix/storage/logs/scheduler.log 2>&1
```

Verify:

```bash
crontab -l
```

---

## Phase 4: SSL Certificate

### Step 11: Install SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain and install certificate
sudo certbot --nginx -d yujix.com -d www.yujix.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Step 12: Update .env with HTTPS URL

```bash
nano /var/www/yujix/.env
```

Change:
```
APP_URL=https://yujix.com
SESSION_SECURE_COOKIE=true
```

Then rebuild cache:

```bash
php artisan config:cache
```

---

## Phase 5: GitHub Actions CI/CD

### Step 13: Generate Deploy SSH Key

On your local machine:

```bash
# Generate a new SSH key for GitHub Actions
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy

# Copy the public key to server
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub deploy@157.245.97.43

# Display private key (copy this for GitHub secret)
cat ~/.ssh/github_actions_deploy
```

### Step 14: Add GitHub Secret

1. Go to your GitHub repository
2. Navigate to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Name: `SSH_PRIVATE_KEY`
5. Value: Paste the entire private key content (from `cat` command above)
6. Click **Add secret**

### Step 15: Test GitHub Actions

The workflow file `.github/workflows/deploy.yml` is already in the repository.

To test:

```bash
# Make a small change and push to main
git add .
git commit -m "Test deployment workflow"
git push origin main
```

Watch the deployment in **Actions** tab on GitHub.

---

## Phase 6: Verification

### Step 16: Run Diagnostic Check

```bash
cd /var/www/yujix
chmod +x check_production.sh
./check_production.sh
```

All checks should pass with green checkmarks.

### Step 17: Manual Testing

**Test website access:**
```bash
curl -I https://yujix.com
```

Should return `HTTP/2 200` or `302`.

**Test queue workers:**
```bash
sudo supervisorctl status yujix-worker:*
```

Should show `RUNNING`.

**Test scheduler:**
```bash
tail -20 /var/www/yujix/storage/logs/scheduler.log
```

Should show recent schedule runs.

**Test API endpoints:**
```bash
# Health check (if endpoint exists)
curl https://yujix.com/api/health

# Test registration
curl -X POST "https://yujix.com/api/register" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

---

## Common Commands

### Service Management

```bash
# Restart Nginx
sudo systemctl restart nginx

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart queue workers
sudo supervisorctl restart yujix-worker:*

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo supervisorctl status
```

### Application Management

```bash
# Pull latest changes
cd /var/www/yujix
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install --omit=dev
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Logs

```bash
# Application logs
tail -f /var/www/yujix/storage/logs/laravel.log

# Queue worker logs
tail -f /var/www/yujix/storage/logs/worker.log

# Scheduler logs
tail -f /var/www/yujix/storage/logs/scheduler.log

# Nginx error logs
sudo tail -f /var/log/nginx/yujix-error.log

# Nginx access logs
sudo tail -f /var/log/nginx/yujix-access.log
```

---

## Troubleshooting

### 502 Bad Gateway

```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Check socket exists
ls -la /var/run/php/php8.2-fpm.sock
```

### Permission Denied Errors

```bash
cd /var/www/yujix
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
```

### Queue Jobs Not Processing

```bash
# Check worker status
sudo supervisorctl status yujix-worker:*

# Restart workers
sudo supervisorctl restart yujix-worker:*

# Check worker logs
tail -100 /var/www/yujix/storage/logs/worker.log

# Test manually
php artisan queue:work --once
```

### Scheduler Not Running

```bash
# Verify cron job exists
crontab -l

# Run manually
php artisan schedule:run

# Test reminder command
php artisan meetings:send-reminders

# Check logs
tail -50 /var/www/yujix/storage/logs/scheduler.log
```

### GitHub Actions Deployment Fails

```bash
# Check SSH access
ssh deploy@157.245.97.43

# Verify sudo permissions
sudo supervisorctl status
sudo systemctl restart php8.2-fpm

# Check git repository
cd /var/www/yujix
git status
git remote -v
```

---

## Database Backups

Create automated daily backups:

```bash
# Create backup script
sudo nano /usr/local/bin/backup-yujix.sh
```

Add:

```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/yujix"
mkdir -p $BACKUP_DIR
mysqldump -u yujix_user -pYOUR_PASSWORD yujix_production | gzip > $BACKUP_DIR/yujix_$TIMESTAMP.sql.gz
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -delete
```

Make executable and add to cron:

```bash
sudo chmod +x /usr/local/bin/backup-yujix.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-yujix.sh
```

---

## Security Checklist

- [x] Firewall (UFW) configured and enabled
- [x] SSH root login disabled
- [x] Fail2Ban installed and running
- [x] SSL certificate installed
- [x] .env file permissions set to 600
- [x] Firebase credentials file permissions set to 600
- [x] Strong database password
- [x] Production environment (APP_ENV=production)
- [x] Debug mode disabled (APP_DEBUG=false)
- [x] Using Razorpay LIVE keys (not test)
- [x] Regular database backups configured

---

## Support

If you encounter issues:

1. Check application logs: `tail -f storage/logs/laravel.log`
2. Run diagnostic script: `./check_production.sh`
3. Review Nginx error logs: `sudo tail -f /var/log/nginx/yujix-error.log`
4. Verify all services are running: `sudo systemctl status nginx php8.2-fpm mysql redis-server`

---

## Deployment Complete!

Your Yujix API is now deployed and running on:
- **URL:** https://yujix.com
- **Server:** 157.245.97.43
- **Application:** /var/www/yujix
- **User:** deploy

Future deployments will happen automatically via GitHub Actions when you push to the `main` branch.
