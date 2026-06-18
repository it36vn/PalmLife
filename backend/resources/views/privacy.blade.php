<!doctype html>
@php
    $c = fn (string $key): string => (string) ($content[$key] ?? '');
    $lines = fn (string $key): array => array_values(array_filter(array_map('trim', preg_split('/\R/', $c($key)) ?: [])));
    $contact = str_replace(
        [':email', ':phone'],
        [
            '<a href="mailto:'.e(config('app.support_email')).'">'.e(config('app.support_email')).'</a>',
            '<a href="tel:'.e(config('app.support_phone')).'">'.e(config('app.support_phone')).'</a>',
        ],
        e($c('privacy_contact_body'))
    );
@endphp
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $c('privacy_title') }} | {{ $c('site_name') }}</title>
    <style>
        :root { color-scheme: light; --ink:#16201d; --muted:#5b6762; --paper:#f5f7f6; --line:#d9ded7; --brand:#0d7c66; --brand-2:#18ad86; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:linear-gradient(180deg,#fbfcfb,var(--paper)); color:var(--ink); }
        header, main { max-width: 920px; margin: 0 auto; padding: 20px; }
        nav { display:flex; align-items:center; justify-content:space-between; gap:16px; }
        a { color:var(--brand); text-decoration:none; font-weight:750; }
        .brand { display:flex; align-items:center; gap:10px; font-size:22px; font-weight:900; }
        .mark { width:36px; height:36px; border-radius:8px; display:grid; place-items:center; background:linear-gradient(135deg,var(--brand),var(--brand-2)); color:#fff; }
        .language-pill { position:relative; display:inline-flex; align-items:center; min-height:40px; border:1px solid var(--line); border-radius:999px; background:rgba(255,255,255,.9); color:var(--muted); font-weight:800; box-shadow:0 8px 22px rgba(20,41,35,.06); }
        .language-pill:before { content:"🌐"; padding-left:12px; font-size:14px; }
        .language-pill select { appearance:none; border:0; background:transparent; color:var(--muted); font:inherit; font-weight:850; padding:9px 16px 9px 8px; outline:0; cursor:pointer; }
        /* .language-pill:after { content:"⌄"; position:absolute; right:12px; pointer-events:none; color:var(--brand); font-weight:900; } */
        .panel { background:#fff; border:1px solid var(--line); border-radius:8px; padding:24px; margin-top:20px; box-shadow:0 18px 45px rgba(20,41,35,.08); }
        h1 { font-size:clamp(34px,6vw,56px); line-height:1.03; margin:0 0 12px; letter-spacing:0; }
        h2 { margin:28px 0 10px; font-size:22px; letter-spacing:0; }
        p, li { color:var(--muted); line-height:1.7; }
        ul { padding-left:20px; }
        .meta { color:var(--muted); font-size:14px; }
    </style>
</head>
<body>
<header>
    <nav>
        <a class="brand" href="/{{ $locale === 'en' ? 'en' : '' }}"><img class="mark" src="/icon.png" alt="{{ $c('site_name') }}" width="32" height="32">{{ $c('site_name') }}</a>
        <span class="language-pill">
            <select aria-label="{{ $locale === 'en' ? 'Language' : 'Ngôn ngữ' }}" onchange="window.location.href=this.value">
                <option value="/privacy" @selected($locale !== 'en')>Tiếng Việt</option>
                <option value="/privacy/en" @selected($locale === 'en')>English</option>
            </select>
        </span>
    </nav>
</header>
<main>
    <article class="panel">
        <h1>{{ $c('privacy_title') }}</h1>
        <p class="meta">{{ $c('privacy_updated') }}</p>
        <p>{{ $c('privacy_intro') }}</p>
        <h2>{{ $c('privacy_info_title') }}</h2>
        <ul>
            @foreach ($lines('privacy_info_items') as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
        <h2>{{ $c('privacy_use_title') }}</h2>
        <ul>
            @foreach ($lines('privacy_use_items') as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
        <h2>{{ $c('privacy_images_title') }}</h2>
        <p>{{ $c('privacy_images_body') }}</p>
        <h2>{{ $c('privacy_controls_title') }}</h2>
        <p>{{ $c('privacy_controls_body') }}</p>
        <h2>{{ $c('privacy_children_title') }}</h2>
        <p>{{ $c('privacy_children_body') }}</p>
        <h2>{{ $c('privacy_contact_title') }}</h2>
        <p>{!! $contact !!}</p>
    </article>
</main>
</body>
</html>
