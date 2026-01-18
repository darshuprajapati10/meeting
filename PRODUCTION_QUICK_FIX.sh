#!/bin/bash

echo "=========================================="
echo "   Production Quick Fix Script"
echo "=========================================="
echo ""
echo "This script will:"
echo "1. Clear all Laravel caches"
echo "2. Run database migrations"
echo "3. Seed subscription plans"
echo "4. Fix file permissions"
echo "5. Clear route cache"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "Step 1: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

echo "Step 2: Running database migrations..."
php artisan migrate --force
echo "✅ Migrations completed"
echo ""

echo "Step 3: Seeding subscription plans..."
php artisan db:seed --class=SubscriptionPlanSeeder --force
echo "✅ Subscription plans seeded"
echo ""

echo "Step 4: Fixing file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
echo "✅ Permissions fixed"
echo ""

echo "Step 5: Verifying subscription plans..."
PLAN_COUNT=$(php artisan tinker --execute="echo App\Models\SubscriptionPlan::count();" 2>/dev/null | tail -1)
if [ "$PLAN_COUNT" -ge 2 ]; then
    echo "✅ Subscription plans verified ($PLAN_COUNT plans)"
else
    echo "⚠️  Warning: Only $PLAN_COUNT subscription plans found (expected at least 2)"
fi

echo ""
echo "=========================================="
echo "   Quick Fix Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test your APIs"
echo "2. Check logs: tail -f storage/logs/laravel.log"
echo "3. If issues persist, run: ./check_production.sh"
echo ""

