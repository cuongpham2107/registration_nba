<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="favicon" content="{{ asset('images/ASG.png') }}">

    <title>Thông tin đăng ký xe khai thác</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50 !important;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 8px;
            
        }
        .btn-danger {
            background-color: #f44336 !important;
        }
        a {
            text-decoration: none;
            color: white !important;
        }
    </style>
</head>
<body style="background: #f6f8fa; font-family: 'Segoe UI', Arial, sans-serif;padding:20px">
    <div style="max-width: 480px; margin: 40px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(102,126,234,0.12); padding: 32px 24px;">
        <div style="text-align:center; margin-bottom: 24px;">
            <div style="margin-top:12px;">
                <h2 style="font-size: 24px; color: #2d3748; font-weight: 700; margin-bottom: 4px;">Thông tin đăng ký xe khai thác</h2>
                <div style="font-size: 14px; color: #718096;">Vui lòng kiểm tra và xác nhận thông tin bên dưới</div>
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;background:#f7fafc;border-radius:12px;overflow:hidden;">
            <tbody>
                <tr>
                    <th style="width:40%;color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Tên đơn vị</th>
                    <td style="padding:10px 8px;">{{ $registration->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Tên tài xế</th>
                    <td style="padding:10px 8px;">{{ $registration->driver_name }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Số điện thoại</th>
                    <td style="padding:10px 8px;">{{ $registration->driver_phone }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Số CMND/CCCD</th>
                    <td style="padding:10px 8px;">{{ $registration->driver_id_card }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Biển số xe</th>
                    <td style="padding:10px 8px;">{{ $registration->vehicle_number }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Số Hawb</th>
                    <td style="padding:10px 8px;">{{ $registration->hawb_number }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Thời gian vào dự kiến</th>
                    <td style="padding:10px 8px;">{{ \Carbon\Carbon::parse($registration->expected_in_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Thời gian ra dự kiến</th>
                    <td style="padding:10px 8px;">{{ \Carbon\Carbon::parse($registration->expected_out_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Ghi chú</th>
                    <td style="padding:10px 8px;">{{ $registration->notes ?? 'Không có' }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Ngày tạo</th>
                    <td style="padding:10px 8px;">{{ \Carbon\Carbon::parse($registration->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top: 32px; text-align:center;">
            @php
                $baseUrl = route('filament.admin.resources.registration-vehicles.index');
                $filterUrl = $baseUrl . '?tableFilters[vehicle_filter][search]='. $registration->hawb_number.'&tableFilters[vehicle_filter][start_date]='. \Carbon\Carbon::parse($registration->expected_in_at)->format('Y-m-d H:i:s');
            @endphp
            <a href="{{ $filterUrl }}" class="btn" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:600;font-size:16px;padding:12px 32px;border-radius:8px;box-shadow:0 2px 8px rgba(102,126,234,0.12);margin-right:12px;text-decoration:none;display:inline-block;">Duyệt</a>
        </div>
    </div>
</body>
</html>