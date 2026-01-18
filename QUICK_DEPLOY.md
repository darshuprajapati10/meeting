# Quick Deployment Reference

Fast reference for deploying Yujix API to Digital Ocean droplet.

## Pre-Flight Checklist

- [ ] Server: root@157.245.97.43
- [ ] Domain: yujix.com DNS → 157.245.97.43
- [ ] Firebase service-account.json ready
- [ ] Razorpay LIVE keys ready
- [ ] GitHub repo access

---

## 1. Server Setup (One-Time)

```bash
# Local machine
chmod +x setup-server.sh
scp setup-server.sh root@157.245.97.43:/root/

# On server
ssh root@157.245.97.43
bash /root/setup-server.sh
# ⚠️ Save the database password!
```

---

## 2. Nginx Configuration

```bash
# Copy from deployment/nginx-yujix.conf to server
sudo nano /etc/nginx/sites-available/yujix
# Paste config

sudo ln -s /etc/nginx/sites-available/yujix /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## 3. Deploy Application

```bash
su - deploy
cd /var/www/yujix
git clone https://github.com/YOUR_USERNAME/yujixapi.git .

# Create .env (use deployment/.env.production.template)
nano .env
chmod 600 .env

# Upload Firebase credentials (from local machine)
# scp service-account.json deploy@157.245.97.43:/var/www/yujix/storage/app/firebase/
chmod 600 storage/app/firebase/service-account.json

# Install & build
composer install --no-dev --optimize-autoloader
npm install --omit=dev
npm run build

# Laravel setup
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=SubscriptionPlanSeeder --force

# Permissions
sudo chown -R deploy:www-data .
sudo chmod -R 775 storage bootstrap/cache

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Supervisor (Queue Workers)

```bash
# Copy from deployment/supervisor-yujix-worker.conf
sudo nano /etc/supervisor/conf.d/yujix-worker.conf
# Paste config

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yujix-worker:*
sudo supervisorctl status  # Should show RUNNING
```

---

## 5. Cron (Scheduler)

```bash
crontab -e
# Add:
# * * * * * cd /var/www/yujix && php artisan schedule:run >> /var/www/yujix/storage/logs/scheduler.log 2>&1
```

---

## 6. SSL Certificate

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yujix.com -d www.yujix.com

# Update .env
nano /var/www/yujix/.env
# Change APP_URL=https://yujix.com
php artisan config:cache
```

---

## 7. GitHub Actions

```bash
# Local machine - generate deploy key
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions_deploy
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub deploy@157.245.97.43
cat ~/.ssh/github_actions_deploy  # Copy for GitHub

# GitHub: Settings → Secrets → New secret
# Name: SSH_PRIVATE_KEY
# Value: [paste private key]

# Server - sudo permissions
sudo nano /etc/sudoers.d/deploy
# Add:
# deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
# deploy ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.2-fpm
# deploy ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx

sudo chmod 440 /etc/sudoers.d/deploy
```

---

## 8. Verify

```bash
cd /var/www/yujix
./check_production.sh

curl -I https://yujix.com
sudo supervisorctl status
tail -20 storage/logs/scheduler.log
```

---

## Daily Operations

**View logs:**
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

**Restart services:**
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart yujix-worker:*
```

**Manual deploy:**
```bash
cd /var/www/yujix
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
sudo supervisorctl restart yujix-worker:*
```

---

## Troubleshooting

**502 Error:**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

**Queue not working:**
```bash
sudo supervisorctl restart yujix-worker:*
tail -f storage/logs/worker.log
```

**Permissions:**
```bash
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Files Reference

- Server setup: `setup-server.sh`
- GitHub Actions: `.github/workflows/deploy.yml`
- Nginx config: `deployment/nginx-yujix.conf`
- Supervisor config: `deployment/supervisor-yujix-worker.conf`
- Environment template: `deployment/.env.production.template`
- Full guide: `DEPLOYMENT_GUIDE.md`

---

**Deployment complete when:**
- ✅ https://yujix.com loads
- ✅ SSL certificate valid
- ✅ Queue workers RUNNING
- ✅ Scheduler running (check logs)
- ✅ GitHub Actions deploying successfully
