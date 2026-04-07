---
description: Check code quality with PHP linting and static analysis
agent: review
model: alibaba-cn/qwen3.6-plus
---
Run code quality checks on the Laravel project.

Check for available tools:
!`composer show 2>/dev/null | grep -E "larastan|phpstan|phpcs|pint|rector" || echo "No static analysis tools found"`

Run available checks:
1. Laravel Pint (if available): `./vendor/bin/pint --test`
2. PHPStan/Larastan (if available): `./vendor/bin/phpstan analyse`
3. Rector (if available): `./vendor/bin/rector --dry-run`

If no tools are installed, suggest:
- `composer require --dev laravel/pint`
- `composer require --dev larastan/larastan`

Report any issues found with file paths and line numbers.
