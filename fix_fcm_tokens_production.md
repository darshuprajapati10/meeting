# Fix FCM Tokens Migration in Production

## Problem
The migration `2025_11_22_084542_create_fcm_tokens_table` fails in production because MySQL doesn't allow TEXT/BLOB columns in unique indexes without a key length.

## Solution Steps for Production

### Step 1: Ensure Fixed Migration is Deployed
Make sure the updated migration file is in production. The fix changes:
- `$table->text('token');` → `$table->string('token', 500);`

### Step 2: Check if Table Exists in Production
Run this command in production:
```bash
php artisan tinker --execute="echo Schema::hasTable('fcm_tokens') ? 'Table exists' : 'Table does not exist';"
```

### Step 3A: If Table Exists (Partially Created)
If the table exists but the migration failed, you need to:

1. **Drop the existing table:**
```bash
php artisan tinker --execute="Schema::dropIfExists('fcm_tokens'); echo 'Table dropped';"
```

2. **Remove the migration record:**
```bash
php artisan tinker --execute="DB::table('migrations')->where('migration', '2025_11_22_084542_create_fcm_tokens_table')->delete(); echo 'Migration record removed';"
```

3. **Run the migration again:**
```bash
php artisan migrate --path=database/migrations/2025_11_22_084542_create_fcm_tokens_table.php
```

### Step 3B: If Table Does Not Exist
If the table doesn't exist:

1. **Remove the migration record (if it exists):**
```bash
php artisan tinker --execute="DB::table('migrations')->where('migration', '2025_11_22_084542_create_fcm_tokens_table')->delete(); echo 'Migration record removed';"
```

2. **Run the migration:**
```bash
php artisan migrate --path=database/migrations/2025_11_22_084542_create_fcm_tokens_table.php
```

### Step 4: Verify the Fix
Verify the table structure:
```bash
php artisan tinker --execute="use Illuminate\Support\Facades\DB; \$columns = DB::select('DESCRIBE fcm_tokens'); foreach(\$columns as \$col) { echo \$col->Field . ' - ' . \$col->Type . PHP_EOL; }"
```

You should see:
- `token - varchar(500)` (NOT `text`)

## Quick Fix Script (All-in-One)

Run this single command to fix everything:

```bash
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Drop table if exists
Schema::dropIfExists('fcm_tokens');
echo 'Table dropped (if existed)' . PHP_EOL;

// Remove migration record
DB::table('migrations')->where('migration', '2025_11_22_084542_create_fcm_tokens_table')->delete();
echo 'Migration record removed' . PHP_EOL;

echo 'Ready to run: php artisan migrate --path=database/migrations/2025_11_22_084542_create_fcm_tokens_table.php' . PHP_EOL;
"
```

Then run:
```bash
php artisan migrate --path=database/migrations/2025_11_22_084542_create_fcm_tokens_table.php
```

## Alternative: Manual SQL Fix (If Needed)

If you prefer to fix the table manually without dropping it:

```sql
-- Check current structure
DESCRIBE fcm_tokens;

-- Drop the unique index if it exists
ALTER TABLE fcm_tokens DROP INDEX fcm_tokens_user_id_token_unique;

-- Change token column from TEXT to VARCHAR(500)
ALTER TABLE fcm_tokens MODIFY COLUMN token VARCHAR(500) NOT NULL;

-- Recreate the unique index
ALTER TABLE fcm_tokens ADD UNIQUE KEY fcm_tokens_user_id_token_unique (user_id, token);
```

Then mark the migration as run:
```bash
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2025_11_22_084542_create_fcm_tokens_table', 'batch' => DB::table('migrations')->max('batch') + 1]);"
```

## Verification

After fixing, verify everything works:
```bash
php artisan migrate:status | grep fcm_tokens
```

Should show: `2025_11_22_084542_create_fcm_tokens_table .................... DONE`

