# Backblaze B2 Off-Site Encrypted Backup Setup Guide

**Server:** 157.245.97.43 (yujix.com)
**Purpose:** Disaster recovery with encrypted off-site backups
**Cost:** $0.005/GB/month (10GB free tier)

---

## Overview

This guide sets up automated, encrypted, off-site backups to Backblaze B2. Backups are:
- **Encrypted** with GPG before upload (zero-knowledge encryption)
- **Automated** daily at 2:00 AM UTC
- **Retained** for 30 days on B2
- **Disaster-proof** - stored off-site, survives complete server loss

---

## Prerequisites

### 1. Backblaze B2 Account

**Sign up:** https://www.backblaze.com/b2/sign-up.html

**Pricing:**
- First 10GB: FREE
- Beyond 10GB: $0.005/GB/month ($5/TB/month)
- Download: $0.01/GB (first 1GB/day free)

**Estimated Cost for Yujix:**
- Database: ~50MB compressed
- Files: ~200MB compressed
- Total: ~250MB per backup
- 30 backups (30 days): ~7.5GB
- **Monthly Cost: $0 (within free tier)**

### 2. Create B2 Bucket

1. Log in to Backblaze B2 dashboard
2. Navigate to "Buckets" → "Create a Bucket"
3. Bucket settings:
   - **Name:** `yujix-production-backups`
   - **Files in Bucket:** Private
   - **Default Encryption:** Disabled (we encrypt locally with GPG)
   - **Object Lock:** Disabled
4. Click "Create a Bucket"

### 3. Create Application Key

1. Navigate to "App Keys" → "Add a New Application Key"
2. Settings:
   - **Name of Key:** `yujix-backup-key`
   - **Allow access to Bucket(s):** `yujix-production-backups`
   - **Type of Access:** Read and Write
   - **Allow List All Bucket Names:** Optional
   - **File name prefix:** (leave empty)
   - **Duration:** (leave empty for no expiration)
3. Click "Create New Key"
4. **IMPORTANT:** Copy both values immediately (shown only once):
   - `keyID`: e.g., `005abc123def456789000001`
   - `applicationKey`: e.g., `K005XYZ...` (long string)

---

## Server Setup

### Step 1: Install Required Packages

```bash
ssh root@157.245.97.43

# Install Python 3 and pip (if not installed)
apt update
apt install -y python3 python3-pip gnupg

# Install Backblaze B2 CLI
pip3 install b2
```

**Verify installation:**
```bash
b2 version
# Should show: b2 command line tool, version X.X.X
```

### Step 2: Generate GPG Encryption Key

```bash
# Generate GPG key for encryption
gpg --generate-key
```

**Prompts and Answers:**
```
Your selection? 1  # (1) RSA and RSA (default)
What keysize do you want? 3072  # Default
Key is valid for? 0  # Key does not expire
Is this correct? y

Real name: Yujix Backup
Email address: admin@yujix.com
Comment: Production backup encryption key
Change (N)ame, (C)omment, (E)mail or (O)kay/(Q)uit? O

Enter passphrase: [LEAVE EMPTY - press Enter]
Repeat passphrase: [LEAVE EMPTY - press Enter]
```

**IMPORTANT:** Leave passphrase empty for automated backups.

**Verify key created:**
```bash
gpg --list-keys
# Should show: admin@yujix.com
```

### Step 3: Authorize B2 CLI

```bash
# Authorize with your Application Key
b2 authorize-account <keyID> <applicationKey>
```

**Example:**
```bash
b2 authorize-account 005abc123def456789000001 K005XYZ...your-long-key...
```

**Expected output:**
```
Using https://api005.backblazeb2.com
Using bucket yujix-production-backups
```

**Verify authorization:**
```bash
b2 get-account-info
# Should show your account details
```

### Step 4: Deploy Backup Scripts

```bash
# Copy scripts from deployment repo
cd /root

# Create backup scripts (or copy from deployment folder)
# - backup-yujix.sh (already exists)
# - backup-to-b2.sh (new)
# - restore-from-b2.sh (new)

# Make scripts executable
chmod +x /root/backup-yujix.sh
chmod +x /root/backup-to-b2.sh
chmod +x /root/restore-from-b2.sh
```

**Script locations:**
- `/root/backup-yujix.sh` - Local backup (already exists)
- `/root/backup-to-b2.sh` - B2 encrypted upload
- `/root/restore-from-b2.sh` - B2 download and restore

### Step 5: Test Manual Backup

