# PHP-FPM and MySQL Security Hardening Guide

**Server:** 157.245.97.43 (yujix.com)
**Purpose:** Harden PHP-FPM and MySQL to prevent privilege escalation and unauthorized access
**Estimated Time:** 1 hour
**Downtime:** ~2 minutes (during service restarts)

---

## Overview

This guide implements security hardening for:
1. **PHP-FPM** - Systemd security restrictions, disabled dangerous functions
2. **MySQL** - Localhost-only binding, binary logging, connection limits

**Security Impact:**
- Systemd security score: 9.6 (UNSAFE) → 1.8 (SECURE)
- MySQL remote access: Disabled
- PHP dangerous functions: Disabled
- Directory execution: Prevented in writable directories

---

## Part 1: PHP-FPM Hardening

### Step 1: Backup Current Configuration

```bash
ssh root@157.245.97.43

# Backup PHP configuration
cp /etc/php/8.2/fpm/php.ini /etc/php/8.2/fpm/php.ini.backup.$(date +%Y%m%d)

# Backup systemd service file (if exists)
if [ -d "/etc/systemd/system/php8.2-fpm.service.d/" ]; then
    tar -czf /root/php-fpm-systemd-backup-$(date +%Y%m%d).tar.gz /etc/systemd/system/php8.2-fpm.service.d/
fi
```

### Step 2: Deploy PHP Security Configuration

```bash
# Create PHP configuration directory
mkdir -p /etc/php/8.2/fpm/conf.d/

# Deploy security.ini
cat > /etc/php/8.2/fpm/conf.d/99-security.ini << 'EOF'
[Copy contents of deployment/php-security.ini]
EOF
```

**Or upload from local machine:**
```bash
scp deployment/php-security.ini root@157.245.97.43:/etc/php/8.2/fpm/conf.d/99-security.ini
```

### Step 3: Create Upload Temporary Directory

```bash
# Create isolated upload directory
mkdir -p /tmp/php-uploads
chown www-data:www-data /tmp/php-uploads
chmod 755 /tmp/php-uploads

# Make persistent across reboots
echo "mkdir -p /tmp/php-uploads && chown www-data:www-data /tmp/php-uploads" >> /etc/rc.local
```

### Step 4: Deploy Systemd Security Overrides

```bash
# Create systemd override directory
mkdir -p /etc/systemd/system/php8.2-fpm.service.d/

# Deploy security overrides
cat > /etc/systemd/system/php8.2-fpm.service.d/security.conf << 'EOF'
[Copy contents of deployment/php-fpm-security.conf]
EOF
```

**Or upload from local machine:**
```bash
scp deployment/php-fpm-security.conf root@157.245.97.43:/etc/systemd/system/php8.2-fpm.service.d/security.conf
```

### Step 5: Reload and Restart PHP-FPM

```bash
# Reload systemd daemon
systemctl daemon-reload

# Restart PHP-FPM (brief downtime - 2-3 seconds)
systemctl restart php8.2-fpm

# Verify service is running
systemctl status php8.2-fpm
```

### Step 6: Verify PHP-FPM Hardening

```bash
# 1. Check systemd security score
systemd-analyze security php8.2-fpm.service

# Expected output:
# Overall exposure level for php8.2-fpm.service: 1.8 SECURE 🙂
# (Before: 9.6 UNSAFE 😨)

# 2. Verify PHP version is hidden
curl -I https://yujix.com/api/health | grep -i "x-powered-by"
# Should NOT show PHP version

# 3. Test dangerous functions are disabled
php -r "exec('whoami');" 2>&1
# Expected: Warning: exec() has been disabled

# 4. Test application still works
curl https://yujix.com/api/health
# Expected: {"status":"healthy"}

# 5. Check PHP error log
tail -50 /var/log/php8.2-fpm-errors.log
# Should not show any fatal errors

# 6. Verify read-only filesystem protection
systemctl show php8.2-fpm.service | grep ReadOnlyPaths
# Should show: ReadOnlyPaths=/

# 7. Verify no-exec in storage
systemctl show php8.2-fpm.service | grep NoExecPaths
# Should show: NoExecPaths=/var/www/yujix/shared/storage /tmp /var/tmp
```

### Step 7: Test Application Functionality

```bash
# Test login
curl -X POST https://yujix.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Test file upload (contact import)
# (Requires valid token and CSV file)

# Test queue workers
supervisorctl status yujix:*
# Should show: RUNNING

# Check Laravel logs for errors
tail -50 /var/www/yujix/shared/storage/logs/laravel.log
```

---

## Part 2: MySQL Hardening

### Step 1: Backup Current MySQL Configuration

```bash
# Backup current MySQL configuration
cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup.$(date +%Y%m%d)

# Backup all MySQL config
tar -czf /root/mysql-config-backup-$(date +%Y%m%d).tar.gz /etc/mysql/
```

### Step 2: Verify MySQL Current State

