<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Brother Outdoor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #2C1A00; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 20px; text-align: center; border-bottom: 1px solid #4a3000; flex-shrink: 0; }
        .sidebar-logo img { width: 100px; mix-blend-mode: lighten; }
        .sidebar-logo p { font-size: 11px; color: #F5A623; margin-top: 5px; }
        .sidebar-menu { padding: 15px 0; overflow-y: auto; flex: 1; }
        .sidebar-menu a { display: block; padding: 12px 20px; color: #ccc; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #F5A623; color: #2C1A00; font-weight: bold; }
        .sidebar-menu .menu-title { padding: 10px 20px; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .main { margin-left: 250px; width: calc(100% - 250px); min-height: 100vh; overflow-y: auto; }
        .topbar { background: white; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .topbar h1 { font-size: 18px; color: #2C1A00; }
        .topbar .user-info { font-size: 14px; color: #555; }
        .topbar .user-info span { color: #F5A623; font-weight: bold; }
        .content { padding: 25px; }
        .section { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .section h2 { font-size: 15px; color: #2C1A00; margin-bottom: 15px; border-left: 4px solid #F5A623; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #2C1A00; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover td { background: #fff8f0; }
        .btn { padding: 7px 14px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
        .btn-primary { background: #F5A623; color: #2C1A00; font-weight: bold; }
        .btn-danger { background: #c00; color: white; }
        .btn-warning { background: #f0ad4e; color: white; }
        .btn-info { background: #0dcaf0; color: white; }
        .btn-success { background: #198754; color: white; }
        .btn-logout { padding: 8px 16px; background: #c00; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .btn-logout:hover { background: #a00; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; }
        .alert-success { background: #d1e7dd; color: #0f5132; }
        .alert-error { background: #f8d7da; color: #842029; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full { grid-column: span 2; }
        label { font-size: 13px; color: #333; margin-bottom: 4px; }
        input[type="text"], input[type="number"], input[type="password"], input[type="date"], select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; font-size: 13px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #F5A623; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-admin { background: #cfe2ff; color: #084298; }
        .badge-owner { background: #e2d9f3; color: #6f42c1; }
        .badge-baik { background: #d1e7dd; color: #0f5132; }
        .badge-rusak { background: #f8d7da; color: #842029; }
        .badge-perbaikan { background: #fff3cd; color: #856404; }
        .badge-dipinjam { background: #fff3cd; color: #856404; }
        .badge-dikembalikan { background: #d1e7dd; color: #0f5132; }
        .badge-terlambat { background: #f8d7da; color: #842029; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: white; border-radius: 10px; padding: 25px; width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; }
        .modal h3 { color: #2C1A00; margin-bottom: 15px; border-left: 4px solid #F5A623; padding-left: 10px; }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px; }
        .alat-list { border: 1px solid #eee; border-radius: 5px; padding: 10px; }
        .alat-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px; }
        .alat-item:last-child { border-bottom: none; }
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #F5A623; }
        .card h3 { font-size: 13px; color: #888; margin-bottom: 8px; }
        .card .number { font-size: 32px; font-weight: bold; color: #2C1A00; }
        .card .icon { font-size: 30px; float: right; margin-top: -40px; }
        @yield('styles')
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo_brother.PNG') }}" alt="Brother Outdoor">
            <p>Sistem Inventori Rental</p>
        </div>
        <div class="sidebar-menu">
            <div class="menu-title">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">🏠 Dashboard</a>
            <div class="menu-title">Inventori</div>
            <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'active' : '' }}">📋 Data Kategori</a>
            <a href="{{ route('alat.index') }}" class="{{ request()->routeIs('alat.*') ? 'active' : '' }}">📦 Data Alat</a>
            @if(session('user')->role == 'admin')
            <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">👥 Data Pelanggan</a>
            @endif
            <div class="menu-title">Transaksi</div>
            <a href="{{ route('rental.index') }}" class="{{ request()->routeIs('rental.*') ? 'active' : '' }}">🤝 Data Rental</a>
            @if(session('user')->role == 'admin')
            <a href="{{ route('pengembalian.index') }}" class="{{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">✅ Pengembalian</a>
            @endif
            <div class="menu-title">Laporan</div>
            @if(in_array(session('user')->role, ['admin', 'owner']))
            <a href="{{ route('laporan.rental') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">📊 Laporan Rental</a>
            @endif
            @if(session('user')->role == 'admin')
            <div class="menu-title">Master</div>
            <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.*') ? 'active' : '' }}">👤 Data User</a>
            @endif
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>@yield('page-title')</h1>
            <div class="user-info">
                Halo, <span>{{ session('user')->nama }}</span> |
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>