<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="favicon" content="{{ asset('images/ASG.png') }}">
    <title>Kết quả phê duyệt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
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
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        .icon.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .icon.danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }
        
        .icon.error {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        h1 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .message {
            font-size: 16px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .info-box {
            background: #f7fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }
        
        .info-value {
            font-weight: 500;
            color: #2d3748;
            font-size: 14px;
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            color: #a0aec0;
            font-size: 13px;
        }
        
        @media (max-width: 480px) {
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
        }
    </style>
</head>
<body>
    <div class="container">
        @if($status === 'Duyệt')
            <div class="icon success">✓</div>
            <h1>Phê duyệt thành công!</h1>
        @elseif($status === 'Từ chối')
            <div class="icon danger">✕</div>
            <h1>Đã từ chối!</h1>
        @else
            <div class="icon error">⚠</div>
            <h1>Thông báo!</h1>
        @endif
        
        <p class="message">{{ $message }}</p>
        
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Người thực hiện:</span>
                <span class="info-value">{{ $name_manager }}</span>
            </div>
            @if($job_title_manager)
            <div class="info-row">
                <span class="info-label">Chức vụ:</span>
                <span class="info-value">{{ $job_title_manager }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Thời gian:</span>
                <span class="info-value">{{ now()->format('H:i - d/m/Y') }}</span>
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} ASGL - Hệ thống đăng ký khách</p>
        </div>
    </div>
</body>
</html>
