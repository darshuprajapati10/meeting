# Deployment Configuration Files

This directory contains template configuration files for deploying Yujix API to production.

## Files Overview

### `nginx-yujix.conf`
Nginx server block configuration for yujix.com.

**Server location:** `/etc/nginx/sites-available/yujix`

**Usage:**
```bash
sudo nano /etc/nginx/sites-available/yujix
# Paste the contents of this file

sudo ln -s /etc/nginx/sites-available/yujix /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### `supervisor-yujix-worker.conf`
Supervisor configuration for Laravel queue workers.

**Server location:** `/etc/supervisor/conf.d/yujix-worker.conf`

**Usage:**
```bash
sudo nano /etc/supervisor/conf.d/yujix-worker.conf
# Paste the contents of this file

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yujix-worker:*
```

---

### `.env.production.template`
Production environment configuration template.

**Server location:** `/var/www/yujix/.env`

**Usage:**
```bash
nano /var/www/yujix/.env
# Use this template and fill in actual values
chmod 600 /var/www/yujix/.env
```

**Required values to replace:**
- `YOUR_DATABASE_PASSWORD_HERE` - from setup-server.sh output
- `your_firebase_project_id` - from Firebase console
- `rzp_live_YOUR_LIVE_KEY_HERE` - Razorpay live key
- `your_live_secret_here` - Razorpay live secret
- Mail configuration values

---

## Deployment Workflow

1. Run `setup-server.sh` to provision server
2. Apply Nginx configuration from `nginx-yujix.conf`
3. Create `.env` using `.env.production.template`
4. Deploy application code
5. Apply Supervisor configuration from `supervisor-yujix-worker.conf`
6. Set up cron job for scheduler
7. Install SSL certificate
8. Configure GitHub Actions for CI/CD

See `../DEPLOYMENT_GUIDE.md` for complete step-by-step instructions.
See `../QUICK_DEPLOY.md` for quick reference.

---

## Configuration Notes

### Nginx
- Configured for PHP 8.2-FPM
- Client max body size: 100M (for file uploads)
- SSL ready (Certbot will modify)
- Security headers enabled
- Static asset caching enabled

### Supervisor
- 2 queue worker processes
- Auto-restart on failure
- 1-hour max execution time
- Logs to `storage/logs/worker.log`

### Environment
- Production mode (APP_DEBUG=false)
- Database queue driver
- Redis cache driver
- Asia/Kolkata timezone
- Secure session cookies (HTTPS)
