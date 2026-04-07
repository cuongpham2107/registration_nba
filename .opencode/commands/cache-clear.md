---
description: Clear all Laravel caches
agent: build
model: alibaba-cn/qwen3.6-plus
---
Clear all Laravel application caches.

Run the following commands:
1. `php artisan config:clear`
2. `php artisan cache:clear`
3. `php artisan route:clear`
4. `php artisan view:clear`
5. `php artisan event:clear`

Then verify with:
!`php artisan optimize:clear`

Report the status of each cache clearing operation.
