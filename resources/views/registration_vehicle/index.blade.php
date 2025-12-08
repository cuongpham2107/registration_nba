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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: rgba(82, 135, 173, 1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
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
            margin-bottom: 30px;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: white;
        }

        h1 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            color: #718096;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            margin-bottom: 8px;
        }

        label span.required {
            color: #e53e3e;
        }

        input[type="text"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        input[type="text"]:focus,
        input[type="datetime-local"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
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

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            color: #a0aec0;
            font-size: 13px;
            text-align: center;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
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

        @media (max-width: 640px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body x-data="vehicleForm()">
    <div class="container">
        <div class="header">
            <div class="icon">
                <img style="width: 100px;" src="{{ asset('images/ASG.png') }}" alt="">
            </div>
            <h1>Đăng ký xe khai thác</h1>
            <p class="subtitle">Vui lòng điền đầy đủ thông tin bên dưới</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

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
                    <label for="driver_id_card">Số CMND/CCCD <span class="required">*</span></label>
                    <input type="text" id="driver_id_card" name="driver_id_card" required x-model="driverIdCard"
                        value="{{ old('driver_id_card') }}">
                    @error('driver_id_card')
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
                    <input type="text" id="pcs" name="pcs" x-model="pcs"
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
                <textarea id="notes" name="notes" rows="3" x-model="notes">{{ old('notes') }}</textarea>
                @error('notes')
                    <span style="color: #e53e3e; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="btn-group">
                <!-- <button type="submit" class="btn btn-primary" name="action" value="save">
                    Tạo
                </button> -->
                <button type="submit" class="btn btn-success" name="action" value="save_and_send" :disabled="hawbError !== ''">
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
                pcs: '',
                unitName: '',
                expectedInAt: '',
                notes: '',
                hasStoredData: false,
                lastSavedTime: '',
                hawbLoading: false,
                hawbError: '',
                hawbSuccess: '',
                lastCheckedHawb: '',

                init() {
                    // Load data from localStorage
                    this.loadFromStorage();

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
                    if (oldPcs) this.pcs = oldPcs;
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
                                this.pcs = plan.Pcs.toString();
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
                }
            }))
        })
    </script>
</body>

</html>