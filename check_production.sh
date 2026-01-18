#!/bin/bash

echo "=========================================="
echo "   Production API Diagnostic Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check 1: .env file
echo "1. Checking .env file..."
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    exit 1
else
    echo -e "${GREEN}✅ .env file exists${NC}"
    
    # Check critical environment variables
    if grep -q "APP_ENV=production" .env; then
        echo -e "${GREEN}✅ APP_ENV=production${NC}"
    else
        echo -e "${YELLOW}⚠️  APP_ENV not set to production${NC}"
    fi
    
    if grep -q "APP_DEBUG=false" .env; then
        echo -e "${GREEN}✅ APP_DEBUG=false${NC}"
    else
        echo -e "${YELLOW}⚠️  APP_DEBUG not false (may show errors)${NC}"
    fi
    
    if grep -q "RAZORPAY_KEY" .env && ! grep -q "RAZORPAY_KEY=$" .env; then
        echo -e "${GREEN}✅ Razorpay keys configured${NC}"
    else
        echo -e "${RED}❌ Razorpay keys missing or empty${NC}"
    fi
    
    if grep -q "DB_DATABASE" .env && ! grep -q "DB_DATABASE=$" .env; then
        echo -e "${GREEN}✅ Database configured${NC}"
    else
        echo -e "${RED}❌ Database not configured${NC}"
    fi
    
    if grep -q "APP_URL=https://yujix.com" .env; then
        echo -e "${GREEN}✅ APP_URL configured${NC}"
    else
        echo -e "${YELLOW}⚠️  APP_URL may not be set correctly${NC}"
    fi
fi

echo ""

# Check 2: Database connection
echo "2. Checking database connection..."
if command_exists php; then
    php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Database connected\n'; } catch(Exception \$e) { echo '❌ Database connection failed: ' . \$e->getMessage() . '\n'; }" 2>/dev/null
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""

# Check 3: Migrations
echo "3. Checking migrations..."
if command_exists php; then
    echo "Migration status:"
    php artisan migrate:status 2>/dev/null | tail -10
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""

# Check 4: Subscription plans
echo "4. Checking subscription plans..."
if command_exists php; then
    PLAN_COUNT=$(php artisan tinker --execute="echo App\Models\SubscriptionPlan::count();" 2>/dev/null | tail -1)
    if [ "$PLAN_COUNT" -ge 2 ]; then
        echo -e "${GREEN}✅ Subscription plans exist ($PLAN_COUNT plans)${NC}"
    else
        echo -e "${RED}❌ Subscription plans missing or incomplete ($PLAN_COUNT plans found, need at least 2)${NC}"
        echo "   Run: php artisan db:seed --class=SubscriptionPlanSeeder --force"
    fi
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""

# Check 5: Storage permissions
echo "5. Checking storage permissions..."
if [ -w storage/logs ]; then
    echo -e "${GREEN}✅ Storage writable${NC}"
else
    echo -e "${RED}❌ Storage not writable${NC}"
    echo "   Run: chmod -R 755 storage bootstrap/cache"
fi

if [ -w bootstrap/cache ]; then
    echo -e "${GREEN}✅ Bootstrap cache writable${NC}"
else
    echo -e "${RED}❌ Bootstrap cache not writable${NC}"
fi

echo ""

# Check 6: Laravel logs
echo "6. Checking Laravel logs (last 5 errors)..."
if [ -f storage/logs/laravel.log ]; then
    ERROR_COUNT=$(tail -1000 storage/logs/laravel.log | grep -i "error\|exception" | wc -l)
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo -e "${YELLOW}⚠️  Found $ERROR_COUNT recent errors in logs${NC}"
        echo "Recent errors:"
        tail -100 storage/logs/laravel.log | grep -i "error\|exception" | tail -5 | sed 's/^/   /'
    else
        echo -e "${GREEN}✅ No recent errors in logs${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Laravel log file not found${NC}"
fi

echo ""

# Check 7: PHP version and extensions
echo "7. Checking PHP version and extensions..."
if command_exists php; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    echo "PHP Version: $PHP_VERSION"
    
    REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "openssl" "json" "curl" "fileinfo")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "^$ext$"; then
            echo -e "${GREEN}✅ $ext extension loaded${NC}"
        else
            echo -e "${RED}❌ $ext extension missing${NC}"
        fi
    done
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""

# Check 8: Composer dependencies
echo "8. Checking Composer dependencies..."
if [ -f composer.json ] && [ -d vendor ]; then
    echo -e "${GREEN}✅ Composer dependencies installed${NC}"
    if [ -f composer.lock ]; then
        echo -e "${GREEN}✅ composer.lock exists${NC}"
    else
        echo -e "${YELLOW}⚠️  composer.lock missing${NC}"
    fi
else
    echo -e "${RED}❌ Composer dependencies not installed${NC}"
    echo "   Run: composer install --no-dev --optimize-autoloader"
fi

echo ""

# Check 9: Route cache
echo "9. Checking route cache..."
if command_exists php; then
    if php artisan route:list >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Routes accessible${NC}"
    else
        echo -e "${YELLOW}⚠️  Route issues detected${NC}"
        echo "   Run: php artisan route:clear"
    fi
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""

# Check 10: Database tables
echo "10. Checking critical database tables..."
if command_exists php; then
    TABLES=("meetings" "surveys" "contacts" "subscriptions" "subscription_plans" "organizations" "users")
    for table in "${TABLES[@]}"; do
        EXISTS=$(php artisan tinker --execute="echo Schema::hasTable('$table') ? '1' : '0';" 2>/dev/null | tail -1)
        if [ "$EXISTS" = "1" ]; then
            echo -e "${GREEN}✅ Table '$table' exists${NC}"
        else
            echo -e "${RED}❌ Table '$table' missing${NC}"
        fi
    done
else
    echo -e "${RED}❌ PHP not found${NC}"
fi

echo ""
echo "=========================================="
echo "   Diagnostic Complete"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Fix any ❌ errors shown above"
echo "2. Clear caches: php artisan config:clear && php artisan cache:clear"
echo "3. Check logs: tail -f storage/logs/laravel.log"
echo "4. Test APIs with proper authentication tokens"
echo ""

