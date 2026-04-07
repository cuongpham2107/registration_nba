---
description: Create a new database migration
agent: build
model: alibaba-cn/qwen3.6-plus
---
Create a new database migration for: $ARGUMENTS

Run: `php artisan make:migration create_$ARGUMENTS_table`

After creation:
1. Review the generated migration file
2. Add proper column types and constraints
3. Add indexes for frequently queried columns
4. Include foreign key relationships
5. Add timestamps if needed
6. Ensure proper naming conventions

Table name: $ARGUMENTS (use plural snake_case)
