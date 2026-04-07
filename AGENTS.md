# AGENTS.md – Hướng dẫn cho AI Coding Agents

Tài liệu này cung cấp ngữ cảnh và quy tắc cho tất cả AI agent khi làm việc trong dự án này.

---

## Cấu trúc dự án

```
.
├── opencode.json               # Cấu hình OpenCode
├── AGENTS.md                   # File này – quy tắc cho agents
└── .opencode/
    ├── agents/                 # Custom agent definitions
    │   ├── review.md
    │   ├── debug.md
    │   ├── test-writer.md
    │   ├── refactor.md
    │   ├── docs-writer.md
    │   ├── security-audit.md
    │   └── git-helper.md
    └── prompts/                # System prompts cho từng agent
        ├── build.txt
        └── plan.txt
```

---

## Quy tắc chung

- **Ngôn ngữ**: Ưu tiên trả lời bằng tiếng Việt trừ khi được yêu cầu khác.
- **Code comments**: Viết bằng tiếng Anh để dễ dàng chia sẻ quốc tế.
- **Commit messages**: Tuân theo [Conventional Commits](https://www.conventionalcommits.org/).
- **Không tự ý xóa file**: Luôn hỏi trước khi xóa bất kỳ file nào.
- **Ưu tiên đọc trước khi sửa**: Đọc toàn bộ file trước khi thực hiện thay đổi.

---

## Quy trình làm việc

### Thêm tính năng mới

1. Dùng agent **Plan** để phân tích và lập kế hoạch
2. Review kế hoạch cùng developer
3. Chuyển sang **Build** để thực hiện
4. Gọi `@test-writer` để tạo tests
5. Gọi `@review` để kiểm tra chất lượng

### Sửa lỗi

1. Gọi `@debug` để điều tra nguyên nhân
2. Dùng **Build** để fix
3. Chạy tests để xác nhận

### Code review

1. Gọi `@review` với tên file hoặc diff cần review
2. Gọi `@security-audit` nếu có thay đổi liên quan đến auth/input

---

## Standards

- **Tests**: Phải có unit test cho business logic mới
- **Docs**: Cập nhật README nếu thay đổi API/interface công khai
- **Security**: Không commit API key, secret vào source code
- **Performance**: Tránh N+1 query, lazy load khi cần thiết

---

## Stack & Conventions

### Công nghệ sử dụng

- **Laravel 12**: Eloquent, Service Container, Form Requests, Policies, Queues, Events/Listeners
- **Filament v5**: Admin panels, Resources, Tables, Forms, Widgets, Clusters, Infolists
- **Livewire v4**: Single-file components (.blade.php), wire:model, wire:submit, #[Validate], #[Modelable]
- **Alpine.js v3**: x-data, x-show, x-on, x-bind, x-for, $wire integration với Livewire
- **Tailwind CSS v4**: Utility-first CSS, custom themes, @theme directive

### Quy ước đặt tên

- Models: Singular PascalCase (`User`, `ProductCategory`)
- Controllers: Plural PascalCase (`UsersController`, `ProductCategoriesController`)
- Filament Resources: Singular PascalCase + `Resource` suffix (`UserResource`)
- Livewire Components: PascalCase (`UserManagement`, `ProductList`)
- Migrations: Plural snake_case (`create_users_table`)
- Services/Actions: Singular PascalCase (`CreateUserService`, `GenerateReport`)

### Performance

- Luôn eager load relationships (`with()`) để tránh N+1 queries
- Sử dụng `chunk()` cho dataset lớn
- Cache các phép tính đắt đỏ
- Database indexes cho columns query thường xuyên

---

## Skills

Skills là các hướng dẫn chuyên biệt mà agents có thể load on-demand qua `skill` tool.

### Boost Skills (tự động kích hoạt bởi Laravel Boost)

| Skill | Khi nào dùng |
|-------|-------------|
| `laravel-best-practices` | Viết/review Laravel PHP code: controllers, models, migrations, form requests, policies, jobs, services, Eloquent queries |
| `tailwindcss-development` | Styling với Tailwind: grid layouts, flex, components, dark mode, responsive |
| `laravel-pdf` | Generate PDFs từ Blade views qua spatie/laravel-pdf |
| `debug-using-debugbar` | Debug slow pages, N+1 queries, exceptions qua Laravel Debugbar |

### Project Skills (`.opencode/skills/`)

| Skill | Khi nào dùng |
|-------|-------------|
| `laravel12` | Reference Laravel 12 patterns |
| `filament-v5` | Reference Filament admin patterns |
| `livewire-v4` | Reference Livewire v4 SFC patterns |
| `alpine-v3` | Reference Alpine.js reactivity |
| `tailwind-v4` | Tailwind CSS v4 utilities, @theme directive |

### Hướng dẫn cài đặt Project Skills

Khi cần tạo skill riêng cho dự án, tạo theo cấu trúc:

```
.opencode/skills/<tên-skill>/SKILL.md
```

Mỗi `SKILL.md` cần có YAML frontmatter:

```markdown
---
name: ten-skill
description: Mô tả ngắn gọn skill này làm gì
license: MIT
---

## Nội dung hướng dẫn
...
```

**Quy tắc đặt tên skill:**
- Viết thường, dùng dấu gạch ngang: `project-laravel`, `project-filament`
- Độ dài: 1-64 ký tự
- Không bắt đầu hoặc kết thúc bằng `-`

**Cài skill từ internet:**
```bash
opencode skill install <tên-skill>
```

**Khi nào nên tạo project skill:**
- Có conventions riêng cho dự án
- Có patterns lặp lại nhiều lần
- Cần hướng dẫn chi tiết cho agents về business logic

---

## Custom Commands

Custom commands giúp chạy nhanh các tác vụ lặp lại qua `/ten-command` trong TUI.

| Command | Mô tả | Agent |
|---------|-------|-------|
| `/test` | Chạy tests với coverage | build |
| `/review` | Review recent code changes | review |
| `/commit` | Tạo commit với conventional message | git-helper |
| `/filament-resource <Model>` | Tạo Filament resource | build |
| `/livewire <Component>` | Tạo Livewire component | build |
| `/migrate <table>` | Tạo database migration | build |
| `/seed [Seeder]` | Seed database | build |
| `/lint` | Code quality checks | review |
| `/cache-clear` | Clear all Laravel caches | build |

### Ví dụ sử dụng

```
/test                          # Chạy tests
/filament-resource User        # Tạo UserResource
/livewire UserManagement       # Tạo UserManagement component
/migrate create_orders_table   # Tạo migration
/seed                          # Chạy seeder
/commit                        # Tạo commit
```

---

## Lệnh thường dùng

```bash
# Cài đặt dependencies
composer install

# Chạy development server
php artisan serve

# Chạy tests
php artisan test

# Build production
npm run build

# Tạo resource filament
php artisan make:filament-resource User --generate

# Tạo custom component livewire 4
php artisan make:livewire NameComponent
```

---

## Ghi chú đặc biệt cho agents

- Khi không chắc về business logic, hỏi developer thay vì tự đoán
- Với các thay đổi lớn (>50 dòng), tạo plan trước khi code
- Luôn kiểm tra edge case và error handling
- Ưu tiên giải pháp đơn giản, dễ maintain hơn là clever code

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v13
- laravel-echo (ECHO) - v2
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.
- `laravel-pdf` — Generate PDFs from Blade views or HTML using spatie/laravel-pdf. Covers creating, formatting, saving, downloading, and testing PDFs with the Browsershot, Cloudflare, or DOMPDF driver.
- `debug-using-debugbar` — Use this skill to optimize requests or debug Laravel application issues — slow pages, N+1 queries, exceptions, failed requests, or unexpected behavior — by inspecting data captured by Laravel Debugbar via Artisan CLI commands. Use when the user asks to investigate a bug, diagnose a slow request, find duplicate queries, check what happened on a previous request, or optimize database performance, even if they don't explicitly mention "debugbar" or "profiling."

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the new Laravel structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow the existing conventions for how and where it is implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Always inspect required options before running a command, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with an optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data))

</code-snippet>

### Testing

Always authenticate before testing panel functionality. Filament uses Livewire, so use `Livewire::test()` or `livewire()` (available when `pestphp/pest-plugin-livewire` is in `composer.json`):

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

<code-snippet name="Calling actions in pages" lang="php">
use Filament\Actions\DeleteAction;
use function Pest\Livewire\livewire;

livewire(EditUser::class, ['record' => $user->id])
    ->callAction(DeleteAction::class)
    ->assertNotified()
    ->assertRedirect();

</code-snippet>

<code-snippet name="Calling actions in tables" lang="php">
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, and `Fieldset` do not span all columns by default. Explicitly set column spans when needed.

</laravel-boost-guidelines>
