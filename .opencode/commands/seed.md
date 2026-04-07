---
description: Seed the database with test data
agent: build
model: alibaba-cn/qwen3.6-plus
---
Run database seeders to populate test data.

Current database status:
!`php artisan db:show 2>/dev/null || echo "Database info not available"`

Available seeders:
!`ls -1 database/seeders/ 2>/dev/null || echo "No seeders directory found"`

Options:
1. Run all seeders: `php artisan db:seed`
2. Run specific seeder: `php artisan db:seed --class=$ARGUMENTS`
3. Refresh and seed: `php artisan migrate:fresh --seed`

If $ARGUMENTS is provided, run that specific seeder.
Otherwise, ask the user which option they prefer.
