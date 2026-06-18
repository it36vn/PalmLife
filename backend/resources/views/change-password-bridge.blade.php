<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi mật khẩu | Xem Chỉ Tay</title>
    <style>
        :root { color-scheme: light; --ink:#16201d; --muted:#62716b; --brand:#087761; --line:#dce5df; --bg:#f4f7f5; }
        * { box-sizing: border-box; }
        body {
            margin:0; min-height:100vh; display:grid; place-items:center; padding:18px;
            font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            color:var(--ink); background:linear-gradient(180deg,#fbfcfb,var(--bg));
        }
        main { width:min(100%,520px); background:#fff; border:1px solid var(--line); border-radius:8px; padding:24px; box-shadow:0 22px 55px rgba(20,41,35,.10); }
        h1 { margin:0 0 10px; font-size:32px; letter-spacing:0; }
        p { margin:0 0 18px; color:var(--muted); line-height:1.6; }
        a { display:flex; align-items:center; justify-content:center; min-height:48px; border-radius:8px; padding:12px 14px; text-decoration:none; font-weight:900; }
        .primary { background:var(--brand); color:#fff; }
        .secondary { color:var(--brand); border:1px solid var(--line); margin-top:10px; }
        .stores { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:14px; }
        @media (max-width:520px) { .stores { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<main>
    <h1>Đổi mật khẩu</h1>
    <p>Ứng dụng sẽ được mở nếu đã cài đặt. Nếu chưa, hãy tải Xem Chỉ Tay từ cửa hàng phù hợp rồi đăng nhập bằng mật khẩu tạm thời trong email.</p>
    <a class="primary" href="{{ $deepLink }}">Mở ứng dụng</a>
    <div class="stores">
        <a class="secondary" href="{{ $appStoreUrl }}">App Store</a>
        <a class="secondary" href="{{ $googlePlayUrl }}">Google Play</a>
    </div>
    <a class="secondary" href="/">Về trang giới thiệu</a>
</main>
<script>
    setTimeout(function () {
        window.location.href = @json($deepLink);
    }, 300);
</script>
</body>
</html>
