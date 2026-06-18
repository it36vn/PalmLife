<!doctype html>
@php
    $c = fn (string $key): string => (string) ($content[$key] ?? '');
@endphp
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $c('site_name') }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #16201d;
            --muted: #5b6762;
            --paper: #f5f7f6;
            --panel: #ffffff;
            --line: #d9ded7;
            --brand: #0d7c66;
            --brand-2: #18ad86;
            --gold: #b88728;
            --rose: #c85d75;
            --shadow: 0 22px 55px rgba(20, 41, 35, .10);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 8% 0%, rgba(24, 173, 134, .17), transparent 30rem),
                linear-gradient(180deg, #fbfcfb 0%, var(--paper) 56%, #eef4f1 100%);
            color: var(--ink);
        }
        header, main { max-width: 1180px; margin: 0 auto; padding: 20px; }
        nav { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        a { color: var(--brand); text-decoration: none; font-weight: 650; }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 24px; font-weight: 900; }
        .mark { width: 38px; height: 38px; border-radius: 8px; display: grid; place-items: center; background: linear-gradient(135deg, var(--brand), var(--brand-2)); color: #fff; font-size: 16px; }
        .hero { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(280px, .9fr); gap: 28px; align-items: center; padding-top: 28px; }
        h1 { font-size: clamp(36px, 6vw, 68px); line-height: 1; margin: 0 0 16px; letter-spacing: 0; }
        h2 { font-size: 28px; margin: 0 0 16px; letter-spacing: 0; }
        p { color: var(--muted); line-height: 1.6; margin: 0; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .button { border: 1px solid var(--brand); border-radius: 8px; padding: 12px 16px; background: var(--brand); color: white; display: inline-flex; align-items: center; gap: 8px; }
        .button.secondary { background: transparent; color: var(--brand); }
        .store-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .store { border: 1px solid var(--line); border-radius: 8px; padding: 11px 14px; background: #fff; color: var(--ink); display: inline-flex; flex-direction: column; gap: 2px; min-width: 150px; box-shadow: 0 8px 20px rgba(20,41,35,.05); }
        .store span { color: var(--muted); font-size: 12px; font-weight: 650; }
        .store strong { font-size: 16px; }
        .preview { background: rgba(255,255,255,.88); border: 1px solid var(--line); border-radius: 8px; padding: 18px; box-shadow: var(--shadow); }
        .phone { border-radius: 28px; border: 10px solid #17221f; background: #f9fbf8; min-height: 480px; padding: 18px; box-shadow: inset 0 0 0 1px #dfe6e1; }
        .scan { min-height: 250px; border: 1px solid #dce5df; border-radius: 8px; background: linear-gradient(145deg, #f7faf8, #e8f4ee); position: relative; overflow: hidden; }
        .scan:before { content: ""; position: absolute; inset: 28px 42px; border: 2px solid rgba(13,124,102,.42); border-radius: 46% 44% 48% 48%; transform: rotate(-7deg); }
        .scan:after { content: ""; position: absolute; left: 20%; right: 20%; top: 45%; height: 3px; background: linear-gradient(90deg, transparent, var(--brand-2), transparent); box-shadow: 0 38px 0 rgba(24,173,134,.62), 0 -42px 0 rgba(184,135,40,.48); }
        .mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
        .mini { border: 1px solid var(--line); border-radius: 8px; padding: 12px; background: white; }
        .section { padding-top: 36px; }
        .plans { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .plan { background: rgba(255,255,255,.92); border: 1px solid var(--line); border-radius: 8px; padding: 16px; min-height: 170px; box-shadow: 0 12px 28px rgba(20,41,35,.06); }
        .plan strong { display: block; font-size: 18px; margin-bottom: 8px; }
        .price { color: var(--gold); font-weight: 800; margin: 10px 0; }
        .guardrails { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .guardrail { background: transparent; border-top: 1px solid var(--line); padding-top: 14px; }
        .nav-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
        .language-pill { position: relative; display: inline-flex; align-items: center; min-height: 40px; border: 1px solid var(--line); border-radius: 999px; background: rgba(255,255,255,.88); color: var(--muted); font-weight: 800; box-shadow: 0 8px 22px rgba(20,41,35,.06); }
        .language-pill:before { content: "🌐"; padding-left: 12px; font-size: 14px; }
        .language-pill select { appearance: none; border: 0; background: transparent; color: var(--muted); font: inherit; font-weight: 850; padding: 9px 16px 9px 8px; outline: 0; cursor: pointer; }
        /* .language-pill:after { content: ""; position: absolute; right: 12px; pointer-events: none; color: var(--brand); font-weight: 900; } */
        footer { padding: 28px 20px 40px; text-align: center; color: var(--muted); }
        @media (max-width: 880px) {
            .hero, .plans, .guardrails { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<header>
    <nav>
        <div class="brand"><img class="mark" src="/icon.png" alt="{{ $c('site_name') }}" width="32" height="32">{{ $c('site_name') }}</div>
        <div class="nav-actions">
            <a href="/privacy/{{ $locale === 'en' ? 'en' : '' }}">{{ $c('privacy_nav') }}</a>
            <span class="language-pill">
                <select aria-label="{{ $locale === 'en' ? 'Language' : 'Ngôn ngữ' }}" onchange="window.location.href=this.value">
                    <option value="/" @selected($locale !== 'en')>Tiếng Việt</option>
                    <option value="/en" @selected($locale === 'en')>English</option>
                </select>
            </span>
        </div>
    </nav>
</header>
<main>
    <section class="hero">
        <div>
            <h1>{{ $c('site_name') }}</h1>
            <p>{{ $c('hero_description') }}</p>
            <div class="actions">
                <a class="button" href="#plans">{{ $c('hero_primary_button') }}</a>
                <a class="button secondary" href="#safety">{{ $c('hero_secondary_button') }}</a>
            </div>
            <div class="store-buttons" aria-label="{{ $c('download_label') }}">
                <a class="store" href="{{ config('app.app_store_url') }}">
                    <span>{{ $c('app_store_prefix') }}</span>
                    <strong>App Store</strong>
                </a>
                <a class="store" href="{{ config('app.google_play_url') }}">
                    <span>{{ $c('google_play_prefix') }}</span>
                    <strong>Google Play</strong>
                </a>
            </div>
        </div>
        <div class="preview" aria-label="{{ $c('preview_label') }}">
            <div class="phone">
                <div class="scan"></div>
                <div class="mini-grid">
                    <div class="mini">
                        <strong>{{ $c('mini_free_title') }}</strong>
                        <p>{{ $c('mini_free_body') }}</p>
                    </div>
                    <div class="mini">
                        <strong>{{ $c('mini_privacy_title') }}</strong>
                        <p>{{ $c('mini_privacy_body') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="plans" class="section">
        <h2>{{ $c('plans_title') }}</h2>
        <div class="plans">
            @foreach ($plans as $plan)
                <article class="plan">
                    <strong>{{ $locale === 'en' ? $plan->name_en : $plan->name_vi }}</strong>
                    <p>{{ $locale === 'en' ? $plan->description_en : $plan->description_vi }}</p>
                    <div class="price">{{ number_format($plan->price_vnd, 0, ',', '.') }} {{ $locale === 'en' ? 'VND' : 'đ' }}</div>
                    <p>{{ $c('plan_footer') }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="safety" class="section">
        <h2>{{ $c('safety_title') }}</h2>
        <div class="guardrails">
            <div class="guardrail">
                <strong>{{ $c('safety_1_title') }}</strong>
                <p>{{ $c('safety_1_body') }}</p>
            </div>
            <div class="guardrail">
                <strong>{{ $c('safety_2_title') }}</strong>
                <p>{{ $c('safety_2_body') }}</p>
            </div>
            <div class="guardrail">
                <strong>{{ $c('safety_3_title') }}</strong>
                <p>{{ $c('safety_3_body') }}</p>
            </div>
        </div>
    </section>
</main>
<footer>
    {{ $c('footer_text') }}
    <br>
    <a href="/privacy/{{ $locale === 'en' ? 'en' : '' }}">{{ $c('privacy_title') }}</a>
</footer>
</body>
</html>
