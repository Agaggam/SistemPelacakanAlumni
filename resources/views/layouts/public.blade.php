<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cari Alumni — Sistem Pelacakan Alumni')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117;
            --surface: #1a1d2e;
            --card: #1e2235;
            --hover: #252840;
            --border: #2d3154;
            --accent: #6366f1;
            --accent-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --subtle: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* NAV */
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: var(--text);
        }
        .nav-brand .logo { width: 32px; height: 32px; background: linear-gradient(135deg, var(--accent), #8b5cf6); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .nav-brand .brand-name { font-size: 15px; font-weight: 700; }
        .nav-brand .brand-sub { font-size: 11px; color: var(--accent-light); font-weight: 500; }
        .nav-right { display: flex; align-items: center; gap: 12px; }

        /* MAIN */
        .main { max-width: 1200px; margin: 0 auto; padding: 28px 20px; }

        /* HERO */
        .hero { text-align: center; padding: 48px 20px 36px; }
        .hero h1 { font-size: 36px; font-weight: 800; background: linear-gradient(135deg, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 12px; }
        .hero p { color: var(--muted); font-size: 16px; max-width: 500px; margin: 0 auto; }

        /* SEARCH BAR */
        .search-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px; margin-bottom: 20px; }
        .search-row { display: flex; gap: 10px; margin-bottom: 14px; }
        .search-input { flex: 1; padding: 12px 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; color: var(--text); font-size: 15px; font-family: 'Inter', sans-serif; }
        .search-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .search-input::placeholder { color: var(--subtle); }
        .filter-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-select { padding: 8px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 13px; font-family: 'Inter', sans-serif; min-width: 130px; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: var(--accent); }
        .filter-select option { background: var(--surface); }
        .ml-auto { margin-left: auto; }

        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; white-space: nowrap; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #5558e8; }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--muted); }
        .btn-outline:hover { border-color: var(--accent); color: var(--accent-light); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-ghost { background: none; border: none; color: var(--muted); font-size: 13px; cursor: pointer; font-family: 'Inter', sans-serif; padding: 8px 12px; border-radius: 8px; }
        .btn-ghost:hover { background: var(--hover); color: var(--text); }

        /* TABS / SOURCE TOGGLE */
        .source-tabs { display: flex; gap: 6px; margin-bottom: 16px; }
        .source-tab { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); color: var(--muted); transition: all 0.2s; }
        .source-tab.active, .source-tab:hover { background: var(--accent); border-color: var(--accent); color: white; }

        /* CARDS / TABLE */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .section-title { font-size: 14px; font-weight: 700; color: var(--text); }
        .section-count { font-size: 12px; background: var(--hover); color: var(--muted); padding: 3px 10px; border-radius: 20px; }
        .results-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; margin-bottom: 24px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--subtle); border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid rgba(45,49,84,0.4); transition: background 0.15s; }
        tbody tr:hover { background: var(--hover); }
        tbody td { padding: 13px 16px; font-size: 13px; }
        tbody tr:last-child { border-bottom: none; }

        /* BADGES */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-success { background: rgba(16,185,129,0.15); color: #34d399; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .badge-info { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .badge-secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }
        .badge-local { background: rgba(99,102,241,0.15); color: #818cf8; }
        .badge-pddikti { background: rgba(16,185,129,0.1); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.2); }

        /* EMPTY STATE */
        .empty { text-align: center; padding: 50px 20px; color: var(--muted); }
        .empty-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-title { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .empty-sub { font-size: 13px; }

        /* ALERTS */
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 13px; }
        .alert-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #fbbf24; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }

        /* DIVIDER */
        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: var(--subtle); font-size: 12px; font-weight: 600; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* RESPONSIVE */
        @media (max-width: 640px) {
            .hero h1 { font-size: 24px; }
            .filter-row { flex-direction: column; }
            .filter-select { min-width: auto; width: 100%; }
            .search-row { flex-direction: column; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="{{ route('search') }}" class="nav-brand">
            <div class="logo">🎓</div>
            <div>
                <div class="brand-name">Sistem Pelacakan Alumni</div>
                <div class="brand-sub">PDDIKTI · Real-Time</div>
            </div>
        </a>
        <div class="nav-right">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">⚙️ Dashboard Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm">Keluar</button>
                </form>
                <span style="font-size:12px; color:var(--muted)">{{ auth()->user()->name }}</span>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">🔐 Login</a>
            @endauth
        </div>
    </nav>

    <main class="main">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171">❌ {{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
