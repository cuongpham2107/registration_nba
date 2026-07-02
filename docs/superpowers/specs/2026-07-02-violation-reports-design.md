# Violation Reports — Database Table & Filament Resource

## Summary

Create a new `violation_reports` table to store incident/citation reports (biên bản vi phạm) with JSON person arrays and flattened incident details. Build a Filament resource with a Gemini-powered header action that scans a photo and auto-creates the record.

## Data Structure

### Table: `violation_reports`

| Column | Type | Description | Source |
|--------|------|-------------|--------|
| `id` | bigint, PK | Auto-increment | |
| `recorded_at` | datetime | Thời gian lập biên bản | `thoi_gian_lap` |
| `location` | string | Địa điểm | `dia_diem` |
| `reporters`| json | Người lập biên bản `[{ho_ten, chuc_vu, cong_ty}]` | `nguoi_lap_bien_ban` |
| `witnesses` | json | Người làm chứng `[{ho_ten, chuc_vu, cong_ty}]` | `nguoi_lam_chung` |
| `violators` | json | Người vi phạm `[{ho_ten, chuc_vu, cong_ty}]` | `nguoi_vi_pham` |
| `target` | string, nullable | Mục tiêu | `muc_tieu` |
| `violation_content` | text | Nội dung vi phạm | `noi_dung_vi_pham` |
| `violation_count` | string, nullable | Số lần vi phạm | `so_lan_vi_pham` |
| `violator_attitude` | text, nullable | Thái độ người vi phạm | `thai_do_nguoi_vi_pham` |
| `resolution_direction` | text, nullable | Hướng xử lý | `huong_xu_ly` |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

### Model: `ViolationReport`

```php
class ViolationReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'recorded_at' => 'datetime',
        'reporters' => 'array',
        'witnesses' => 'array',
        'violators' => 'array',
    ];
}
```

### Migration

Conventional Laravel migration with `json()` columns for the three person arrays, `string`/`text` for flattened fields. No foreign keys.

## Filament Resource

### Navigation

- **Group:** Quản lý danh mục
- **Label:** Biên bản vi phạm
- **Icon:** `heroicon-o-document-text`

### Form

| Field | Component | Notes |
|-------|-----------|-------|
| `recorded_at` | DateTimePicker | label: Thời gian lập |
| `location` | TextInput | label: Địa điểm |
| `reporters` | Repeater | 3 columns: ho_ten, chuc_vu, cong_ty, label: Người lập biên bản |
| `witnesses` | Repeater | same schema, label: Người làm chứng |
| `violators` | Repeater | same schema, label: Người vi phạm |
| `target` | TextInput | nullable |
| `violation_content` | Textarea | label: Nội dung vi phạm, columnSpanFull |
| `violation_count` | TextInput | label: Số lần vi phạm |
| `violator_attitude` | Textarea | label: Thái độ người vi phạm |
| `resolution_direction` | Textarea | label: Hướng xử lý |

All Repeaters: `reorderable(false)`, `defaultItems(1)`, `collapsible(false)`.

### Table

| Column | Source | Format |
|--------|--------|--------|
| Thời gian lập | `recorded_at` | `H:i, d/m/Y` |
| Địa điểm | `location` | plain |
| Nội dung vi phạm | `violation_content` | `limit(50)` |
| Số lần | `violation_count` | plain |
| Hướng xử lý | `resolution_direction` | `limit(50)` |

No filters needed initially.

## Gemini OCR Action

### Header Action: "Quét biên bản"

Button on `ListViolationReports` (header, before table).

**Flow:**

1. Click → opens modal with a single `FileUpload` field (image only)
2. User selects an image → clicks "Xử lý"
3. Backend:
   a. Reads the uploaded file, base64-encodes it
   b. Calls Gemini API:
      - URL: `https://generativelanguage.googleapis.com/v1beta/interactions`
      - Header: `x-goog-api-key`, `Content-Type: application/json`, `Api-Revision: 2026-05-20`
      - Body: `{ model: "gemini-3.5-flash", input: [text prompt, { type: "image", data: base64, mime_type }] }`
      - Prompt asks Gemini to return JSON matching `violation_reports` structure
   c. Parses JSON response → `ViolationReport::create()`
4. Notification (success/error) → reload table

**API key** stored in `.env` as `GEMINI_API_KEY`.

### Error handling

- No image selected → validation error
- Gemini call fails → notification with error message
- JSON parse fails → notification "Không thể đọc được nội dung từ ảnh, vui lòng thử lại"
- Success → notification "Tạo biên bản thành công" + reload

## Out of Scope

- Search/filters on table (can be added later)
- Editing Gemini response before saving (can be added later)
- History/versioning
- Export