```bash
# Run manual backup to B2
/root/backup-to-b2.sh
```

**Expected output:**
```
===========================================
Backblaze B2 Encrypted Backup Started
===========================================
[timestamp] Step 1: Running local backup...
[timestamp] Step 2: Encrypting database backup...
[timestamp] Database encrypted: 52M -> 52M
[timestamp] Step 3: Encrypting files backup...
[timestamp] Files encrypted: 198M -> 198M
[timestamp] Step 4: Uploading database to Backblaze B2...
[timestamp] ✅ Database uploaded to B2: database/yujix-db-20260111-140000.sql.gz.gpg
[timestamp] Step 5: Uploading files to Backblaze B2...
[timestamp] ✅ Files uploaded to B2: files/yujix-files-20260111-140000.tar.gz.gpg
[timestamp] Step 6: Cleaning up local encrypted files...
[timestamp] ✅ Local encrypted files removed
[timestamp] Step 7: Cleaning up old B2 backups...
[timestamp] ✅ B2 cleanup complete
===========================================
✅ Encrypted off-site backup completed successfully
===========================================
```

**Verify upload in B2:**
```bash
# List backups in B2
b2 ls --recursive yujix-production-backups

# Should show:
# database/yujix-db-20260111-140000.sql.gz.gpg
# files/yujix-files-20260111-140000.tar.gz.gpg
```

### Step 6: Schedule Automated Daily Backups

```bash
# Edit crontab
crontab -e
```

**Remove old local backup cron:**
```
# Remove or comment out:
# 0 2 * * * /root/backup-yujix.sh >> /var/log/yujix-backup.log 2>&1
```

**Add B2 backup cron:**
```
# Daily encrypted backup to Backblaze B2 at 2:00 AM UTC
0 2 * * * /root/backup-to-b2.sh >> /var/log/yujix-backup.log 2>&1
```

**Save and exit** (`:wq` in vim)

**Verify crontab:**
```bash
crontab -l
# Should show the new B2 backup cron
```

---

## Testing Disaster Recovery

### Test 1: List Available Backups

```bash
/root/restore-from-b2.sh

# Should list all backups with timestamps
```

### Test 2: Test Restore (on Staging Server)

**DO NOT test restore on production!** Test on staging first.

```bash
# On staging server:
/root/restore-from-b2.sh 20260111-140000

# Follow prompts to restore database and files
```

### Test 3: Verify Restored Data

```bash
# Check database connection
mysql -u yujix_user -p -e "USE yujix_db; SELECT COUNT(*) FROM users;"

# Check files exist
ls -la /var/www/yujix/shared/storage

# Check application works
curl https://staging.yujix.com/api/health
```

---

## Monitoring & Maintenance

### Check Backup Logs

```bash
# View recent backup logs
tail -100 /var/log/yujix-backup.log

# Check for errors
grep -i error /var/log/yujix-backup.log
```

### Check B2 Storage Usage

```bash
# List all backups
b2 ls --recursive yujix-production-backups

# Count backups
b2 ls --recursive yujix-production-backups database/ | wc -l
b2 ls --recursive yujix-production-backups files/ | wc -l
```

**Expected:**
- ~30 database backups (one per day, 30-day retention)
- ~30 files backups

### Check B2 Costs

1. Log in to Backblaze B2 dashboard
2. Navigate to "Billing"
3. Check current month usage and costs

**Expected monthly cost:** $0 (within 10GB free tier)

### Manual Cleanup (if needed)

```bash
# Delete specific backup
b2 delete-file-version <filename> <fileId>

# List old backups (older than 60 days)
b2 ls --long --recursive yujix-production-backups | grep "2025-11"
```

---

## Security Considerations

### Encryption

✅ **Local GPG Encryption:** All backups encrypted before upload
✅ **Zero-Knowledge:** Backblaze cannot decrypt your backups
✅ **No Passphrase:** Automated backups (trade-off for convenience)

**Improving Security (Optional):**
- Use GPG key with passphrase (requires manual intervention)
- Store GPG private key in hardware security module
- Enable Backblaze Object Lock (immutable backups)

### Access Control

✅ **Private Bucket:** Backups not publicly accessible
✅ **Application Key:** Limited to specific bucket (read/write only)
✅ **No List Permission:** Optional - key can't list all buckets

**Recommended:**
- Rotate B2 application key every 90 days
- Use separate B2 key for restore operations (read-only)
- Enable 2FA on Backblaze account

