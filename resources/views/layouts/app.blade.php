<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaceWidgets — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* ─────────────────────────────────────────
           DESIGN TOKENS — light & dark
        ───────────────────────────────────────── */
        :root {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --bg:                #f6f7f9;
            --bg-elevated:       #ffffff;
            --bg-subtle:         #f0f2f6;
            --bg-hover:          #f4f5f8;
            --border:            #e3e6ec;
            --border-strong:     #c9cdd8;
            --border-focus:      #2563eb;
            --text-primary:      #111827;
            --text-secondary:    #4b5563;
            --text-muted:        #9ca3af;
            --accent:            #2563eb;
            --accent-hover:      #1d4ed8;
            --accent-text:       #ffffff;
            --accent-subtle:     #eff4ff;
            --accent-subtle-b:   #c7d7fb;
            --danger:            #dc2626;
            --danger-hover:      #b91c1c;
            --danger-subtle:     #fef2f2;
            --danger-subtle-b:   #fecaca;
            --success:           #16a34a;
            --success-subtle:    #f0fdf4;
            --warning:           #d97706;
            --warning-subtle:    #fffbeb;
            --shadow-xs:         0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm:         0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:         0 4px 12px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.04);
            --shadow-lg:         0 12px 28px rgba(0,0,0,0.10), 0 4px 8px rgba(0,0,0,0.05);
            --shadow-modal:      0 20px 48px rgba(0,0,0,0.14), 0 6px 16px rgba(0,0,0,0.07);
            --radius:            9px;
            --radius-lg:         13px;
            --radius-xl:         18px;
            --nav-bg:            rgba(255,255,255,0.90);
        }

        [data-theme="dark"] {
            --bg:                #0e1117;
            --bg-elevated:       #161c27;
            --bg-subtle:         #1c2333;
            --bg-hover:          #1f2b3e;
            --border:            #263044;
            --border-strong:     #334466;
            --border-focus:      #3b82f6;
            --text-primary:      #e8edf5;
            --text-secondary:    #8b97b0;
            --text-muted:        #4d5e78;
            --accent:            #3b82f6;
            --accent-hover:      #60a5fa;
            --accent-text:       #ffffff;
            --accent-subtle:     #162035;
            --accent-subtle-b:   #2a4070;
            --danger:            #f87171;
            --danger-hover:      #fca5a5;
            --danger-subtle:     #2c1212;
            --danger-subtle-b:   #5c2020;
            --success:           #4ade80;
            --success-subtle:    #0e2818;
            --warning:           #fbbf24;
            --warning-subtle:    #261d08;
            --shadow-xs:         0 1px 2px rgba(0,0,0,0.3);
            --shadow-sm:         0 1px 3px rgba(0,0,0,0.35);
            --shadow-md:         0 4px 12px rgba(0,0,0,0.45);
            --shadow-lg:         0 12px 28px rgba(0,0,0,0.55);
            --shadow-modal:      0 20px 48px rgba(0,0,0,0.7);
            --nav-bg:            rgba(14,17,23,0.92);
        }

        /* ─────────────────────────────────────────
           BASE
        ───────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-sans);
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14.5px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background 0.22s ease, color 0.22s ease;
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* ─────────────────────────────────────────
           NAVBAR
        ───────────────────────────────────────── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 200;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: background 0.22s, border-color 0.22s;
        }
        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 0.925rem;
            letter-spacing: -0.02em;
            transition: opacity 0.15s;
        }
        .nav-logo:hover { opacity: 0.7; }
        .nav-logo-mark {
            width: 28px; height: 28px;
            background: var(--accent);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: background 0.22s;
        }

        .nav-right { display: flex; align-items: center; gap: 6px; }

        .nav-link {
            font-size: 0.845rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 5px 11px;
            border-radius: 7px;
            transition: color 0.15s, background 0.15s;
        }
        .nav-link:hover { color: var(--text-primary); background: var(--bg-subtle); }

        /* Theme button */
        .theme-btn {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-elevated);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .theme-btn:hover { background: var(--bg-subtle); color: var(--text-primary); border-color: var(--border-strong); }
        .theme-btn .icon-sun  { display: none; }
        .theme-btn .icon-moon { display: block; }
        [data-theme="dark"] .theme-btn .icon-sun  { display: block; }
        [data-theme="dark"] .theme-btn .icon-moon { display: none; }

        /* Primary CTA */
        .btn-cta {
            font-family: var(--font-sans);
            font-size: 0.825rem;
            font-weight: 600;
            background: var(--accent);
            color: var(--accent-text);
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 6px 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
            transition: background 0.15s, transform 0.12s, box-shadow 0.15s;
            box-shadow: 0 1px 2px rgba(37,99,235,0.18);
        }
        .btn-cta:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(37,99,235,0.22);
        }
        .btn-cta:active { transform: translateY(0); }

        /* Mobile */
        .mobile-btn {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-elevated);
            color: var(--text-secondary);
            cursor: pointer;
            display: none; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .mobile-btn:hover { background: var(--bg-subtle); }
        .mobile-nav { display: none; border-top: 1px solid var(--border); }
        .mobile-nav.open {
            display: flex; flex-direction: column; gap: 4px;
            padding: 10px 1.5rem 14px;
            background: var(--nav-bg);
        }

        @media (max-width: 580px) {
            .mobile-btn  { display: flex; }
            .nav-desktop { display: none !important; }
        }

        /* ─────────────────────────────────────────
           PAGE WRAPPER
        ───────────────────────────────────────── */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            animation: fadeUp 0.28s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform: translateY(8px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ─────────────────────────────────────────
           SHARED CARD
        ───────────────────────────────────────── */
        .card {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: background 0.22s, border-color 0.22s;
        }

        /* ─────────────────────────────────────────
           TOOLTIP
        ───────────────────────────────────────── */
        [data-tip] { position: relative; }
        [data-tip]::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%) translateY(3px);
            background: var(--text-primary);
            color: var(--bg-elevated);
            font-size: 0.7rem;
            font-weight: 500;
            white-space: nowrap;
            padding: 4px 8px;
            border-radius: 5px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.16s, transform 0.16s;
            z-index: 9999;
        }
        [data-tip]:hover::after { opacity:1; transform: translateX(-50%) translateY(0); }

        /* ─────────────────────────────────────────
           TOAST
        ───────────────────────────────────────── */
        .fw-toast {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: var(--radius-lg);
            max-width: 360px;
            width: 100%;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            animation: toastSlide 0.3s cubic-bezier(0.34,1.4,0.64,1) both;
        }
        @keyframes toastSlide {
            from { opacity:0; transform: translateX(24px); }
            to   { opacity:1; transform: translateX(0); }
        }
        .fw-toast-icon { flex-shrink: 0; margin-top: 1px; }
        .fw-toast-msg  { font-size: 0.845rem; font-weight: 500; color: var(--text-primary); flex:1; line-height:1.5; }
        .fw-toast-close {
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 1.1rem; line-height: 1;
            padding: 0; transition: color 0.15s;
        }
        .fw-toast-close:hover { color: var(--text-primary); }
        .fw-toast-success { border-left: 3px solid var(--success); }
        .fw-toast-error   { border-left: 3px solid var(--danger); }
        .fw-toast-warning { border-left: 3px solid var(--warning); }
        .fw-toast-info    { border-left: 3px solid var(--accent); }
    </style>
