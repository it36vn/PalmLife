<!doctype html>
<html lang="{{ $adminLocale ?? 'vi' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tr['login_title'] }} | {{ $tr['app_name'] }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #16201d;
            --muted: #62716b;
            --line: #dce5df;
            --brand: #087761;
            --brand-2: #12a982;
            --paper: #f4f7f5;
            --danger: #aa314b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 18px;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 15% 0%, rgba(18, 169, 130, .17), transparent 30rem),
                linear-gradient(180deg, #fbfcfb, var(--paper));
        }
        .panel {
            width: min(100%, 430px);
            background: rgba(255,255,255,.92);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 22px 55px rgba(20, 41, 35, .10);
            padding: 24px;
        }
        .mark {
            width: 48px; height: 48px; border-radius: 8px;
            display: grid; place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: #fff; font-weight: 900; margin-bottom: 16px;
        }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .language-pill { position:relative; display:inline-flex; align-items:center; min-height:38px; border:1px solid var(--line); border-radius:999px; background:#fff; color:var(--muted); font-weight:800; }
        .language-pill:before { content:"🌐"; padding-left:11px; font-size:14px; }
        .language-pill select { appearance:none; border:0; background:transparent; color:var(--muted); font:inherit; font-weight:800; padding:8px 16px 8px 8px; outline:0; cursor:pointer; }
        /* .language-pill:after { content:"⌄"; position:absolute; right:11px; pointer-events:none; color:var(--brand); font-weight:900; } */
        h1 { margin: 0 0 8px; font-size: 30px; letter-spacing: 0; }
        p { margin: 0 0 18px; color: var(--muted); line-height: 1.55; }
        label { display: grid; gap: 7px; color: var(--muted); font-size: 13px; font-weight: 800; }
        input {
            width: 100%;
            min-height: 48px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 11px 12px;
            font: inherit;
        }
        button {
            width: 100%;
            min-height: 48px;
            margin-top: 14px;
            border: 0;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            font: inherit;
            font-weight: 900;
            cursor: pointer;
        }
        .error {
            margin-bottom: 12px;
            padding: 11px 12px;
            border-radius: 8px;
            background: #fff3f5;
            color: var(--danger);
            border: 1px solid #f1c0ca;
            font-weight: 750;
        }
        a { color: var(--brand); text-decoration: none; font-weight: 800; display: inline-block; margin-top: 14px; }
    </style>
</head>
<body>
    <main class="panel">
        <div class="topbar">
            <img class="mark" src="/icon.png" alt="Xem Chi Tay" width="32" height="32">
            <span class="language-pill">
                <select aria-label="{{ $tr['language'] }}" onchange="window.location.href=this.value">
                    <option value="{{ request()->fullUrlWithQuery(['admin_locale' => 'vi']) }}" @selected(($adminLocale ?? 'vi') !== 'en')>{{ $tr['vietnamese'] }}</option>
                    <option value="{{ request()->fullUrlWithQuery(['admin_locale' => 'en']) }}" @selected(($adminLocale ?? 'vi') === 'en')>{{ $tr['english'] }}</option>
                </select>
            </span>
        </div>
        <h1>{{ $tr['login_title'] }}</h1>
        <p>{{ $tr['login_intro'] }}</p>

        @if (isset($errors) && $errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="/admin/login">
            @csrf
            <label>
                {{ $tr['password'] }}
                <input type="password" name="password" autocomplete="current-password" required autofocus>
            </label>
            <button type="submit">{{ $tr['login_button'] }}</button>
        </form>
        <a href="/">{{ $tr['back_to_landing'] }}</a>
    </main>
</body>
</html>
