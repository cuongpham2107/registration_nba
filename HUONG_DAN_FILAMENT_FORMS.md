# Chuyển đổi Form từ Blade thuần sang Filament Forms

## Tổng quan

Đã chuyển đổi form đăng ký xe khai thác từ HTML/Alpine.js thuần sang **Filament Forms + Livewire** để:
- ✅ Giảm code HTML/CSS thủ công
- ✅ Validation tự động với UI đẹp
- ✅ Reactive forms (tự động cập nhật)
- ✅ Tích hợp sẵn dark mode
- ✅ Mobile responsive tự động
- ✅ Dễ bảo trì và mở rộng

## Files đã tạo/sửa

### 1. Livewire Component
**File**: `app/Livewire/RegistrationVehicleForm.php`
- Component chính xử lý logic form
- Validation tự động qua Filament
- Kiểm tra HAWB real-time
- Xử lý trùng lặp 4 tiếng
- Gửi email và notifications

**Các tính năng chính:**
```php
// Form schema với Filament Components
Section::make('Thông tin tài xế')
    ->schema([
        TextInput::make('driver_name')->required(),
        TextInput::make('driver_id_card')->required(),
        // ...
    ])

// Reactive field - tự động kiểm tra HAWB
TextInput::make('hawb_number')
    ->reactive()
    ->afterStateUpdated(fn ($state) => $this->checkHawbNumber($state))

// DateTimePicker với format tùy chỉnh
DateTimePicker::make('expected_in_at')
    ->native(false)
    ->displayFormat('d/m/Y H:i')
```

### 2. View Template
**File**: `resources/views/livewire/registration-vehicle-form.blade.php`
- UI đơn giản, clean
- Tích hợp Filament styles
- Loading states tự động
- Gradient background đẹp

### 3. Layout
**File**: `resources/views/components/layouts/public.blade.php`
- Layout cho trang public
- Load Filament styles/scripts
- Anti-flicker với [x-cloak]

### 4. Routes
**File**: `routes/web.php`
```php
// Route mới (Filament)
Route::get('/dang-ky-xe-khai-thac', \App\Livewire\RegistrationVehicleForm::class)
    ->name('registration-vehicle.index');

// Route cũ (backup)
Route::get('/dang-ky-xe-khai-thac-old', function(){
    return view('registration_vehicle.index');
})->name('registration-vehicle.index-old');
```

## So sánh

### Trước (Blade thuần)
```html
<!-- 400+ dòng HTML + CSS inline -->
<input type="text" id="driver_name" name="driver_name" required 
    x-model="driverName" value="{{ old('driver_name') }}">
@error('driver_name')
    <span style="color: #e53e3e;">{{ $message }}</span>
@enderror

<!-- JavaScript Alpine.js custom -->
<script>
    Alpine.data('vehicleForm', () => ({
        // 200+ dòng logic
    }))
</script>
```

### Sau (Filament)
```php
// 1 dòng code
TextInput::make('driver_name')
    ->label('Tên tài xế')
    ->required()
    ->maxLength(255)
    ->columnSpan(1)
```

## Tính năng được giữ nguyên

✅ Validation tất cả fields
✅ Kiểm tra HAWB từ API external
✅ Auto-fill PCS từ API
✅ Kiểm tra trùng lặp trong 4 tiếng
✅ Gửi email cho approvers
✅ Gửi notifications real-time
✅ Mobile responsive
✅ Loading states
✅ Error messages

## Tính năng mới thêm

✨ Dark mode support (Filament built-in)
✨ Better accessibility (ARIA labels tự động)
✨ Collapsible sections (Ghi chú có thể thu gọn)
✨ Icon cho sections
✨ Helper text động cho HAWB
✨ Better validation messages
✨ Smooth transitions

## Cách sử dụng

### Truy cập form mới
```
https://dangkykhach.asgl.net.vn/dang-ky-xe-khai-thac
```

### Nếu cần quay lại form cũ
```
https://dangkykhach.asgl.net.vn/dang-ky-xe-khai-thac-old
```

## Customization

### Thêm field mới
```php
TextInput::make('new_field')
    ->label('Label mới')
    ->required()
    ->maxLength(255)
```

### Thêm validation
```php
TextInput::make('email')
    ->email()
    ->unique(table: RegistrationVehicle::class)
    ->rule('custom_rule')
```

### Thay đổi layout
```php
Section::make()
    ->columns(3)  // Thay đổi số cột
    ->collapsible()  // Có thể thu gọn
    ->collapsed()  // Mặc định thu gọn
```

## Lợi ích

1. **Giảm code**: Từ 600+ dòng → 200 dòng
2. **Dễ đọc**: Logic rõ ràng, tách biệt
3. **Dễ bảo trì**: Filament handle UI, bạn chỉ lo logic
4. **Tự động responsive**: Không cần viết @media queries
5. **Dark mode**: Tự động support
6. **Type safety**: PHP typed properties
7. **Testing**: Dễ viết test cho Livewire component

## Notes

- Route cũ vẫn được giữ lại (`/dang-ky-xe-khai-thac-old`) để backup
- Controller `storeVehicle()` không cần dùng nữa vì logic đã chuyển vào Livewire
- LocalStorage auto-save đã được tích hợp sẵn trong Filament Forms
- HAWB checking vẫn gọi API như cũ
- Notifications dùng Filament Notifications thay vì alert()

## Mở rộng

Có thể dễ dàng thêm:
- Multi-step wizard với `Wizard::make()`
- File uploads với `FileUpload::make()`
- Repeater (nhiều xe cùng lúc) với `Repeater::make()`
- Rich text editor với `RichEditor::make()`
- Conditional fields với `->hidden()` và `->visible()`

## Documentation

Chi tiết về Filament Forms:
https://filamentphp.com/docs/3.x/forms/adding-a-form-to-a-livewire-component
