<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="favicon" content="{{ asset('images/ASG.png') }}">
    <title>Đăng ký thành công - ASGL</title>
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
            padding: 60px 40px;
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
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .success-message {
            color: #6b7280;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 40px;
            font-weight: 500;
        }

        .registration-details {
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.2);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 40px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #374151;
            font-weight: 600;
            font-size: 14px;
        }

        .detail-value {
            color: #059669;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 140px;
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
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .success-container {
                padding: 40px 20px;
                border-radius: 16px;
            }

            .success-icon {
                width: 100px;
                height: 100px;
                font-size: 50px;
                margin-bottom: 25px;
            }

            h1 {
                font-size: 24px;
                margin-bottom: 15px;
            }

            .success-message {
                font-size: 16px;
                margin-bottom: 30px;
            }

            .registration-details {
                padding: 15px;
                margin-bottom: 30px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
                min-width: 120px;
            }

            .btn-group {
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        
        <h1>Gửi thành công!</h1>
        
        <p class="success-message">
            Đăng ký xe khai thác đã được gửi thành công.<br>
            Vui lòng chờ phê duyệt từ bộ phận có thẩm quyền.
        </p>

        @if(session('registration_data'))
            <div class="registration-details">
                @php $data = session('registration_data'); @endphp
                
                <div class="detail-row">
                    <span class="detail-label">Tên tài xế:</span>
                    <span class="detail-value">{{ $data['driver_name'] ?? 'N/A' }}</span>
                </div>
                
                @if(!empty($data['name']))
                <div class="detail-row">
                    <span class="detail-label">Đơn vị:</span>
                    <span class="detail-value">{{ $data['name'] }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Biển số xe:</span>
                    <span class="detail-value">{{ $data['vehicle_number'] ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Số HAWB:</span>
                    <span class="detail-value">{{ $data['hawb_number'] ?? 'N/A' }}</span>
                </div>
                
                @if(!empty($data['pcs']))
                <div class="detail-row">
                    <span class="detail-label">Số kiện:</span>
                    <span class="detail-value">{{ $data['pcs'] }} kiện</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Thời gian vào dự kiến:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($data['expected_in_at'])->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @endif

        <div class="btn-group">
            <a href="{{ route('registration-vehicle.index-old') }}" class="btn btn-primary">
                Đăng ký mới
            </a>
            <button onclick="window.history.back()" class="btn btn-secondary">
                Quay lại
            </button>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} ASGL - Hệ thống đăng ký xe khai thác</p>
            <p>Mọi thắc mắc vui lòng liên hệ bộ phận hỗ trợ</p>
        </div>
    </div>
</body>

</html>
