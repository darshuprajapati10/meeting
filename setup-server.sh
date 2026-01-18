#!/bin/bash

###############################################################################
# Digital Ocean Server Setup Script for Yujix API
#
# This script provisions a fresh Ubuntu server with all required software
# for running the Laravel 12 application.
#
# Usage: bash setup-server.sh
###############################################################################

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/yujix"
DEPLOY_USER="deploy"
DB_NAME="yujix_production"
DB_USER="yujix_user"
PHP_VERSION="8.2"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Yujix API - Server Setup${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Prompt for database password
read -sp "Enter MySQL password for ${DB_USER}: " DB_PASSWORD
echo ""
read -sp "Confirm MySQL password: " DB_PASSWORD_CONFIRM
echo ""

if [ "$DB_PASSWORD" != "$DB_PASSWORD_CONFIRM" ]; then
    echo -e "${RED}Passwords do not match. Exiting.${NC}"
    exit 1
fi

echo -e "${YELLOW}Starting server setup...${NC}"
echo ""

# Update system
echo -e "${GREEN}[1/10] Updating system packages...${NC}"
apt update && apt upgrade -y
apt install -y software-properties-common curl wget git unzip vim htop ufw fail2ban

# Install Nginx
echo -e "${GREEN}[2/10] Installing Nginx...${NC}"
apt install -y nginx
systemctl start nginx
systemctl enable nginx

# Add PHP repository and install PHP
echo -e "${GREEN}[3/10] Installing PHP ${PHP_VERSION}...${NC}"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml \
    php${PHP_VERSION}-curl php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-gd php${PHP_VERSION}-redis php${PHP_VERSION}-intl \
    php${PHP_VERSION}-soap php${PHP_VERSION}-opcache

# Configure PHP
echo -e "${YELLOW}Configuring PHP...${NC}"
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/;max_execution_time = 30/max_execution_time = 300/' /etc/php/${PHP_VERSION}/fpm/php.ini

systemctl restart php${PHP_VERSION}-fpm
systemctl enable php${PHP_VERSION}-fpm

# Install MySQL
echo -e "${GREEN}[4/10] Installing MySQL...${NC}"
apt install -y mysql-server

# Secure MySQL and create database
echo -e "${YELLOW}Configuring MySQL...${NC}"
mysql <<MYSQL_SCRIPT
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
CREATE DATABASE ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo -e "${GREEN}MySQL configured successfully${NC}"

# Install Redis
echo -e "${GREEN}[5/10] Installing Redis...${NC}"
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# Test Redis
if redis-cli ping | grep -q PONG; then
    echo -e "${GREEN}Redis is running${NC}"
else
    echo -e "${RED}Redis installation failed${NC}"
    exit 1
fi

# Install Node.js
echo -e "${GREEN}[6/10] Installing Node.js 20.x LTS...${NC}"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs build-essential

echo -e "${GREEN}Node.js version: $(node -v)${NC}"
echo -e "${GREEN}npm version: $(npm -v)${NC}"

# Install Composer
echo -e "${GREEN}[7/10] Installing Composer...${NC}"
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

echo -e "${GREEN}Composer version: $(composer --version)${NC}"

# Install Supervisor
echo -e "${GREEN}[8/10] Installing Supervisor...${NC}"
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor

# Create deploy user
echo -e "${GREEN}[9/10] Creating deploy user...${NC}"
if id "${DEPLOY_USER}" &>/dev/null; then
    echo -e "${YELLOW}User ${DEPLOY_USER} already exists${NC}"
else
    adduser --disabled-password --gecos "" ${DEPLOY_USER}
    usermod -aG www-data ${DEPLOY_USER}

    # Copy SSH keys from root
    mkdir -p /home/${DEPLOY_USER}/.ssh
    if [ -f /root/.ssh/authorized_keys ]; then
        cp /root/.ssh/authorized_keys /home/${DEPLOY_USER}/.ssh/
    fi
    chown -R ${DEPLOY_USER}:${DEPLOY_USER} /home/${DEPLOY_USER}/.ssh
    chmod 700 /home/${DEPLOY_USER}/.ssh
    chmod 600 /home/${DEPLOY_USER}/.ssh/authorized_keys 2>/dev/null || true

    echo -e "${GREEN}User ${DEPLOY_USER} created${NC}"
fi

# Create application directory
echo -e "${YELLOW}Creating application directory...${NC}"
mkdir -p ${APP_DIR}
chown -R ${DEPLOY_USER}:www-data ${APP_DIR}
chmod 755 ${APP_DIR}

# Configure UFW Firewall
echo -e "${GREEN}[10/10] Configuring firewall...${NC}"
ufw --force enable
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw allow 80/tcp
ufw allow 443/tcp

echo -e "${GREEN}Firewall configured${NC}"
ufw status

# Configure Fail2Ban
echo -e "${YELLOW}Configuring Fail2Ban...${NC}"
if [ ! -f /etc/fail2ban/jail.local ]; then
    cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
fi
systemctl enable fail2ban
systemctl start fail2ban

# Create sudo permissions for deploy user
echo -e "${YELLOW}Configuring sudo permissions for deploy user...${NC}"
cat > /etc/sudoers.d/${DEPLOY_USER} <<EOF
${DEPLOY_USER} ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
${DEPLOY_USER} ALL=(ALL) NOPASSWD: /bin/systemctl restart php${PHP_VERSION}-fpm
${DEPLOY_USER} ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
${DEPLOY_USER} ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
EOF

chmod 440 /etc/sudoers.d/${DEPLOY_USER}

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Server Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${GREEN}Installed Software:${NC}"
echo -e "  - Nginx: $(nginx -v 2>&1 | grep -oP 'nginx/\K[0-9.]+')"
echo -e "  - PHP: $(php -v | head -n 1 | grep -oP 'PHP \K[0-9.]+')"
echo -e "  - MySQL: $(mysql --version | grep -oP 'Distrib \K[0-9.]+')"
echo -e "  - Redis: $(redis-server --version | grep -oP 'v=\K[0-9.]+')"
echo -e "  - Node.js: $(node -v)"
echo -e "  - npm: $(npm -v)"
echo -e "  - Composer: $(composer --version --no-ansi | grep -oP 'Composer version \K[0-9.]+')"
echo ""
echo -e "${GREEN}Database Configuration:${NC}"
echo -e "  - Database: ${DB_NAME}"
echo -e "  - User: ${DB_USER}"
echo -e "  - Password: [hidden]"
echo ""
echo -e "${GREEN}Application Directory:${NC}"
echo -e "  - Path: ${APP_DIR}"
echo -e "  - Owner: ${DEPLOY_USER}:www-data"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "  1. Save your database password securely"
echo -e "  2. Configure Nginx for yujix.com domain"
echo -e "  3. Clone repository to ${APP_DIR}"
echo -e "  4. Create .env file with production configuration"
echo -e "  5. Run deployment steps"
echo -e "  6. Install SSL certificate with Certbot"
echo ""
echo -e "${GREEN}Database Password: ${DB_PASSWORD}${NC}"
echo -e "${YELLOW}(Save this password - you'll need it for .env configuration)${NC}"
echo ""
