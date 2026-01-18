#!/bin/bash

# Production Deployment Script
# Yeh script production server par run karna hai

echo "=========================================="
echo "🚀 Production Deployment - Pricing Page"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check if we're in production directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file nahi mila. Production directory mein hain?${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Pulling latest code from git...${NC}"
git pull origin main

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Git pull failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Code updated${NC}"
echo ""

echo -e "${YELLOW}Step 2: Installing/updating dependencies...${NC}"
composer install --no-dev --optimize-autoloader --quiet

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer install failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ PHP dependencies installed${NC}"
echo ""

echo -e "${YELLOW}Step 3: Installing npm dependencies...${NC}"
npm install --omit=dev --quiet

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ npm install failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ npm dependencies installed${NC}"
echo ""

echo -e "${YELLOW}Step 4: Building production assets...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Build failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Assets built successfully${NC}"
echo ""

# Verify Pricing file
PRICING_FILE=$(ls public/build/assets/Pricing-*.js 2>/dev/null | head -1)
if [ -z "$PRICING_FILE" ]; then
    echo -e "${RED}❌ Pricing file nahi mila!${NC}"
    exit 1
fi

echo -e "${BLUE}📦 New Pricing file:${NC}"
ls -lh "$PRICING_FILE" | awk '{print "  " $9 " (" $5 ")"}'
echo ""

echo -e "${YELLOW}Step 5: Clearing Laravel caches...${NC}"
php artisan cache:clear --quiet
php artisan config:clear --quiet
php artisan view:clear --quiet
php artisan route:clear --quiet
php artisan optimize:clear --quiet

echo -e "${GREEN}✅ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 6: Setting file permissions...${NC}"
chmod -R 755 public/build/ 2>/dev/null
# Fix storage and cache permissions for logging
chown -R deploy:www-data storage bootstrap/cache 2>/dev/null || echo "  (ownership unchanged)"
find storage -type d -exec chmod 775 {} \; 2>/dev/null
find storage -type f -exec chmod 664 {} \; 2>/dev/null
find bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null
find bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

echo -e "${YELLOW}Step 7: Restarting PHP-FPM...${NC}"
if command -v systemctl &> /dev/null; then
    sudo systemctl restart php8.2-fpm 2>/dev/null || sudo systemctl restart php8.3-fpm 2>/dev/null || echo "PHP-FPM restart skipped"
elif command -v service &> /dev/null; then
    sudo service php8.2-fpm restart 2>/dev/null || sudo service php8.3-fpm restart 2>/dev/null || echo "PHP-FPM restart skipped"
else
    echo "PHP-FPM restart skipped (service command not found)"
fi
echo ""

echo -e "${BLUE}=========================================="
echo "✅ DEPLOYMENT COMPLETE!"
echo "==========================================${NC}"
echo ""
echo -e "${GREEN}Sab kuch ready hai!${NC}"
echo ""
echo "Ab test karein:"
echo "1. Visit: https://yujix.com/pricing"
echo "2. Hard refresh: Cmd+Shift+R (Mac) ya Ctrl+Shift+R (Windows)"
echo "3. Verify: 2 plans dikhne chahiye (FREE + PRO) ₹ prices ke saath"
echo ""



