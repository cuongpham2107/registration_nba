# AGENTS.md – Hướng dẫn cho AI Coding Agents

## Ngôn ngữ & conventions

- Trả lời bằng tiếng Việt trừ khi được yêu cầu khác.
- Code comments viết bằng tiếng Anh.
- Commit messages tuân theo [Conventional Commits](https://www.conventionalcommits.org/).
- Không tự ý xóa file — hỏi trước. Đọc toàn bộ file trước khi sửa.

## Stack

- **Laravel 12** (Laravel 10 structure — middleware ở `app/Http/Middleware/`, providers ở `app/Providers/`, không có `bootstrap/app.php`)
- **Filament v5** admin panel: Resources tại `app/Filament/Resources/`, Pages tại `app/Filament/Pages/`
- **Livewire v4** single-file components (`.blade.php` với `<?php` block)
- **Tailwind CSS v4** với `@tailwindcss/vite` plugin
- **Vite** + **Bun** (dùng `bun` thay `npm`/`yarn`)
- **PHP 8.4**, **PHPUnit v11** (không dùng Pest)

## Domain

Đây là hệ thống **quản lý đăng ký (Registration)** với Filament admin panel.
Các model chính: `Registration`, `RegistrationEntry`, `Guest`, `CarCatalog`, `Card`, `Invoice`, `Fee`, `Company`, `Area`, `User`.

## Package đáng chú ý

- `filament-shield` — quản lý roles/permissions (model `Role`, `Permission`)
- `spatie/laravel-pdf` + `spatie/browsershot` — generate PDF hóa đơn
- `maatwebsite/excel` — import/export Excel
- `laravel/reverb` — WebSocket server
- `laravel/sanctum` — API auth
- `simplesoftwareio/simple-qrcode` — QR code
- `barryvdh/laravel-debugbar` — debug toolbar (dev only)

## Lệnh quan trọng

```bash
# Dev server (Bun + PHP + Reverb)
composer run dev

# Test: luôn dùng --compact, filter theo test hoặc file
php artisan test --compact
php artisan test --compact --filter=testName
php artisan test --compact tests/Feature/ExampleTest.php

# Format code trước khi commit
vendor/bin/pint --dirty --format agent

# Build assets
bun run build

# Production chạy qua PM2 (port 8010)
pm2 start pm2.json
```

## Quirks & gotchas

- **Laravel 10 structure**: Không migrate sang cấu trúc mới của Laravel 11/12. Middleware đăng ký trong `app/Http/Kernel.php`, exception handling trong `app/Exceptions/Handler.php`, console trong `app/Console/Kernel.php`.
- **`ID_DB_*` env vars**: User model dùng một connection database riêng (`ID_DB_HOST`, `ID_DB_DATABASE`, …). Đừng xóa các vars này trong `.env`.
- **Filament theme**: Custom theme tại `resources/css/filament/admin/theme.css` — nếu sửa Filament UI, cần chạy `bun run build`.
- **Filament namespaces**: Actions dùng `Filament\Actions\` (không dùng sub-namespace như `Filament\Tables\Actions\`). Form fields: `Filament\Forms\Components\`. Layout: `Filament\Schemas\Components\`. Icons: `Filament\Support\Icons\Heroicon` enum.
- **File visibility**: Filament file upload mặc định là `private`. Cần `->visibility('public')` nếu cần public access.
- **Database column migratons**: Khi sửa column trong migration, phải include tất cả attributes cũ, nếu không chúng sẽ bị mất.
- **Custom form components**: `AutocompleteHawb` và `Avatar` trong `app/Forms/Components/`.
- **Invoice download**: Route dùng signed URL middleware (`->middleware('signed')`).
- **No sqlite test isolation**: `phpunit.xml` KHÔNG dùng `sqlite :memory:` (bị comment out). Tests chạy trên database thật — đảm bảo DB đã được migrate và seed trước khi test.

## Khi không chắc

- Hỏi developer về business logic thay vì tự đoán.
- Với thay đổi lớn (>50 dòng), lập plan trước khi code.
- Ưu tiên giải pháp đơn giản, dễ maintain.
- Không tạo documentation files trừ khi được yêu cầu rõ ràng.
- Không thêm dependencies mới nếu chưa được duyệt.