---

## Disaster Recovery Procedure

### Scenario: Complete Server Loss

**Recovery Time Objective (RTO):** 2-4 hours
**Recovery Point Objective (RPO):** 24 hours (daily backups)

### Recovery Steps:

1. **Provision New Server**
   - Ubuntu 22.04
   - Same configuration as production

2. **Install Base Software**
   ```bash
   # Install nginx, PHP, MySQL, etc.
   # (Use original setup scripts)
   ```

3. **Install B2 CLI and GPG**
   ```bash
   apt install -y python3-pip gnupg
   pip3 install b2
   ```

4. **Restore GPG Private Key**
   ```bash
   # Import GPG key from secure backup location
   gpg --import /path/to/backup-private-key.asc
   ```

5. **Authorize B2**
   ```bash
   b2 authorize-account <keyID> <applicationKey>
   ```

6. **Download and Run Restore Script**
   ```bash
   # Get latest backup timestamp
   /root/restore-from-b2.sh

   # Restore from latest backup
   /root/restore-from-b2.sh 20260111-020000
   ```

7. **Verify Application**
   ```bash
   # Test health endpoint
   curl https://yujix.com/api/health

   # Test login
   curl -X POST https://yujix.com/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

8. **Update DNS** (if IP changed)
   - Point yujix.com to new server IP
   - Wait for DNS propagation

---

## Troubleshooting

### Error: "b2 command not found"

```bash
# Install b2-cli
pip3 install b2

# Add to PATH
export PATH="$PATH:/usr/local/bin"
```

### Error: "GPG key not found"

```bash
# List GPG keys
gpg --list-keys

# If missing, regenerate key
gpg --generate-key
```

### Error: "B2 authorization failed"

```bash
# Re-authorize with correct credentials
b2 clear-account
b2 authorize-account <keyID> <applicationKey>
```

### Error: "Upload failed"

```bash
# Check internet connection
ping backblazeb2.com

# Check B2 service status
# Visit: https://status.backblaze.com

# Retry upload
/root/backup-to-b2.sh
```

### Error: "Decryption failed"

```bash
# Ensure correct GPG key is imported
gpg --list-secret-keys

# Import private key if missing
gpg --import /path/to/private-key.asc
```

---

## Cost Optimization

### Current Setup:
- **30-day retention** = ~7.5GB
- **Cost:** $0/month (free tier)

### If Exceeding Free Tier:

**Option 1: Reduce Retention**
```bash
# Edit backup-to-b2.sh
# Change: B2_RETENTION_DAYS=30
# To: B2_RETENTION_DAYS=14

# This halves storage: ~3.75GB
```

**Option 2: Weekly Backups Only**
```bash
# Edit crontab
# Change daily backup to weekly (Sunday only)
0 2 * * 0 /root/backup-to-b2.sh >> /var/log/yujix-backup.log 2>&1

# This uses ~1GB (4 weekly backups)
```

**Option 3: Database Only**
```bash
# Upload only database backups (skip files)
# Modify backup-to-b2.sh to skip files section

# This uses ~1.5GB (database only, 30 days)
```

---

## GPG Key Backup (CRITICAL)

**IMPORTANT:** Backup your GPG private key securely!

### Export Private Key:

```bash
# Export private key
gpg --export-secret-keys -a admin@yujix.com > /root/backup-gpg-private-key.asc

# Secure the file
chmod 600 /root/backup-gpg-private-key.asc
```

### Store Securely:

**Option 1: Password Manager**
- Copy contents of `backup-gpg-private-key.asc`
- Store in password manager (1Password, Bitwarden, etc.)

**Option 2: Encrypted USB Drive**
- Copy to encrypted USB drive
- Store in secure location

**Option 3: Paper Backup**
- Print the key
- Store in safe or safety deposit box

**Without the private key, you CANNOT restore backups!**

---

## Next Steps

After B2 setup is complete:

- [x] B2 account created
- [x] B2 bucket created
- [x] Application key generated
- [x] B2 CLI installed and authorized
- [x] GPG key generated
- [x] Backup scripts deployed
- [x] Test backup completed
- [x] Automated cron scheduled
- [x] GPG private key backed up
- [ ] Test disaster recovery on staging
- [ ] Document recovery procedure
- [ ] Train team on restore process

---

**Setup Guide Created:** January 11, 2026
**Status:** Ready for Implementation
**Estimated Setup Time:** 30 minutes
**Monthly Cost:** $0 (within free tier)
