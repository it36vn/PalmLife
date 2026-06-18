<!doctype html>
<html lang="{{ $adminLocale ?? 'vi' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $tr['app_name'] }} {{ $tr['admin_console'] }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7f5;
            --surface: #ffffff;
            --surface-soft: #eef5f2;
            --ink: #16201d;
            --muted: #62716b;
            --line: #dce5df;
            --brand: #087761;
            --brand-2: #12a982;
            --gold: #b9862d;
            --rose: #c85d75;
            --blue: #4276b8;
            --shadow: 0 18px 45px rgba(20, 41, 35, .09);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(18, 169, 130, .13), transparent 32rem),
                linear-gradient(180deg, #fbfcfb 0%, var(--bg) 44%, #eef4f1 100%);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }
        .sidebar {
            border-right: 1px solid var(--line);
            background: rgba(255,255,255,.78);
            backdrop-filter: blur(18px);
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 26px; }
        .brand-mark {
            width: 42px; height: 42px; border-radius: 8px;
            display: grid; place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: #fff; font-weight: 900;
            box-shadow: var(--shadow);
        }
        .brand strong { display: block; font-size: 18px; }
        .brand span { color: var(--muted); font-size: 13px; }
        .nav { display: grid; gap: 8px; }
        .nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 12px; border-radius: 8px;
            color: var(--muted); font-weight: 750;
        }
        .nav a.active, .nav a:hover {
            background: var(--surface-soft);
            color: var(--brand);
        }
        .content { padding: 28px; min-width: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px; margin-bottom: 22px;
        }
        h1 { font-size: clamp(28px, 4vw, 42px); line-height: 1.05; margin: 0; letter-spacing: 0; }
        h2 { margin: 0 0 14px; font-size: 20px; letter-spacing: 0; }
        p { margin: 0; color: var(--muted); line-height: 1.55; }
        .pill {
            display: inline-flex; align-items: center; gap: 8px;
            min-height: 36px; padding: 8px 12px; border-radius: 999px;
            background: var(--surface); border: 1px solid var(--line);
            color: var(--muted); font-weight: 750;
        }
        .grid { display: grid; gap: 16px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .two { grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr); }
        .card {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(220,229,223,.9);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 18px;
        }
        .stat { min-height: 128px; display: grid; align-content: space-between; }
        .stat .value { font-size: 38px; font-weight: 900; letter-spacing: 0; }
        .tone-green { border-top: 4px solid var(--brand); }
        .tone-gold { border-top: 4px solid var(--gold); }
        .tone-blue { border-top: 4px solid var(--blue); }
        .tone-rose { border-top: 4px solid var(--rose); }
        .chart {
            width: 100%;
            min-height: 260px;
            overflow: visible;
        }
        .list { display: grid; gap: 10px; }
        .row {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line);
        }
        .row:last-child { border-bottom: 0; }
        .meta { color: var(--muted); font-size: 13px; }
        .toolbar {
            display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between;
            align-items: center; margin-bottom: 14px;
        }
        .language-pill { position: relative; display: inline-flex; align-items: center; min-height: 36px; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--muted); font-weight: 750; }
        .language-pill:before { content: "🌐"; padding-left: 11px; font-size: 14px; }
        .language-pill select { appearance: none; border: 0; background: transparent; color: var(--muted); font-weight: 800; padding: 8px 16px 8px 8px; outline: 0; cursor: pointer; }
        /* .language-pill:after { content: "⌄"; position: absolute; right: 11px; pointer-events: none; color: var(--brand); font-weight: 900; } */
        .search { display: flex; gap: 8px; flex-wrap: wrap; }
        .input, .select, textarea {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 8px;
            padding: 11px 12px;
            min-height: 42px;
            color: var(--ink);
        }
        textarea { width: 100%; resize: vertical; min-height: 78px; }
        .button {
            border: 0; border-radius: 8px; padding: 11px 14px;
            min-height: 42px; background: var(--brand); color: #fff;
            font-weight: 850; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .button.secondary { background: #e7efeb; color: var(--brand); }
        .button.danger { background: #fff1f3; color: #aa314b; }
        .button.compact { min-height: 34px; padding: 7px 10px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 13px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .table-wrap { overflow-x: auto; }
        .badge {
            display: inline-flex; align-items: center; min-height: 28px; padding: 5px 9px;
            border-radius: 999px; background: var(--surface-soft); color: var(--brand);
            font-weight: 800; font-size: 12px;
        }
        .form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .form-grid .wide { grid-column: span 2; }
        .form-grid .full { grid-column: 1 / -1; }
        label { display: grid; gap: 6px; color: var(--muted); font-size: 13px; font-weight: 750; }
        .plan-edit { display: grid; gap: 12px; }
        details { border: 1px solid var(--line); border-radius: 8px; padding: 14px; background: #fff; }
        summary { cursor: pointer; font-weight: 850; }
        .pagination { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .pagination nav { display: flex; flex-wrap: wrap; gap: 8px; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 38px; min-height: 38px; padding: 8px 10px;
            border: 1px solid var(--line); border-radius: 8px; background: #fff;
        }
        .pagination [aria-current="page"] span { background: var(--brand); color: #fff; border-color: var(--brand); }
        .status {
            margin-bottom: 14px; padding: 12px 14px; border-radius: 8px;
            border: 1px solid #bfe3d5; background: #effaf5; color: #0d6b56;
            font-weight: 750;
        }
        .errors {
            margin-bottom: 14px; padding: 12px 14px; border-radius: 8px;
            border: 1px solid #f1c0ca; background: #fff3f5; color: #9d2942;
        }
        @media (max-width: 1040px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; border-right: 0; border-bottom: 1px solid var(--line); }
            .nav { grid-template-columns: repeat(6, minmax(0, 1fr)); }
            .stats, .two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 720px) {
            .content, .sidebar { padding: 18px 14px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .nav, .stats, .two, .form-grid { grid-template-columns: 1fr; }
            .form-grid .wide { grid-column: auto; }
            .row { align-items: flex-start; flex-direction: column; }
            th, td { padding: 11px 8px; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="/admin">
            <img class="brand-mark" src="/icon.png" alt="Xem Chi Tay" width="32" height="32">
            <span>
                <strong>{{ $tr['app_name'] }}</strong>
                <span>{{ $tr['admin_console'] }}</span>
            </span>
        </a>
        <nav class="nav" aria-label="Admin">
            <a class="{{ $page === 'dashboard' ? 'active' : '' }}" href="/admin">{{ $tr['dashboard'] }}</a>
            <a class="{{ $page === 'users' ? 'active' : '' }}" href="/admin/users">{{ $tr['users'] }}</a>
            <a class="{{ $page === 'analyses' ? 'active' : '' }}" href="/admin/analyses">{{ $tr['ai_history'] }}</a>
            <a class="{{ $page === 'notifications' ? 'active' : '' }}" href="/admin/notifications">{{ $tr['notifications'] }}</a>
            <a class="{{ $page === 'plans' ? 'active' : '' }}" href="/admin/plans">{{ $tr['plans'] }}</a>
            <a class="{{ $page === 'contents' ? 'active' : '' }}" href="/admin/contents">{{ $tr['site_content'] }}</a>
        </nav>
    </aside>

    <main class="content">
        <div class="topbar">
            <div>
                <h1>{{ $title }}</h1>
                <p>{{ $tr['subtitle'] }}</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a class="pill" href="/" target="_blank" rel="noreferrer">{{ $tr['open_landing'] }}</a>
                <span class="language-pill">
                    <select aria-label="{{ $tr['language'] }}" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['admin_locale' => 'vi']) }}" @selected(($adminLocale ?? 'vi') !== 'en')>{{ $tr['vietnamese'] }}</option>
                        <option value="{{ request()->fullUrlWithQuery(['admin_locale' => 'en']) }}" @selected(($adminLocale ?? 'vi') === 'en')>{{ $tr['english'] }}</option>
                    </select>
                </span>
                <form method="post" action="/admin/logout">
                    @csrf
                    <button class="pill" type="submit" style="cursor:pointer">{{ $tr['logout'] }}</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if ($page === 'dashboard')
            <section class="grid stats">
                @foreach ($stats as $stat)
                    <article class="card stat tone-{{ $stat['tone'] }}">
                        <p>{{ $stat['label'] }}</p>
                        <div class="value">{{ number_format($stat['value']) }}</div>
                    </article>
                @endforeach
            </section>

            <section class="grid two" style="margin-top:16px">
                <article class="card">
                    <h2>{{ $tr['readings_7_days'] }}</h2>
                    @php
                        $max = max(1, $chart->max('value'));
                        $barWidth = 54;
                        $gap = 24;
                    @endphp
                    <svg class="chart" viewBox="0 0 560 260" role="img" aria-label="{{ $tr['chart_label'] }}">
                        <line x1="28" y1="210" x2="540" y2="210" stroke="#dce5df" />
                        @foreach ($chart as $i => $point)
                            @php
                                $height = 24 + (($point['value'] / $max) * 152);
                                $x = 42 + ($i * ($barWidth + $gap));
                                $y = 210 - $height;
                            @endphp
                            <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $height }}" rx="8" fill="#087761" opacity="{{ .52 + ($point['value'] / $max) * .42 }}" />
                            <text x="{{ $x + 27 }}" y="{{ $y - 8 }}" text-anchor="middle" fill="#16201d" font-size="13" font-weight="800">{{ $point['value'] }}</text>
                            <text x="{{ $x + 27 }}" y="236" text-anchor="middle" fill="#62716b" font-size="12">{{ $point['label'] }}</text>
                        @endforeach
                    </svg>
                </article>

                <article class="card">
                    <h2>{{ $tr['selling_plans'] }}</h2>
                    <div class="list">
                        @foreach ($plans as $plan)
                            <div class="row">
                                <div>
                                    <strong>{{ ($adminLocale ?? 'vi') === 'en' ? $plan->name_en : $plan->name_vi }}</strong>
                                    <div class="meta">{{ $plan->quota_limit ?? $tr['unlimited'] }} {{ $tr['readings'] }} / {{ $plan->quota_period }}</div>
                                </div>
                                <span class="badge">{{ number_format($plan->price_vnd, 0, ',', '.') }} {{ $tr['currency_suffix'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="grid two" style="margin-top:16px">
                <article class="card">
                    <h2>{{ $tr['latest_users'] }}</h2>
                    <div class="list">
                        @foreach ($latestUsers as $user)
                            <div class="row">
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <div class="meta">{{ $user->email }}</div>
                                </div>
                                <span class="meta">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="card">
                    <h2>{{ $tr['latest_readings'] }}</h2>
                    <div class="list">
                        @foreach ($latestReadings as $reading)
                            <div class="row">
                                <div>
                                    <strong>{{ $reading->user?->name ?? $tr['deleted_user'] }}</strong>
                                    <div class="meta">{{ $reading->type }} • {{ $reading->locale }} • {{ $reading->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <span class="badge">{{ $reading->result['provider'] ?? 'free-local' }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>
        @endif

        @if ($page === 'users')
            <section class="card">
                <div class="toolbar">
                    <h2>{{ $tr['user_list'] }}</h2>
                    <form class="search" method="get" action="/admin/users">
                        <input class="input" name="q" value="{{ $search }}" placeholder="{{ $tr['search_placeholder'] }}">
                        <button class="button" type="submit">{{ $tr['search'] }}</button>
                    </form>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>{{ $tr['id'] }}</th><th>{{ $tr['name'] }}</th><th>{{ $tr['email'] }}</th><th>{{ $tr['status'] }}</th><th>{{ $tr['read_count'] }}</th><th>{{ $tr['created_at'] }}</th><th>{{ $tr['actions'] }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->locked_at)
                                            <span class="badge" style="background:#fff1f3;color:#aa314b">{{ $tr['locked'] }}</span>
                                            <div class="meta">{{ $user->locked_at->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="badge">{{ $tr['active'] }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge">{{ $user->analysis_requests_count }}</span></td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form method="post" action="/admin/users/{{ $user->id }}/toggle-lock">
                                            @csrf
                                            <button class="button compact {{ $user->locked_at ? 'secondary' : 'danger' }}" type="submit">
                                                {{ $user->locked_at ? $tr['unlock'] : $tr['lock'] }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.admin-pagination', ['paginator' => $users])
            </section>
        @endif

        @if ($page === 'analyses')
            <section class="card">
                <div class="toolbar">
                    <h2>{{ $tr['ai_history'] }}</h2>
                    <form class="search" method="get" action="/admin/analyses">
                        <select class="select" name="type">
                            <option value="">{{ $tr['all_types'] }}</option>
                            @foreach ($tr['reading_types'] as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="button" type="submit">{{ $tr['filter'] }}</button>
                    </form>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>{{ $tr['id'] }}</th><th>{{ $tr['users'] }}</th><th>{{ $tr['type'] }}</th><th>{{ $tr['provider'] }}</th><th>{{ $tr['image_hash'] }}</th><th>{{ $tr['created_at'] }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($analyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->id }}</td>
                                    <td>{{ $analysis->user?->email ?? $tr['deleted_user'] }}</td>
                                    <td><span class="badge">{{ $tr['reading_types'][$analysis->type] ?? $analysis->type }}</span></td>
                                    <td>{{ $analysis->result['provider'] ?? 'free-local' }}</td>
                                    <td><span class="meta">{{ str($analysis->input_hash)->limit(18) }}</span></td>
                                    <td>{{ $analysis->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.admin-pagination', ['paginator' => $analyses])
            </section>
        @endif

        @if ($page === 'notifications')
            <section class="card" style="margin-bottom:16px">
                <div class="toolbar">
                    <h2>{{ $tr['send_notification'] }}</h2>
                    <span class="pill">{{ $tr['notification_target_hint'] }}</span>
                </div>
                <form class="form-grid" method="post" action="/admin/notifications">
                    @csrf
                    <label class="wide">
                        {{ $tr['send_to'] }}
                        <select class="select" name="target" id="notification-target">
                            <option value="all" @selected(old('target', 'all') === 'all')>{{ $tr['send_all_users'] }}</option>
                            <option value="selected" @selected(old('target') === 'selected')>{{ $tr['send_selected_users'] }}</option>
                        </select>
                    </label>
                    <label class="wide">
                        {{ $tr['selected_users'] }}
                        <select class="select" name="user_ids[]" multiple size="8">
                            @foreach ($notificationUsers as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                    {{ $user->name }} • {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                        <span class="meta">{{ $tr['selected_users_hint'] }}</span>
                    </label>
                    <label class="wide">
                        {{ $tr['title_vi'] }}
                        <input class="input" name="title_vi" value="{{ old('title_vi') }}" required>
                    </label>
                    <label class="wide">
                        {{ $tr['title_en'] }}
                        <input class="input" name="title_en" value="{{ old('title_en') }}" required>
                    </label>
                    <label class="wide">
                        {{ $tr['body_vi'] }}
                        <textarea name="body_vi" rows="5" required>{{ old('body_vi') }}</textarea>
                    </label>
                    <label class="wide">
                        {{ $tr['body_en'] }}
                        <textarea name="body_en" rows="5" required>{{ old('body_en') }}</textarea>
                    </label>
                    <div class="full"><button class="button" type="submit">{{ $tr['send_notification_button'] }}</button></div>
                </form>
            </section>

            <section class="card">
                <div class="toolbar">
                    <h2>{{ $tr['recent_notifications'] }}</h2>
                    <span class="pill">{{ $notifications->total() }} {{ $tr['notifications'] }}</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>{{ $tr['id'] }}</th><th>{{ $tr['users'] }}</th><th>{{ $tr['title'] }}</th><th>{{ $tr['content'] }}</th><th>{{ $tr['status'] }}</th><th>{{ $tr['created_at'] }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->id }}</td>
                                    <td>{{ $notification->user?->email ?? $tr['deleted_user'] }}</td>
                                    <td>
                                        <strong>{{ ($adminLocale ?? 'vi') === 'en' ? $notification->title_en : $notification->title_vi }}</strong>
                                        <div class="meta">{{ $notification->type }}</div>
                                    </td>
                                    <td>{{ str(($adminLocale ?? 'vi') === 'en' ? $notification->body_en : $notification->body_vi)->limit(90) }}</td>
                                    <td>
                                        @if ($notification->read_at)
                                            <span class="badge">{{ $tr['read'] }}</span>
                                            <div class="meta">{{ $notification->read_at->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="badge" style="background:#fff6df;color:#946300">{{ $tr['unread'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.admin-pagination', ['paginator' => $notifications])
            </section>
        @endif

        @if ($page === 'plans')
            <details class="card" style="margin-bottom:16px">
                <summary>{{ $tr['add_plan'] }}</summary>
                <p style="margin:8px 0 14px">{{ $tr['collapse_hint'] }}</p>
                <form class="form-grid" method="post" action="/admin/plans">
                    @csrf
                    @include('partials.admin-plan-fields', ['plan' => null])
                    <div class="full"><button class="button" type="submit">{{ $tr['add_plan_button'] }}</button></div>
                </form>
            </details>

            <section class="card">
                <div class="toolbar">
                    <h2>{{ $tr['manage_plans'] }}</h2>
                    <span class="pill">{{ $plans->total() }} {{ $tr['plan_count'] }}</span>
                </div>
                <div class="plan-edit">
                    @foreach ($plans as $plan)
                        <details>
                            <summary>{{ ($adminLocale ?? 'vi') === 'en' ? $plan->name_en : $plan->name_vi }} • {{ number_format($plan->price_vnd, 0, ',', '.') }} {{ $tr['currency_suffix'] }} • {{ $plan->code }}</summary>
                            <form class="form-grid" method="post" action="/admin/plans/{{ $plan->id }}" style="margin-top:14px">
                                @csrf
                                @method('PUT')
                                @include('partials.admin-plan-fields', ['plan' => $plan])
                                <div class="full" style="display:flex;gap:10px;flex-wrap:wrap">
                                    <button class="button" type="submit">{{ $tr['save_changes'] }}</button>
                                </div>
                            </form>
                            <form method="post" action="/admin/plans/{{ $plan->id }}" style="margin-top:10px">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">{{ $tr['delete_plan'] }}</button>
                            </form>
                        </details>
                    @endforeach
                </div>
                @include('partials.admin-pagination', ['paginator' => $plans])
            </section>
        @endif

        @if ($page === 'contents')
            <section class="card">
                <div class="toolbar">
                    <h2>{{ $tr['site_content'] }}</h2>
                    <span class="pill">{{ $tr['content_saved_hint'] }}</span>
                </div>
                <form method="post" action="/admin/contents">
                    @csrf
                    @foreach ($contentGroups as $group => $items)
                        <details class="plan-edit" open style="margin-bottom:14px">
                            <summary>{{ $tr['content_groups'][$group] ?? $group }}</summary>
                            <div class="grid" style="gap:12px;margin-top:14px">
                                @foreach ($items as $item)
                                    <div class="card" style="box-shadow:none;background:#fbfdfc">
                                        <h2 style="font-size:16px;margin-bottom:10px">{{ $item->label }}</h2>
                                        <div class="form-grid">
                                            <label class="wide">
                                                {{ $tr['vietnamese_content'] }}
                                                @if ($item->type === 'textarea')
                                                    <textarea name="content[{{ $item->id }}][value_vi]" rows="5" required>{{ old("content.{$item->id}.value_vi", $item->value_vi) }}</textarea>
                                                @else
                                                    <input class="input" name="content[{{ $item->id }}][value_vi]" value="{{ old("content.{$item->id}.value_vi", $item->value_vi) }}" required>
                                                @endif
                                            </label>
                                            <label class="wide">
                                                {{ $tr['english_content'] }}
                                                @if ($item->type === 'textarea')
                                                    <textarea name="content[{{ $item->id }}][value_en]" rows="5" required>{{ old("content.{$item->id}.value_en", $item->value_en) }}</textarea>
                                                @else
                                                    <input class="input" name="content[{{ $item->id }}][value_en]" value="{{ old("content.{$item->id}.value_en", $item->value_en) }}" required>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                    <button class="button" type="submit">{{ $tr['save_changes'] }}</button>
                </form>
            </section>
        @endif
    </main>
</div>
</body>
</html>
