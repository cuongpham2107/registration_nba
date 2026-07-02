# Violation Reports Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create `violation_reports` table + Filament resource + Gemini OCR action

**Architecture:** Single table with 3 JSON columns (reporters/witnesses/violators), flattened incident details. Gemini OCR as a service class called from a custom header action on the list page.

**Tech Stack:** Laravel 11, Filament v3, Guzzle HTTP, Gemini 3.5 Flash API

---

## File Map

- Create: `database/migrations/2026_07_02_000001_create_violation_reports_table.php`
- Create: `app/Models/ViolationReport.php`
- Create: `app/Services/GeminiOcrService.php`
- Create: `config/services.php` (modify — add gemini config)
- Create: `app/Filament/Resources/ViolationReportResource.php`
- Create: `app/Filament/Resources/ViolationReportResource/Pages/ListViolationReports.php`
- Create: `app/Filament/Resources/ViolationReportResource/Pages/CreateViolationReport.php`
- Create: `app/Filament/Resources/ViolationReportResource/Pages/EditViolationReport.php`
- Modify: `.env` — add `GEMINI_API_KEY`

---

### Task 1: Create migration + run

- [ ] **Create the migration file**

File: `database/migrations/2026_07_02_000001_create_violation_reports_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_reports', function (Blueprint $table) {
            $table->id();
            $table->dateTime('recorded_at');
            $table->string('location');
            $table->json('reporters');
            $table->json('witnesses');
            $table->json('violators');
            $table->string('target')->nullable();
            $table->text('violation_content');
            $table->string('violation_count')->nullable();
            $table->text('violator_attitude')->nullable();
            $table->text('resolution_direction')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_reports');
    }
};
```

- [ ] **Run migration**

```bash
php artisan migrate
```

- [ ] **Commit**

```bash
git add database/migrations/2026_07_02_000001_create_violation_reports_table.php
git commit -m "feat: create violation_reports table"
```

---

### Task 2: Create ViolationReport model

- [ ] **Create `app/Models/ViolationReport.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'recorded_at' => 'datetime',
        'reporters' => 'array',
        'witnesses' => 'array',
        'violators' => 'array',
    ];
}
```

- [ ] **Commit**

```bash
git add app/Models/ViolationReport.php
git commit -m "feat: add ViolationReport model"
```

---

### Task 3: Create GeminiOcrService

- [ ] **Create `app/Services/GeminiOcrService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiOcrService
{
    public function analyze(string $imageBase64, string $mimeType): ?array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY chưa được cấu hình');
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Api-Revision' => '2026-05-20',
        ])->post('https://generativelanguage.googleapis.com/v1beta/interactions', [
            'model' => 'gemini-3.5-flash',
            'input' => [
                [
                    'type' => 'text',
                    'text' => 'Trích xuất thông tin từ hình ảnh biên bản vi phạm này và trả về JSON với cấu trúc: { "recorded_at": "12:15 13/03/2026", "location": "Tập đoàn ASG NB", "reporters": [{"ho_ten": "Nguyễn Đức Tuấn", "chuc_vu": "BV", "cong_ty": "ALPHA"}], "witnesses": [{"ho_ten": "...", "chuc_vu": "...", "cong_ty": "..."}], "violators": [{"ho_ten": "...", "chuc_vu": "...", "cong_ty": "..."}], "target": "", "violation_content": "nội dung vi phạm", "violation_count": "số lần", "violator_attitude": "thái độ", "resolution_direction": "hướng xử lý" }. Chỉ trả về JSON, không kèm giải thích.',
                ],
                [
                    'type' => 'image',
                    'data' => $imageBase64,
                    'mime_type' => $mimeType,
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API lỗi: ' . $response->body());
        }

        $data = $response->json();

        $text = $data['output'][0]['text'] ?? null;

        if (empty($text)) {
            return null;
        }

        $json = $this->extractJson($text);

        if ($json === null) {
            return null;
        }

        return $json;
    }

    private function extractJson(string $text): ?array
    {
        preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches);

        if (! empty($matches[1])) {
            $text = $matches[1];
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }
}
```

- [ ] **Add gemini config to `config/services.php`**

Open `config/services.php` and append before the last `];`:

```php
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
```

- [ ] **Commit**

```bash
git add app/Services/GeminiOcrService.php config/services.php
git commit -m "feat: add GeminiOcrService"
```

---

### Task 4: Create Filament Resource

