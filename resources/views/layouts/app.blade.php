<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Pelacakan Alumni')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-primary: #0f1117;
            --bg-secondary: #1a1d2e;
            --bg-card: #1e2235;
            --bg-hover: #252840;
            --border: #2d3154;
            --accent: #6366f1;
            --accent-light: #818cf8;
            --accent-glow: rgba(99, 102, 241, 0.15);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --sidebar-width: 260px;
            --header-height: 64px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
        }
        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 20px 22px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand h1 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.3;
        }
        .sidebar-brand span {
            font-size: 11px;
            color: var(--accent-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sidebar-brand .logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .sidebar-nav { padding: 12px 12px; flex: 1; }
        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            padding: 8px 10px 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg-hover); color: var(--text-primary); }
        .nav-item.active { background: var(--accent-glow); color: var(--accent-light); }
        .nav-item .icon { font-size: 17px; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
        }
        .user-info {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 0;
        }
        .user-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700;
        }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: var(--text-muted); }
        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 8px 10px;
            background: none; border: 1px solid var(--border);
            border-radius: 7px; color: var(--text-secondary);
            font-size: 13px; cursor: pointer; margin-top: 8px;
            transition: all 0.2s; text-decoration: none; justify-content: center;
        }
        .logout-btn:hover { border-color: var(--danger); color: var(--danger); }
        .public-btn {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 8px 10px;
            background: none; border: 1px solid var(--border);
            border-radius: 7px; color: var(--text-secondary);
            font-size: 13px; cursor: pointer; margin-top: 8px;
            transition: all 0.2s; text-decoration: none; justify-content: center;
        }
        .public-btn:hover { border-color: var(--accent); color: var(--accent-light); }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            height: var(--header-height);
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 18px; font-weight: 700; }
        .topbar-subtitle { font-size: 13px; color: var(--text-muted); }
        .content { padding: 28px; flex: 1; }

        /* CARDS */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 20px;
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .card-title { font-size: 15px; font-weight: 600; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 50%;
            opacity: 0.08;
            transform: translate(20px, -20px);
        }
        .stat-card.total::before { background: var(--accent); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }
        .stat-card.secondary::before { background: var(--text-muted); }
        .stat-value { font-size: 32px; font-weight: 800; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: var(--text-secondary); }
        .stat-icon { font-size: 24px; margin-bottom: 10px; }

        /* BADGES */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .badge-success { background: rgba(16,185,129,0.15); color: #34d399; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .badge-danger { background: rgba(239,68,68,0.15); color: #f87171; }
        .badge-secondary { background: rgba(100,116,139,0.15); color: #94a3b8; }
        .badge-info { background: rgba(59,130,246,0.15); color: #60a5fa; }

        /* TABLES */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        tbody tr { border-bottom: 1px solid rgba(45,49,84,0.5); transition: background 0.15s; }
        tbody tr:hover { background: var(--bg-hover); }
        tbody td { padding: 13px 16px; font-size: 14px; }
        tbody tr:last-child { border-bottom: none; }

        /* BUTTONS */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #5558e8; box-shadow: 0 4px 15px rgba(99,102,241,0.35); }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { background: #059669; }
        .btn-warning { background: var(--warning); color: #1a1d2e; }
        .btn-warning:hover { background: #d97706; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border); }
        .btn-secondary:hover { color: var(--text-primary); border-color: var(--text-muted); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-secondary); }
        .btn-outline:hover { border-color: var(--accent); color: var(--accent-light); }

        /* FORMS */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 7px; color: var(--text-secondary); }
        .form-control {
            width: 100%; padding: 10px 14px;
            background: var(--bg-primary); border: 1px solid var(--border);
            border-radius: 8px; color: var(--text-primary); font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
        .form-control::placeholder { color: var(--text-muted); }
        select.form-control option { background: var(--bg-secondary); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

        /* ALERTS */
        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
            font-size: 14px; display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }
        .alert-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #fbbf24; }

        /* PAGINATION */
        .pagination { display: flex; gap: 6px; align-items: center; justify-content: center; margin-top: 20px; }
        .pagination a, .pagination span {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 8px;
            text-decoration: none; font-size: 13px; font-weight: 600;
            border: 1px solid var(--border); color: var(--text-secondary);
            transition: all 0.2s;
        }
        .pagination a:hover { border-color: var(--accent); color: var(--accent-light); }
        .pagination .active span { background: var(--accent); color: white; border-color: var(--accent); }
        .pagination .disabled span { opacity: 0.4; }

        /* MISC */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .text-muted { color: var(--text-muted); font-size: 13px; }
        .text-sm { font-size: 13px; }
        .font-mono { font-family: monospace; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* SEARCH BAR */
        .search-filters {
            display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
        }
        .search-filters .form-control { width: auto; min-width: 200px; }

        /* TIMELINE */
        .timeline { position: relative; padding-left: 24px; }
        .timeline::before {
            content: ''; position: absolute;
            left: 7px; top: 8px; bottom: 8px;
            width: 2px; background: var(--border);
        }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-dot {
            position: absolute; left: -22px; top: 4px;
            width: 12px; height: 12px; border-radius: 50%;
            background: var(--accent); border: 2px solid var(--bg-card);
        }
        .timeline-date { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .timeline-content {
            background: var(--bg-hover); border-radius: 8px; padding: 12px 14px;
            font-size: 13px;
        }
        .timeline-title { font-weight: 600; margin-bottom: 4px; }

        /* SCORE BAR */
        .score-bar { 
            height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; margin-top: 4px;
        }
        .score-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }

        /* CHIP */
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; background: var(--bg-hover);
            border-radius: 6px; font-size: 12px; color: var(--text-secondary);
        }

        /* MODAL */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 16px; padding: 28px; width: 480px; max-width: 95vw;
        }
        .modal-title { font-size: 17px; font-weight: 700; margin-bottom: 18px; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo-icon">🎓</div>
            <h1>Sistem Pelacakan Alumni</h1>
            <span>Dashboard Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">MENU UTAMA</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span> Dashboard
            </a>

            <div class="nav-section-label" style="margin-top:20px">🌐 PDDIKTI</div>
            <a href="{{ route('pddikti.search') }}" class="nav-item {{ request()->routeIs('pddikti.search') || request()->routeIs('pddikti.detail') ? 'active' : '' }}">
                <span class="icon">🔎</span> Cari di PDDIKTI
            </a>

            <div class="nav-section-label" style="margin-top:20px">TRACKING LOKAL</div>
            <a href="{{ route('alumni.index') }}" class="nav-item {{ request()->routeIs('alumni.index') || request()->routeIs('alumni.show') || request()->routeIs('alumni.edit') ? 'active' : '' }}">
                <span class="icon">👥</span> Data Alumni Tersimpan
            </a>
            <a href="{{ route('alumni.create') }}" class="nav-item {{ request()->routeIs('alumni.create') ? 'active' : '' }}">
                <span class="icon">➕</span> Tambah Alumni Manual
            </a>
        </nav>
        <div class="sidebar-footer" style="padding: 15px 20px; border-top: 1px solid var(--border);">
            <div class="user-info" style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 10px; background: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">A</div>
                <div>
                    <div class="user-name" style="font-size: 13px; font-weight: 700; color: #f1f5f9;">Admin Pelacakan</div>
                    <div class="user-role" style="font-size: 11px; color: #64748b;">Administrator</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ url('/') }}" class="public-btn" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: 12px; font-weight: 600;">
                    🌐 Publik
                </a>
                <form action="{{ route('logout') }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="logout-btn" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; color: #94a3b8; background: transparent; font-size: 12px; font-weight: 600; cursor: pointer;">
                        🚪 Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <header class="topbar">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-subtitle">@yield('page-subtitle', 'Sistem Pelacakan Alumni')</div>
            </div>
            <div class="flex items-center gap-3">
                <div style="background: #1e2235; border: 1px solid var(--border); padding: 8px 16px; border-radius: 10px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #f1f5f9;">
                    <span style="opacity: 0.7;">🕒</span> {{ now()->format('d M Y, H:i') }}
                </div>
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <div>
                        @foreach($errors->all() as $error)
                            <div>❌ {{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