```bash
# Check current bind address
mysql -u root -p -e "SHOW VARIABLES LIKE 'bind_address';"

# Check if MySQL is accessible remotely
netstat -tulpn | grep 3306
# Look for 0.0.0.0:3306 (bad) vs 127.0.0.1:3306 (good)

# List all MySQL users
mysql -u root -p -e "SELECT user, host FROM mysql.user;"
```

### Step 3: Deploy MySQL Security Configuration

```bash
# Deploy security configuration
cat > /etc/mysql/mysql.conf.d/99-security.cnf << 'EOF'
[Copy contents of deployment/mysql-security.cnf]
EOF
```

**Or upload from local machine:**
```bash
scp deployment/mysql-security.cnf root@157.245.97.43:/etc/mysql/mysql.conf.d/99-security.cnf
```

### Step 4: Create MySQL Log Directory

```bash
# Create log directory
mkdir -p /var/log/mysql
chown mysql:mysql /var/log/mysql
chmod 750 /var/log/mysql

# Create secure file privilege directory
mkdir -p /var/lib/mysql-files
chown mysql:mysql /var/lib/mysql-files
chmod 750 /var/lib/mysql-files
```

### Step 5: Audit MySQL Users and Privileges

```bash
mysql -u root -p << 'EOF'
-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove test database
DROP DATABASE IF EXISTS test;

-- Show current users
SELECT user, host FROM mysql.user;

-- Check yujix_user privileges
SHOW GRANTS FOR 'yujix_user'@'localhost';

-- Verify yujix_user only has necessary privileges
-- Should have: SELECT, INSERT, UPDATE, DELETE on yujix_db.*
-- Should NOT have: DROP, GRANT, FILE, SUPER, PROCESS

-- If yujix_user has excessive privileges, revoke them:
-- REVOKE ALL PRIVILEGES ON *.* FROM 'yujix_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON yujix_db.* TO 'yujix_user'@'localhost';

-- Create backup-only user (for automated backups)
CREATE USER IF NOT EXISTS 'yujix_backup'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON yujix_db.* TO 'yujix_backup'@'localhost';

FLUSH PRIVILEGES;
EOF
```

**Generate strong backup user password:**
```bash
# Generate random password
openssl rand -base64 32
# Save this password - needed for automated backups
```

**Update backup script with new user:**
```bash
# Edit /root/backup-yujix.sh
# Change: DB_USER="yujix_user"
# To: DB_USER="yujix_backup"

# Update .env with backup password (or use separate backup .env)
```

### Step 6: Restart MySQL

```bash
# Test configuration before restart
mysqld --help --verbose | grep bind-address

# Restart MySQL (brief downtime - 5-10 seconds)
systemctl restart mysql

# Verify MySQL is running
systemctl status mysql
```

### Step 7: Verify MySQL Hardening

```bash
# 1. Verify bind address (localhost only)
mysql -u root -p -e "SHOW VARIABLES LIKE 'bind_address';"
# Expected: 127.0.0.1

# 2. Verify no remote access possible
netstat -tulpn | grep 3306
# Expected: 127.0.0.1:3306 (NOT 0.0.0.0:3306)

# 3. Test remote connection fails
mysql -h 157.245.97.43 -u yujix_user -p
# Expected: ERROR 2003 (HY000): Can't connect

# 4. Verify local connection works
mysql -u yujix_user -p yujix_db -e "SELECT COUNT(*) FROM users;"
# Should show count

# 5. Verify LOAD DATA LOCAL INFILE disabled
mysql -u root -p -e "SHOW VARIABLES LIKE 'local_infile';"
# Expected: OFF

# 6. Verify binary logging enabled
mysql -u root -p -e "SHOW BINARY LOGS;"
# Should show list of binary logs

# 7. Verify slow query log enabled
mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log';"
# Expected: ON

# 8. Check connection limits
mysql -u root -p -e "SHOW VARIABLES LIKE 'max_connections';"
# Expected: 200

# 9. Verify application still works
curl https://yujix.com/api/health
# Expected: {"status":"healthy"}

# 10. Test database operations from Laravel
cd /var/www/yujix/current
php artisan tinker
# Run: \App\Models\User::count()
# Should return user count without errors
```

---

## Rollback Procedures

### Rollback PHP-FPM Hardening

```bash
# If application breaks after PHP-FPM hardening:

# 1. Remove security configuration
rm /etc/php/8.2/fpm/conf.d/99-security.ini

# 2. Remove systemd overrides
rm -rf /etc/systemd/system/php8.2-fpm.service.d/

# 3. Restore original configuration
cp /etc/php/8.2/fpm/php.ini.backup.YYYYMMDD /etc/php/8.2/fpm/php.ini

# 4. Reload and restart
systemctl daemon-reload
systemctl restart php8.2-fpm

# 5. Verify application works
curl https://yujix.com/api/health
```

### Rollback MySQL Hardening

