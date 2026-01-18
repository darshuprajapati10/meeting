#!/bin/bash

# Shipping Page Production Deployment Script
# This script helps deploy the updated Shipping page to production

echo "=========================================="
echo "Shipping Page Production Deployment"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "package.json" ]; then
    echo -e "${RED}Error: package.json not found. Please run this script from the project root.${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Building production assets...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}Build failed! Please fix errors and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Build completed successfully${NC}"
echo ""

# Show the new Shipping build file
echo -e "${YELLOW}New Shipping build file:${NC}"
ls -lh public/build/assets/Shipping-*.js | tail -1
echo ""

echo -e "${YELLOW}Step 2: Files ready for deployment${NC}"
echo ""
echo "The following files need to be uploaded to production:"
echo "  - public/build/manifest.json"
echo "  - public/build/assets/Shipping-CADm0iBn.js"
echo "  - public/build/assets/app-D5rtGQk_.js (main app bundle)"
echo "  - public/build/assets/app-BQAnke0F.css (CSS bundle)"
echo ""
echo "OR upload the entire directory:"
echo "  - public/build/ (entire directory)"
echo ""

echo -e "${YELLOW}Step 3: After uploading, run these commands on production server:${NC}"
echo ""
echo "  cd /path/to/production"
echo "  php artisan cache:clear"
echo "  php artisan config:clear"
echo "  php artisan view:clear"
echo "  php artisan route:clear"
echo "  php artisan optimize:clear"
echo ""

echo -e "${YELLOW}Step 4: Test the deployment${NC}"
echo ""
echo "  1. Visit: https://yujix.com/shipping"
echo "  2. Hard refresh: Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)"
echo "  3. Verify content shows 'Digital Service Delivery' section"
echo ""

echo -e "${GREEN}Deployment files are ready!${NC}"
echo ""

