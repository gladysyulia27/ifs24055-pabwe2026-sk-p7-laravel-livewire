<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Stock Barang') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root { --ink:#0f172a; --muted:#475569; --line:#e2e8f0; --brand:#0b5bd3; --brand-soft:#eff6ff; --surface:#ffffff; --bg:#f8fafc; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; color: var(--ink); background: radial-gradient(circle at top left, #dbeafe 0%, transparent 28%), var(--bg); }
        .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; border-radius:10px; padding:.55rem .95rem; font-weight:600; font-size:.875rem; transition:.15s; cursor:pointer; }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-muted { background:#f1f5f9; color:#0f172a; border:1px solid var(--line); }
        .btn-muted:hover { background:#e2e8f0; }
        .field { width:100%; border:1px solid #cbd5e1; border-radius:10px; padding:.58rem .8rem; font-size:.9rem; background:#fff; }
        .field:focus { outline:2px solid #93c5fd; border-color:var(--brand); }
        .badge { display:inline-flex; align-items:center; border-radius:999px; padding:.2rem .65rem; font-size:.75rem; font-weight:700; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-yellow { background:#fef9c3; color:#854d0e; }
        .badge-red { background:#fee2e2; color:#991b1b; }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen flex items-center justify-center p-5">
    <main class="w-full max-w-md">{{ $slot }}</main>
    @livewireScripts
</body>
</html>
