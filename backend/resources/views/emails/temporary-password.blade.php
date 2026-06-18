<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mật khẩu tạm thời</title>
</head>
<body style="margin:0;background:#f4f7f5;color:#16201d;font-family:Arial,'Helvetica Neue',sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7f5;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #dce5df;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px;background:linear-gradient(135deg,#087761,#12a982);color:#ffffff;">
                            <div style="font-size:14px;font-weight:700;opacity:.88;">Xem Chỉ Tay</div>
                            <h1 style="margin:8px 0 0;font-size:30px;line-height:1.12;letter-spacing:0;">Mật khẩu tạm thời của bạn</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px;color:#62716b;line-height:1.6;">Chào {{ $user->name }},</p>
                            <p style="margin:0 0 18px;color:#62716b;line-height:1.6;">Bạn vừa yêu cầu khôi phục mật khẩu. Hãy dùng mật khẩu tạm thời bên dưới để đăng nhập, sau đó đổi sang mật khẩu mới trong ứng dụng.</p>
                            <div style="margin:20px 0;padding:18px;border-radius:8px;background:#eef7f3;border:1px solid #cfe7dd;text-align:center;">
                                <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#087761;font-weight:800;">Mật khẩu tạm thời</div>
                                <div style="font-size:28px;line-height:1.3;font-weight:900;letter-spacing:1px;color:#16201d;margin-top:6px;">{{ $temporaryPassword }}</div>
                                <div style="margin-top:10px;color:#9a5968;font-weight:800;">Có hiệu lực trong 1 phút kể từ khi email được gửi.</div>
                            </div>
                            <a href="{{ $changePasswordUrl }}" style="display:block;text-align:center;background:#087761;color:#ffffff;text-decoration:none;border-radius:8px;padding:15px 18px;font-weight:900;">Mở màn hình đổi mật khẩu</a>
                            <p style="margin:18px 0 0;color:#62716b;line-height:1.6;">Nếu bạn chưa cài ứng dụng, hãy tải app tại đây rồi đăng nhập bằng mật khẩu tạm thời.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:14px;">
                                <tr>
                                    <td width="50%" style="padding-right:6px;">
                                        <a href="{{ $appStoreUrl }}" style="display:block;text-align:center;border:1px solid #dce5df;color:#16201d;text-decoration:none;border-radius:8px;padding:13px 10px;font-weight:900;background:#ffffff;">
                                            <span style="font-size:18px;vertical-align:-2px;">&#63743;</span>
                                            App Store
                                        </a>
                                    </td>
                                    <td width="50%" style="padding-left:6px;">
                                        <a href="{{ $googlePlayUrl }}" style="display:block;text-align:center;border:1px solid #dce5df;color:#16201d;text-decoration:none;border-radius:8px;padding:13px 10px;font-weight:900;background:#ffffff;">
                                            <span style="font-size:17px;vertical-align:-2px;">&#9654;</span>
                                            Google Play
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:18px 0 0;color:#9a5968;line-height:1.6;font-size:13px;">Nếu bạn không yêu cầu thao tác này, hãy đăng nhập và đổi mật khẩu ngay hoặc liên hệ hỗ trợ.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#f8fbf9;color:#62716b;font-size:12px;line-height:1.5;">
                            Email này được gửi tự động. Vì lý do an toàn, không chia sẻ mật khẩu tạm thời cho bất kỳ ai.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