</head>

<body>

{{-- Apply saved theme before render --}}
<script>
    (function(){
        var t = localStorage.getItem('fw-theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
</script>

{{-- ── Navbar ── --}}
<nav class="navbar">
    <div class="nav-inner">
        <a href="{{ route('widgets.index') }}" class="nav-logo">
            <div class="nav-logo-mark">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <circle cx="12" cy="8" r="4"/>
                    <path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </div>
            FaceWidgets
        </a>

        {{-- Desktop --}}
        <div class="nav-right nav-desktop">
            <a href="{{ route('widgets.index') }}" class="nav-link" data-tip="View all widgets">Dashboard</a>
            <!-- Domain link -->
            <a href="{{ route('domains.index') }}" class="nav-link" data-tip="Manage verified domains">Domains</a>

            <!-- <button class="theme-btn" onclick="toggleTheme()" data-tip="Toggle theme" aria-label="Toggle theme">
                <svg class="icon-moon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg class="icon-sun" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button> -->

            <a href="{{ route('widgets.create') }}" class="btn-cta" data-tip="Create a new widget">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                </svg>
                New Widget
            </a>
        </div>

        {{-- Mobile controls --}}
        <div style="display:flex;gap:6px;align-items:center;">
            <button class="theme-btn" onclick="toggleTheme()" aria-label="Toggle theme" style="display:flex;">
                <svg class="icon-moon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg class="icon-sun" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>
            <button class="mobile-btn" onclick="toggleMobileNav()" aria-label="Menu">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="mobile-nav" id="mobileNav">
        <a href="{{ route('widgets.index') }}" class="nav-link">Dashboard</a>

        <!-- domain link -->
         <a href="{{ route('domains.index') }}" class="nav-link">Domains</a>

         
        <a href="{{ route('widgets.create') }}" class="btn-cta" style="justify-content:center;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
            New Widget
        </a>
    </div>




</nav>

{{-- ── Content ── --}}
<main class="page-wrapper">
    @yield('content')
</main>


{{-- ── Toast (logic UNCHANGED) ── --}}
@if(session('success') || session('error') || session('warning') || session('info'))
@php
    $toastMsg  = session('success') ?? session('error') ?? session('warning') ?? session('info');
    $toastType = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : 'info'));
    $toastColors = [
        'success' => 'bg-green-50 border-green-400 text-green-800',
        'error'   => 'bg-red-50 border-red-400 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
        'info'    => 'bg-blue-50 border-blue-400 text-blue-800',
    ];
    $toastIcons = [
        'success' => '<svg class="fw-toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--success)"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        'error'   => '<svg class="fw-toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--danger)"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        'warning' => '<svg class="fw-toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--warning)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/></svg>',
        'info'    => '<svg class="fw-toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/></svg>',
    ];
@endphp
<div id="toastNotification" class="fw-toast fw-toast-{{ $toastType }}" role="alert">
    {!! $toastIcons[$toastType] !!}
    <p class="fw-toast-msg">{{ $toastMsg }}</p>
    <button onclick="dismissToast()" class="fw-toast-close" aria-label="Dismiss">&times;</button>
</div>
<script>
    var toastTimer = setTimeout(dismissToast, 4000);
    function dismissToast() {
        clearTimeout(toastTimer);
        var t = document.getElementById('toastNotification');
        if (!t) return;
        t.style.opacity   = '0';
        t.style.transform = 'translateX(20px)';
        t.style.transition = 'opacity .28s, transform .28s';
        setTimeout(function(){ t.remove(); }, 300);
    }
</script>
@endif

<script>
    function toggleTheme() {
        var cur  = document.documentElement.getAttribute('data-theme');
        var next = cur === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('fw-theme', next);
    }
    function toggleMobileNav() {
        document.getElementById('mobileNav').classList.toggle('open');
    }
</script>

</body>
</html>