```bash
# If database becomes inaccessible:

# 1. Remove security configuration
rm /etc/mysql/mysql.conf.d/99-security.cnf

# 2. Restore original configuration
cp /etc/mysql/mysql.conf.d/mysqld.cnf.backup.YYYYMMDD /etc/mysql/mysql.conf.d/mysqld.cnf

# 3. Restart MySQL
systemctl restart mysql

# 4. Verify application works
curl https://yujix.com/api/health
```

---

## Monitoring

### PHP-FPM

```bash
# Monitor PHP-FPM errors
tail -f /var/log/php8.2-fpm-errors.log

# Monitor PHP-FPM slow requests
tail -f /var/log/php8.2-fpm-slow.log

# Check PHP-FPM status
systemctl status php8.2-fpm

# Check systemd security violations
journalctl -u php8.2-fpm -n 100 | grep -i "denied\|violation"
```

### MySQL

```bash
# Monitor MySQL errors
tail -f /var/log/mysql/error.log

# Monitor slow queries
tail -f /var/log/mysql/slow-query.log

# Monitor binary logs
mysql -u root -p -e "SHOW BINARY LOGS;"

# Check MySQL status
systemctl status mysql

# Monitor active connections
mysql -u root -p -e "SHOW PROCESSLIST;"

# Check connection usage
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
mysql -u root -p -e "SHOW STATUS LIKE 'Max_used_connections';"
```

---

## Performance Impact

### Expected Impact:

**PHP-FPM:**
- Systemd restrictions: ~1-2% overhead (negligible)
- Disabled functions: No impact (not used)
- Read-only filesystem: No impact (proper write paths configured)
- **Overall:** < 2% performance impact

**MySQL:**
- Localhost binding: No impact (application uses localhost)
- Binary logging: ~5-10% write overhead (acceptable for point-in-time recovery)
- Connection limits: No impact (200 is sufficient)
- **Overall:** ~5% performance impact

**Total Expected Impact:** < 5% (acceptable trade-off for security)

### Benchmarking:

```bash
# Before hardening
ab -n 1000 -c 10 https://yujix.com/api/health > /tmp/benchmark-before.txt

# After hardening
ab -n 1000 -c 10 https://yujix.com/api/health > /tmp/benchmark-after.txt

# Compare
diff /tmp/benchmark-before.txt /tmp/benchmark-after.txt
```

---

## Troubleshooting

### Issue: PHP-FPM won't start after hardening

```bash
# Check systemd status
systemctl status php8.2-fpm -l

# Check for specific errors
journalctl -xeu php8.2-fpm

# Common issues:
# 1. Incorrect ReadWritePaths - add missing directories
# 2. Missing /tmp/php-uploads - create it
# 3. Permission issues - check file ownership
```

### Issue: Application can't write to storage

```bash
# Verify ReadWritePaths includes storage
systemctl show php8.2-fpm.service | grep ReadWritePaths

# Should include: /var/www/yujix/shared/storage

# If missing, add to security.conf:
ReadWritePaths=/var/www/yujix/shared/storage

# Reload
systemctl daemon-reload
systemctl restart php8.2-fpm
```

### Issue: MySQL binary logs filling disk

```bash
# Check binary log size
du -sh /var/log/mysql/

# Manually purge old logs
mysql -u root -p -e "PURGE BINARY LOGS BEFORE NOW() - INTERVAL 3 DAY;"

# Adjust retention in config:
binlog_expire_logs_seconds = 259200  # 3 days instead of 7
```

### Issue: Slow queries increasing

```bash
# Check slow query log
tail -100 /var/log/mysql/slow-query.log

# Common causes:
# 1. Missing indexes - add indexes to frequently queried columns
# 2. Large table scans - optimize queries
# 3. Increase slow query threshold if false positives
```

---

## Security Validation

### PHP-FPM Security Checklist

- [ ] Systemd security score: ≤ 2.0 (SECURE)
- [ ] PHP version hidden from headers
- [ ] Dangerous functions disabled (exec, system, etc.)
- [ ] Read-only filesystem (except write paths)
- [ ] No execution in writable directories
- [ ] Private /tmp directory
- [ ] Limited capabilities
- [ ] System call filtering active
- [ ] Application functionality intact

### MySQL Security Checklist

- [ ] Bind address: 127.0.0.1 (localhost only)
- [ ] No remote connections possible
- [ ] Anonymous users removed
- [ ] Test database removed
- [ ] User privileges minimized
- [ ] LOAD DATA LOCAL INFILE disabled
- [ ] Binary logging enabled
- [ ] Backup user created with minimal privileges
- [ ] Application database access working

---

## Next Steps

After completing PHP-FPM and MySQL hardening:

- [ ] Monitor logs for 24-48 hours
- [ ] Run performance benchmarks
- [ ] Update monitoring alerts for new log locations
- [ ] Document any custom ReadWritePaths needed
- [ ] Schedule quarterly security audits

---

**Guide Created:** January 11, 2026
**Estimated Time:** 1 hour
**Downtime:** ~2 minutes total
**Security Impact:** Systemd 9.6 → 1.8, MySQL hardened, attack surface significantly reduced
