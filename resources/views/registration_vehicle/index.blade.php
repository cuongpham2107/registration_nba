<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="favicon" content="{{ asset('images/ASG.png') }}">
    <title>Đăng ký xe khai thác</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica                    const oldPcs = '{{ old('pcs') }}';

                    // Load from localStorage (không lưu hawb_number, expected_in_at)
                          } finally {
                        this.hawbLoading = false;
                    }
                },

                showSuccessPopupWithCountdown() {
                    this.showSuccessPopup = true;
                    this.countdown = 5;
                    this.closingPopup = false;
                    
                    // Start countdown
                    this.countdownInterval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.closeSuccessPopup();
                        }
                    }, 1000);
                },

                closeSuccessPopup() {
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                    
                    this.closingPopup = true;
                    
                    // Wait for animation to complete before hiding
                    setTimeout(() => {
                        this.showSuccessPopup = false;
                        this.closingPopup = false;
                        this.countdown = 5;
                    }, 500);
                }
            })
        })        const saved = localStorage.getItem('vehicleForm');

                    if (saved && !oldDriverName) {
                        const data = JSON.parse(saved);
                        this.driverName = data.driverName || '';
                        this.driverPhone = data.driverPhone || '';
                        this.driverIdCard = data.driverIdCard || '';
                        this.vehicleNumber = data.vehicleNumber || '';
                        this.unitName = data.unitName || '';
                        this.pcs = data.pcs ? parseInt(data.pcs) : null;
                        this.notes = data.notes || '';

                        // Show notification if there's saved data            } finally {
                        this.hawbLoading = false;
                    }
                },

                showSuccessPopupWithCountdown() {
                    this.showSuccessPopup = true;
                    this.countdown = 5;
                    this.closingPopup = false;
                    
                    
                    // Start countdown
                    this.countdownInterval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.closeSuccessPopup();
                        }
                    }, 1000);
                },

                closeSuccessPopup() {
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                    
                    this.closingPopup = true;
                    
                    // Wait for animation to complete before hiding
                    setTimeout(() => {
                        this.showSuccessPopup = false;
                        this.closingPopup = false;
                        this.countdown = 5;
                    }, 500);
                }
            })
        }), sans-serif;
            background: rgba(82, 135, 173, 1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 20px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: white;
        }

        h1 {
            font-size: 22px;
            color: #2d3748;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 13px;
            color: #718096;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            /* gap: 12px; */
        }

        label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
            margin-bottom: 6px;
        }

        label span.required {
            color: #e53e3e;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="datetime-local"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        .btn-group {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn-disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            filter: grayscale(10%);
        }

        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            color: #a0aec0;
            font-size: 11px;
            text-align: center;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #93c5fd;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info .icon {
            width: auto;
            height: auto;
            margin: 0;
            font-size: 18px;
            background: none;
        }

        /* Success Dialog Styles */
        .success-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            padding: 50px;
            border-radius: 24px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4), 
                        0 0 0 1px rgba(255, 255, 255, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.9);
            z-index: 9999;
            text-align: center;
            width: 400px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            animation: dialogIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            /* Glass morphism effect */
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.98) 0%,
                rgba(255, 255, 255, 0.95) 100%);
        }

        .success-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 9998;
            animation: overlayIn 0.5s ease-out;
        }

        @keyframes dialogIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.7);
                filter: blur(5px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
                filter: blur(0px);
            }
        }

        @keyframes overlayIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
                -webkit-backdrop-filter: blur(0px);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
            }
        }

        @keyframes dialogOut {
            from {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
                filter: blur(0px);
            }
            to {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.7);
                filter: blur(5px);
            }
        }

        @keyframes overlayOut {
            from {
                opacity: 1;
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
            }
            to {
                opacity: 0;
                backdrop-filter: blur(0px);
                -webkit-backdrop-filter: blur(0px);
            }
        }

        .success-popup.closing {
            animation: dialogOut 0.5s cubic-bezier(0.55, 0.085, 0.68, 0.53);
        }

        .success-popup-overlay.closing {
            animation: overlayOut 0.5s ease-in;
        }

        .success-popup .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4),
                        0 0 0 4px rgba(16, 185, 129, 0.1);
            animation: successPulse 2s infinite;
        }

        @keyframes successPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4),
                           0 0 0 4px rgba(16, 185, 129, 0.1);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 25px 50px rgba(16, 185, 129, 0.6),
                           0 0 0 8px rgba(16, 185, 129, 0.2);
            }
        }

        .success-popup h3 {
            color: #1f2937;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: -0.5px;
        }

        .success-popup p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            font-weight: 500;
            max-width: 280px;
        }

        .success-popup .countdown {
            color: #059669;
            font-weight: 700;
            font-size: 15px;
            background: rgba(16, 185, 129, 0.1);
            padding: 12px 20px;
            border-radius: 25px;
            border: 2px solid rgba(16, 185, 129, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .success-popup .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 35px;
            height: 35px;
            background: rgba(107, 114, 128, 0.1);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            color: #6b7280;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .success-popup .close-btn:hover {
            background: rgba(107, 114, 128, 0.2);
            color: #374151;
            transform: scale(1.1);
        }

        @media (max-width: 640px) {
            body {
                padding: 5px;
            }

            .container {
                padding: 15px;
                border-radius: 12px;
            }

            .success-popup {
                width: 320px;
                height: 320px;
                padding: 40px;
            }

            .success-popup .success-icon {
                width: 90px;
                height: 90px;
                font-size: 45px;
                margin-bottom: 25px;
            }

            .success-popup h3 {
                font-size: 22px;
                margin-bottom: 15px;
            }

            .success-popup p {
                font-size: 14px;
                margin-bottom: 25px;
            }

            .success-popup .countdown {
                font-size: 13px;
                padding: 10px 16px;
            }

            .success-popup .close-btn {
                top: 15px;
                right: 15px;
                width: 30px;
                height: 30px;
                font-size: 16px;
            }

            h1 {
                font-size: 20px;
            }

            .icon {
                width: 50px;
                height: 50px;
                font-size: 25px;
            }

            .subtitle {
                font-size: 12px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            input[type="text"],
            input[type="number"],
            input[type="datetime-local"],
            textarea {
                padding: 8px 12px;
                font-size: 14px;
            }

            textarea {
                min-height: 50px;
            }

            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }

            .btn-group {
                gap: 6px;
                margin-top: 15px;
            }

            .alert {
                padding: 8px 10px;
                font-size: 12px;
            }

            .footer {
                font-size: 10px;
                margin-top: 15px;
                padding-top: 12px;
            }
        }

        @media (min-width: 641px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 15px;
            }
        }

        /* Luôn hiển thị 2 cột cho row này, kể cả trên mobile */
        .form-row-always-2 {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
        }

        @media (min-width: 641px) {
            .form-row-always-2 {
                gap: 15px !important;
            }
        }
    </style>
