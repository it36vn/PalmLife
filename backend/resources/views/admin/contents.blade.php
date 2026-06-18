<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin — Contents</title>
    <style>body{font-family:system-ui,Segoe UI,Roboto,Arial;padding:20px}</style>
</head>
<body>
    <h1>Editable site content</h1>

    @if(session('success'))
        <div style="padding:8px;background:#e6ffef;border:1px solid #b7f0d0;margin-bottom:12px">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('admin.contents.update') }}">
        @csrf
        @foreach($contents as $content)
            <div style="margin-bottom:18px">
                <label style="font-weight:700">{{ $content->key }} ({{ $content->locale }})</label>
                <textarea name="contents[{{ $content->id }}]" rows="4" style="width:100%;margin-top:6px">{{ $content->content }}</textarea>
            </div>
        @endforeach
        <button type="submit" style="padding:10px 14px;border-radius:8px;background:#0d7c66;color:#fff;border:0">Save</button>
    </form>
</body>
</html>
