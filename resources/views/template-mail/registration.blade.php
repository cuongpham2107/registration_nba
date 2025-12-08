<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thông tin đăng ký</title>
    <meta name="favicon" content="{{ asset('images/ASG.png') }}">

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
                <h2 style="font-size: 24px; color: #2d3748; font-weight: 700; margin-bottom: 4px;">Thông tin đăng ký</h2>
                <div style="font-size: 14px; color: #718096;">Vui lòng kiểm tra và xác nhận thông tin bên dưới</div>
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;background:#f7fafc;border-radius:12px;overflow:hidden;">
            <tbody>
                <tr>
                    <th style="width:40%;color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Đơn vị khách</th>
                    <td style="padding:10px 8px;">{{ $name }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Mục đích</th>
                    <td style="padding:10px 8px;">{{ $purpose }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">BKS ô tô</th>
                    <td style="padding:10px 8px;">{{ $bks }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Giờ vào dự kiến</th>
                    <td style="padding:10px 8px;">{{ $start_date }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Giờ ra dự kiến</th>
                    <td style="padding:10px 8px;">{{ $end_date }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Tài sản</th>
                    <td style="padding:10px 8px;">{{ $asset }}</td>
                </tr>
                <tr>
                    <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Ghi chú</th>
                    <td style="padding:10px 8px;">{{ $note }}</td>
                </tr>
            </tbody>
        </table>
        @if ($customers->count() > 0)
            <h3 style="font-size: 18px; color: #2d3748; font-weight: 600; margin-top: 24px; margin-bottom: 12px;">Danh sách nhân viên đăng ký:</h3>
            <table style="width:100%;border-collapse:collapse;background:#f7fafc;border-radius:12px;overflow:hidden;">
                <thead>
                    <tr>
                        <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Tên khách</th>
                        <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Giấy tờ</th>
                        <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Số</th>
                        <th style="color:#667eea;text-align:left;padding:10px 8px;background:#f3f6fd;font-weight:600;">Khu vực</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $staff)
                    <tr>
                        <td style="padding:10px 8px;">{{ $staff['name'] }}</td>
                        <td style="padding:10px 8px;">{{ $staff['type'] }}</td>
                        <td style="padding:10px 8px;">{{ $staff['papers'] }}</td>
                        <td style="padding:10px 8px;">
                            @foreach ($staff['areas'] as $area)
                                <p>{{ \App\Models\Area::where('code',$area)->first()->name}}</p>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div style="margin-top: 32px; text-align:center;">
            <a href="{{route('approve',$id). '?name_manager=' . $name_manager . '&job_title_manager=' . $job_title_manager}}" class="btn" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:600;font-size:16px;padding:12px 32px;border-radius:8px;box-shadow:0 2px 8px rgba(102,126,234,0.12);margin-right:12px;text-decoration:none;display:inline-block;">Duyệt</a>
            <a href="{{route('reject',$id). '?name_manager=' . $name_manager . '&job_title_manager=' . $job_title_manager}}" class="btn btn-danger" style="background:linear-gradient(135deg,#f44336,#ff6b6b);color:#fff;font-weight:600;font-size:16px;padding:12px 32px;border-radius:8px;box-shadow:0 2px 8px rgba(244,67,54,0.12);text-decoration:none;display:inline-block;">Từ chối</a>
        </div>
    </div>
</body>
</html>