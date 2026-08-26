<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="favicon" content="{{ asset('images/favicon.ico') }}">
    <title>Đăng ký khách thành công - ASG</title>
    <style>
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

        .success-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            max-width: 500px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 25px 50px rgba(16, 185, 129, 0.6);
            }
        }

        h1 {
            color: #1f2937;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .success-message {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .registration-details {
            background: rgba(16, 185, 129, 0.08);
            border: 1.5px solid rgba(16, 185, 129, 0.25);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid rgba(16, 185, 129, 0.12);
            gap: 12px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #374151;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .detail-value {
            color: #059669;
            font-weight: 700;
            font-size: 13px;
            text-align: right;
            word-break: break-word;
        }

        .customer-list {
            margin-top: 6px;
            padding-left: 12px;
            font-size: 12px;
            color: #047857;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 130px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .success-container {
                padding: 30px 16px;
                border-radius: 16px;
            }

            .success-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
                margin-bottom: 16px;
            }

            h1 {
                font-size: 20px;
            }

            .btn {
                padding: 10px 18px;
                font-size: 13px;
                min-width: 110px;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        
        <h1>Gửi đăng ký thành công, vui lòng chờ phê duyệt!</h1>
        
        <p class="success-message">
            Thông tin đăng ký khách đã được gửi thành công.<br>
            Vui lòng chờ phê duyệt từ bộ phận có thẩm quyền.
        </p>

        @if(session('registration_guest_data'))
            <div class="registration-details">
                @php $data = session('registration_guest_data'); @endphp
                
                <div class="detail-row">
                    <span class="detail-label">Đơn vị khách:</span>
                    <span class="detail-value">{{ $data['name'] ?? 'N/A' }}</span>
                </div>

                @if(!empty($data['bks']))
                <div class="detail-row">
                    <span class="detail-label">BKS ô tô:</span>
                    <span class="detail-value">{{ $data['bks'] }}</span>
                </div>
                @endif
                
                @if(!empty($data['purpose']))
                <div class="detail-row">
                    <span class="detail-label">Mục đích:</span>
                    <span class="detail-value">{{ $data['purpose'] }}</span>
                </div>
                @endif

                @if(!empty($data['start_date']))
                <div class="detail-row">
                    <span class="detail-label">Giờ vào dự kiến:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y H:i') }}</span>
                </div>
                @endif

                @if(!empty($data['end_date']))
                <div class="detail-row">
                    <span class="detail-label">Giờ ra dự kiến:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y H:i') }}</span>
                </div>
                @endif

                @if(!empty($data['customers']) && is_array($data['customers']))
                <div class="detail-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="detail-label">Danh sách khách ({{ count($data['customers']) }} người):</span>
                    <div class="customer-list" style="width: 100%;">
                        @foreach($data['customers'] as $c)
                            <div style="padding: 2px 0;">
                                • <strong>{{ $c['name'] ?? '' }}</strong> - {{ $c['papers'] ?? '' }}
                                @if(!empty($c['license_plate'])) (BKS: {{ $c['license_plate'] }}) @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        @endif

        <div class="btn-group">
            <a href="{{ route('registration-guest.index') }}" class="btn btn-primary">
                Đăng ký mới
            </a>
            <button onclick="window.history.back()" class="btn btn-secondary">
                Quay lại
            </button>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} ASG - Hệ thống đăng ký khách</p>
            <p>Mọi thắc mắc vui lòng liên hệ bộ phận lễ tân / an ninh</p>
        </div>
    </div>
</body>

</html>