</head>

<body x-data="vehicleForm()">
    <div class="container">
        <div class="header">
            <div class="icon">
                <img style="width: 70px;" src="{{ asset('images/ASG.png') }}" alt="">
            </div>
            <h1>Đăng ký xe khai thác</h1>
            <p class="subtitle">Điền thông tin bên dưới</p>
        </div>

        @if(session('success') && !str_contains(session('success'), 'gửi email thành công'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Success Popup -->
        <div x-show="showSuccessPopup" x-cloak>
            <div class="success-popup-overlay" 
                 :class="{ 'closing': closingPopup }"
                 @click="closeSuccessPopup"></div>
            <div class="success-popup" 
                 :class="{ 'closing': closingPopup }">
                <button class="close-btn" @click="closeSuccessPopup" type="button">
                    ×
                </button>
                <h3>Gửi thành công!</h3>
                <p>Đăng ký xe khai thác đã được gửi thành công.<br>Vui lòng chờ phê duyệt.</p>
                <div class="countdown" x-text="'Tự động đóng sau ' + countdown + ' giây'"></div>
            </div>
        </div>

        <!-- Thông báo dữ liệu đã lưu -->
        <div x-show="hasStoredData" x-cloak class="alert alert-info">
            <span class="icon">ℹ️</span>
            <div>
                <span x-text="'Lần gửi thành công: ' + lastSavedTime"></span>
            </div>
        </div>

        <form action="{{ route('registration-vehicle.store') }}" method="POST" id="vehicleForm">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="driver_name">Tên tài xế <span class="required">*</span></label>
                    <input type="text" id="driver_name" name="driver_name" required x-model="driverName"
                        value="{{ old('driver_name') }}">
                    @error('driver_name')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Tên đơn vị</label>
                    <input type="text" id="name" name="name" x-model="unitName" value="{{ old('name') }}">
                    @error('name')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row form-row-always-2">
                <div class="form-group">
                    <label for="driver_id_card">Số CCCD/CMND<span class="required">*</span></label>
                    <input type="text" id="driver_id_card" name="driver_id_card" required x-model="driverIdCard"
                        value="{{ old('driver_id_card') }}">
                    @error('driver_id_card')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="driver_phone">Số điện thoại</label>
                    <input type="text" id="driver_phone" name="driver_phone" x-model="driverPhone"
                        value="{{ old('driver_phone') }}">
                    @error('driver_phone')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_number">Biển số xe <span class="required">*</span></label>
                    <input type="text" id="vehicle_number" name="vehicle_number" required x-model="vehicleNumber"
                        value="{{ old('vehicle_number') }}">
                    @error('vehicle_number')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="hawb_number">Số Hawb <span class="required">*</span></label>
                    <input type="text" id="hawb_number" name="hawb_number" required x-model="hawbNumber"
                        value="{{ old('hawb_number') }}"
                        @input.debounce.2000ms="checkHawbNumber()"
                        @blur="checkHawbNumber()">
                    <div x-show="hawbLoading" x-cloak style="color: #667eea; font-size: 12px; margin-top: 4px;">
                        🔄 Đang kiểm tra số HAWB...
                    </div>
                    <div x-show="hawbError" x-cloak style="color: #e53e3e; font-size: 12px; margin-top: 4px;" x-text="hawbError"></div>
                    <div x-show="hawbSuccess" x-cloak style="color: #38a169; font-size: 12px; margin-top: 4px;" x-text="hawbSuccess"></div>
                    @error('hawb_number')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pcs">PCS</label>
                    <input type="number" id="pcs" name="pcs" x-model="pcs"
                        value="{{ old('pcs') }}">
                    @error('pcs')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="expected_in_at">Thời gian vào dự kiến <span class="required">*</span></label>
                    <input type="datetime-local" id="expected_in_at" name="expected_in_at" required
                        x-model="expectedInAt" value="{{ old('expected_in_at') }}">
                    @error('expected_in_at')
                        <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea id="notes" name="notes" rows="2" x-model="notes">{{ old('notes') }}</textarea>
                @error('notes')
                    <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="btn-group">
                <!-- <button type="submit" class="btn btn-primary" name="action" value="save">
                    Tạo
                </button> -->
                <!-- Submit button is disabled and visually dimmed until required fields are filled -->
                <button
                    type="submit"
                    class="btn btn-success"
                    name="action"
                    value="save_and_send"
                    :disabled="!isFormComplete()"
                    :class="{ 'btn-disabled': !isFormComplete() }"
                >
                    Tạo và gửi
                </button>
                <button type="button" class="btn btn-secondary" @click="window.history.back()">
                    Hủy bỏ
                </button>
            </div>
        </form>

        <div class="footer">
            <p>© {{ date('Y') }} ASGL - Hệ thống đăng ký xe khai thác</p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vehicleForm', () => ({
                driverName: '',
                driverPhone: '',
                driverIdCard: '',
                vehicleNumber: '',
                hawbNumber: '',
                pcs: null,
                unitName: '',
                expectedInAt: '',
                notes: '',
                hasStoredData: false,
                lastSavedTime: '',
                hawbLoading: false,
                hawbError: '',
                hawbSuccess: '',
                lastCheckedHawb: '',
                showSuccessPopup: false,
                closingPopup: false,
                countdown: 5,
                countdownInterval: null,

                // Return true when required fields are filled and valid
                isFormComplete() {
                    // required: driverName, driverIdCard, vehicleNumber, hawbNumber, expectedInAt
                    const requiredFilled = this.driverName && this.driverIdCard && this.vehicleNumber && this.hawbNumber && this.expectedInAt;
                    // hawbError should be empty AND hawbSuccess must exist (API check passed)
                    const hawbValid = this.hawbError === '' && this.hawbSuccess !== '';
                    return Boolean(requiredFilled) && hawbValid;
                },

                init() {
                    // Load data from localStorage
                    this.loadFromStorage();

                    // Check for success session and show popup
                    const successMessage = @json(session('success'));
                    
                    if (successMessage && successMessage.includes('gửi email thành công')) {
                        this.showSuccessPopupWithCountdown();
                    }

                    // Set default datetime values if not loaded from storage or old input
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    const defaultValue = now.toISOString().slice(0, 16);

                    if (!this.expectedInAt) {
                        this.expectedInAt = defaultValue;
                    }

                    // Watch for changes and save to localStorage
                    // Không lưu hawb_number, expected_in_at để người dùng tự nhập
                    this.$watch('driverName', value => this.saveToStorage());
                    this.$watch('driverPhone', value => this.saveToStorage());
                    this.$watch('driverIdCard', value => this.saveToStorage());
                    this.$watch('vehicleNumber', value => this.saveToStorage());
                    this.$watch('unitName', value => this.saveToStorage());
                    this.$watch('pcs', value => this.saveToStorage());
                    this.$watch('notes', value => this.saveToStorage());

                    // Watch hawbNumber để reset error khi người dùng thay đổi
                    this.$watch('hawbNumber', value => {
                        if (value !== this.lastCheckedHawb) {
                            this.hawbError = '';
                            this.hawbSuccess = '';
                        }
                    });
                },

                loadFromStorage() {
                    // Check if there's old Laravel input first (validation errors)
                    const oldDriverName = '{{ old('driver_name') }}';
                    const oldDriverPhone = '{{ old('driver_phone') }}';
                    const oldDriverIdCard = '{{ old('driver_id_card') }}';
                    const oldVehicleNumber = '{{ old('vehicle_number') }}';
                    const oldUnitName = '{{ old('name') }}';
                    const oldPcs = '{{ old('pcs') }}';
                    const oldNotes = '{{ old('notes') }}';

                    // Load from localStorage (không lưu hawb_number, expected_in_at)
                    const saved = localStorage.getItem('vehicleForm');

                    if (saved && !oldDriverName) {
                        const data = JSON.parse(saved);
                        this.driverName = data.driverName || '';
                        this.driverPhone = data.driverPhone || '';
                        this.driverIdCard = data.driverIdCard || '';
                        this.vehicleNumber = data.vehicleNumber || '';
                        this.unitName = data.unitName || '';
                        this.notes = data.notes || '';

                        // Show notification if there's saved data
                        if (data.savedAt) {
                            this.hasStoredData = true;
                            this.lastSavedTime = this.formatDateTime(data.savedAt);
                        }
                    }

                    // Nếu có đầu vào cũ từ validation errors, hãy sử dụng nó để ghi đè
                    if (oldDriverName) this.driverName = oldDriverName;
                    if (oldDriverPhone) this.driverPhone = oldDriverPhone;
                    if (oldDriverIdCard) this.driverIdCard = oldDriverIdCard;
                    if (oldVehicleNumber) this.vehicleNumber = oldVehicleNumber;
                    if (oldUnitName) this.unitName = oldUnitName;
                    if (oldPcs) this.pcs = parseInt(oldPcs) || null;
                    if (oldNotes) this.notes = oldNotes;
                },

                saveToStorage() {
                    // Không lưu hawb_number, expected_in_at
                    const data = {
                        driverName: this.driverName,
                        driverPhone: this.driverPhone,
                        driverIdCard: this.driverIdCard,
                        vehicleNumber: this.vehicleNumber,
                        unitName: this.unitName,
                        pcs: this.pcs,
                        notes: this.notes,
                        savedAt: new Date().toISOString()
                    };
                    localStorage.setItem('vehicleForm', JSON.stringify(data));
                },

                clearStorage() {
                    localStorage.removeItem('vehicleForm');
                    this.hasStoredData = false;
                },

                formatDateTime(isoString) {
                    const date = new Date(isoString);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');

                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                },

                async checkHawbNumber() {
                    // Reset messages chỉ khi người dùng thay đổi số HAWB
                    if (this.hawbNumber !== this.lastCheckedHawb) {
                        this.hawbError = '';
                        this.hawbSuccess = '';
                    }

                    // Nếu không có hawb number hoặc giống lần kiểm tra trước thì bỏ qua
                    if (!this.hawbNumber || this.hawbNumber === this.lastCheckedHawb) {
                        return;
                    }

                    this.hawbLoading = true;
                    this.lastCheckedHawb = this.hawbNumber;

                    try {
                        const response = await fetch(`https://wh-nba.asgl.net.vn/api/hawb-info/${this.hawbNumber}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (data.success && data.data && data.data.plan) {
                            // HAWB tồn tại và có thông tin
                            const plan = data.data.plan;
                            this.hawbSuccess = `✓ Số HAWB hợp lệ - Dest: ${plan.Dest || 'N/A'}, PCS: ${plan.Pcs || 'N/A'}, Agent: ${plan.Agent || 'N/A'}`;
                            
                            // Tự động điền PCS nếu có
                            if (plan.Pcs && !this.pcs) {
                                this.pcs = parseInt(plan.Pcs) || plan.Pcs;
                            }
                        } else {
                            // HAWB không tồn tại
                            this.hawbError = '⚠️ Số HAWB không tồn tại trong hệ thống';
                        }
                    } catch (error) {
                        console.error('Error checking HAWB:', error);
                        this.hawbError = '❌ Lỗi kết nối đến server. Vui lòng thử lại.';
                    } finally {
                        this.hawbLoading = false;
                    }
                },

                showSuccessPopupWithCountdown() {
                    this.showSuccessPopup = true;
                    this.countdown = 5;
                    this.closingPopup = false;
                    
                    // Start countdown
                    this.countdownInterval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.closeSuccessPopup();
                        }
                    }, 1000);
                },

                closeSuccessPopup() {
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                    
                    this.closingPopup = true;
                    
                    // Wait for animation to complete before hiding
                    setTimeout(() => {
                        this.showSuccessPopup = false;
                        this.closingPopup = false;
                        this.countdown = 5;
                    }, 500);
                }
            }))
        })

        // Vô hiệu hóa việc nhấn Enter để submit form trên điện thoại
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('vehicleForm');
            const inputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="datetime-local"], textarea');
            
            // Ngăn chặn Enter key submit form cho tất cả input
            inputs.forEach(function(input) {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        
                        // Chuyển focus đến input tiếp theo thay vì submit
                        const currentIndex = Array.from(inputs).indexOf(input);
                        const nextInput = inputs[currentIndex + 1];
                        
                        if (nextInput) {
                            nextInput.focus();
                        } else {
                            // Nếu đã ở input cuối cùng, blur input hiện tại
                            input.blur();
                        }
                        
                        return false;
                    }
                });
            });
            
            // Ngăn chặn submit form khi nhấn Enter trên form
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    // Chỉ cho phép submit nếu đang focus vào button submit
                    const activeElement = document.activeElement;
                    if (activeElement && activeElement.type === 'submit') {
                        return true;
                    }
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>

</html>