- [ ] **Create `app/Filament/Resources/ViolationReportResource.php`**

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolationReportResource\Pages;
use App\Models\ViolationReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ViolationReportResource extends Resource
{
    protected static ?string $model = ViolationReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Biên bản vi phạm';
    protected static ?string $navigationLabel = 'Biên bản vi phạm';
    protected static ?string $navigationGroup = 'Quản lý danh mục';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Thời gian lập')
                            ->required(),
                        Forms\Components\TextInput::make('location')
                            ->label('Địa điểm')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Người liên quan')
                    ->schema([
                        Forms\Components\Repeater::make('reporters')
                            ->label('Người lập biên bản')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người lập biên bản')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),

                        Forms\Components\Repeater::make('witnesses')
                            ->label('Người làm chứng')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người làm chứng')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),

                        Forms\Components\Repeater::make('violators')
                            ->label('Người vi phạm')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người vi phạm')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),
                    ]),

                Forms\Components\Section::make('Nội dung sự việc')
                    ->schema([
                        Forms\Components\TextInput::make('target')
                            ->label('Mục tiêu')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('violation_content')
                            ->label('Nội dung vi phạm')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('violation_count')
                            ->label('Số lần vi phạm')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('violator_attitude')
                            ->label('Thái độ người vi phạm')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Xử lý')
                    ->schema([
                        Forms\Components\Textarea::make('resolution_direction')
                            ->label('Hướng xử lý')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Thời gian lập')
                    ->dateTime('H:i, d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Địa điểm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('violation_content')
                    ->label('Nội dung vi phạm')
                    ->limit(50),
                Tables\Columns\TextColumn::make('violation_count')
                    ->label('Số lần'),
                Tables\Columns\TextColumn::make('resolution_direction')
                    ->label('Hướng xử lý')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViolationReports::route('/'),
            'create' => Pages\CreateViolationReport::route('/create'),
            'edit' => Pages\EditViolationReport::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Create `app/Filament/Resources/ViolationReportResource/Pages/CreateViolationReport.php`**

```php
<?php

namespace App\Filament\Resources\ViolationReportResource\Pages;

use App\Filament\Resources\ViolationReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateViolationReport extends CreateRecord
{
    protected static string $resource = ViolationReportResource::class;
}
```

- [ ] **Create `app/Filament/Resources/ViolationReportResource/Pages/EditViolationReport.php`**

```php
<?php

namespace App\Filament\Resources\ViolationReportResource\Pages;

use App\Filament\Resources\ViolationReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditViolationReport extends EditRecord
{
    protected static string $resource = ViolationReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

- [ ] **Create `app/Filament/Resources/ViolationReportResource/Pages/ListViolationReports.php`**

```php
<?php

namespace App\Filament\Resources\ViolationReportResource\Pages;

use App\Filament\Resources\ViolationReportResource;
use App\Services\GeminiOcrService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListViolationReports extends ListRecords
{
    protected static string $resource = ViolationReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('scanViolationReport')
                ->label('Quét biên bản')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('success')
                ->modalHeading('Tải ảnh biên bản lên')
                ->modalSubmitActionLabel('Xử lý')
                ->form([
                    FileUpload::make('image')
                        ->label('Chọn ảnh biên bản')
                        ->image()
                        ->required()
                        ->maxSize(10240),
                ])
                ->action(function (array $data) {
                    $path = $data['image'] ?? null;

                    if (empty($path)) {
                        Notification::make()
                            ->title('Vui lòng chọn ảnh')
                            ->danger()
                            ->send();

                        return;
                    }

                    $fullPath = Storage::disk('public')->path($path);
                    $mimeType = mime_content_type($fullPath);
                    $base64 = base64_encode(file_get_contents($fullPath));

                    try {
                        $service = app(GeminiOcrService::class);
                        $result = $service->analyze($base64, $mimeType);

                        if ($result === null) {
                            Notification::make()
                                ->title('Không thể đọc được nội dung từ ảnh, vui lòng thử lại')
                                ->danger()
                                ->send();

                            return;
                        }

                        $recordedAt = null;
                        if (! empty($result['recorded_at'])) {
                            $parsed = date_create_from_format('H:i d/m/Y', $result['recorded_at']);
                            if ($parsed) {
                                $recordedAt = $parsed;
                            }
                        }

                        \App\Models\ViolationReport::create([
                            'recorded_at' => $recordedAt ?? now(),
                            'location' => $result['location'] ?? '',
                            'reporters' => $result['reporters'] ?? [],
                            'witnesses' => $result['witnesses'] ?? [],
                            'violators' => $result['violators'] ?? [],
                            'target' => $result['target'] ?? null,
                            'violation_content' => $result['violation_content'] ?? '',
                            'violation_count' => $result['violation_count'] ?? null,
                            'violator_attitude' => $result['violator_attitude'] ?? null,
                            'resolution_direction' => $result['resolution_direction'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Tạo biên bản thành công')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Lỗi: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
```

- [ ] **Add GEMINI_API_KEY to `.env`**

```bash
echo -e "\nGEMINI_API_KEY=your_gemini_api_key_here" >> .env
```

- [ ] **Commit all Filament files**

```bash
git add \
  app/Filament/Resources/ViolationReportResource.php \
  app/Filament/Resources/ViolationReportResource/ \
  .env
git commit -m "feat: add ViolationReport Filament resource with Gemini OCR action"
```
