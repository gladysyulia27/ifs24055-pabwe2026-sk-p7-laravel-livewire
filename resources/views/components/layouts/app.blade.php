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
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .nav-link { display:flex; align-items:center; gap:.65rem; border-radius:10px; padding:.65rem .85rem; color:#cbd5e1; font-weight:600; font-size:.9rem; }
        .nav-link.active, .nav-link:hover { background: rgba(255,255,255,.1); color:#fff; }
        th { color:#334155; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="min-h-screen md:flex">
        <aside class="sidebar text-white md:w-64 p-5" aria-label="Sidebar navigasi" title="Sidebar navigasi">
            <a href="/" class="text-xl font-bold tracking-tight">Stock<span class="text-blue-300">wise.</span></a>
            <p class="text-xs text-slate-300 mt-1 mb-8">INVENTORY MANAGEMENT</p>
            <nav class="space-y-2" aria-label="Navigasi utama">
                @if(auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="/admin/users">▦ <span>Pengguna</span></a>
                @else
                    <a class="nav-link {{ request()->is('stocks*') ? 'active' : '' }}" href="/stocks">▤ <span>Daftar Stok</span></a>
                @endif
                <a class="nav-link {{ request()->is('profile') ? 'active' : '' }}" href="/profile">◎ <span>Profil</span></a>
            </nav>
            <div class="mt-12 border-t border-slate-700 pt-4">
                <div class="flex items-center gap-3 mb-3">
                    @if(auth()->user()->profile_photo_path)
                        <img src="/storage/{{ auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-600">
                    @else
                        <div class="w-9 h-9 rounded-full bg-blue-600 text-white grid place-items-center text-sm font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-300">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="text-sm text-slate-300 hover:text-white">Keluar ↗</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 min-w-0 p-5 md:p-9">
            <div class="max-w-7xl mx-auto">
                @if(session('success'))
                    <div class="mb-5 rounded-xl bg-green-50 border border-green-200 text-green-800 p-3 text-sm">{{ session('success') }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
