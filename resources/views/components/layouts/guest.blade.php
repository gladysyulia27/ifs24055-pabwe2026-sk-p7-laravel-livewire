<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Stockwise - Aplikasi Manajemen Inventaris dan Stok Barang">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Stock Barang') }}</title>
    @php
        $inlineCss = '';
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            if ($cssFile && file_exists(public_path('build/' . $cssFile))) {
                $inlineCss = file_get_contents(public_path('build/' . $cssFile));
            }
        }
    @endphp
    <style>
        {!! $inlineCss !!}
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
    @php
        $lwScriptTag = \Livewire\Mechanisms\FrontendAssets\FrontendAssets::js([]);
        $lwAttrs = [];
        if (preg_match_all('/([\w-]+)="([^"]*)"/', $lwScriptTag, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $lwAttrs[$m[1]] = $m[2];
            }
        }
    @endphp
    <script>
        (function () {
            var attrs = @json($lwAttrs);
            var loaded = false, ready = false;
            var dirtyValues = new Map();
            var pendingSubmit = null;
            var pendingClicks = [];
            var events = ['pointerdown', 'mousedown', 'touchstart', 'focusin', 'keydown', 'input', 'change', 'click'];

            function trackInput(e) {
                if (ready) return;
                var el = e.target;
                if (el && (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA')) {
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        dirtyValues.set(el, el.checked);
                    } else if (el.type !== 'file') {
                        dirtyValues.set(el, el.value);
                    }
                }
            }

            function interceptSubmit(e) {
                if (ready) return;
                var form = e.target;
                if (form && form.hasAttribute && (form.hasAttribute('wire:submit') || form.hasAttribute('wire:submit.prevent'))) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    pendingSubmit = form;
                    loadLivewire();
                }
            }

            function interceptClick(e) {
                if (ready) return;
                var el = e.target && e.target.closest ? e.target.closest('[wire\\:click],[wire\\:click\\.prevent]') : null;
                if (el) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    pendingClicks.push(el);
                    loadLivewire();
                }
            }

            document.addEventListener('input', trackInput, true);
            document.addEventListener('change', trackInput, true);
            document.addEventListener('submit', interceptSubmit, true);
            document.addEventListener('click', interceptClick, true);

            function loadLivewire() {
                if (loaded) return;
                loaded = true;
                events.forEach(function (ev) {
                    window.removeEventListener(ev, loadLivewire, true);
                });
                if (!attrs.src) return;
                var s = document.createElement('script');
                Object.keys(attrs).forEach(function (k) {
                    s.setAttribute(k, attrs[k]);
                });
                s.onload = function () {
                    document.querySelectorAll('input, select, textarea').forEach(function (el) {
                        if (!dirtyValues.has(el) && el.type !== 'file' && el.value !== el.defaultValue) {
                            dirtyValues.set(el, el.value);
                        }
                    });
                    if (window.Livewire && !window.Livewire.initialRenderIsFinished) {
                        try { window.Livewire.start(); } catch (err) {}
                    }
                    ready = true;
                    document.removeEventListener('input', trackInput, true);
                    document.removeEventListener('change', trackInput, true);
                    document.removeEventListener('submit', interceptSubmit, true);
                    document.removeEventListener('click', interceptClick, true);

                    dirtyValues.forEach(function (val, el) {
                        if (el.type === 'checkbox' || el.type === 'radio') {
                            el.checked = val;
                        } else if (el.type !== 'file') {
                            el.value = val;
                        }
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    dirtyValues.clear();

                    setTimeout(function () {
                        if (pendingSubmit) {
                            var f = pendingSubmit;
                            pendingSubmit = null;
                            f.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                        }
                        while (pendingClicks.length > 0) {
                            var c = pendingClicks.shift();
                            c.click();
                        }
                    }, 0);
                };
                document.body.appendChild(s);
            }

            events.forEach(function (ev) {
                window.addEventListener(ev, loadLivewire, { capture: true, passive: true });
            });
        })();
    </script>
</body>
</html>
