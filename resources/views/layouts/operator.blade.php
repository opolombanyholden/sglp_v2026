<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Opérateur') - DGELP</title>

    <!-- ✅ Bootstrap 4.6.2 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        /* =========================================================
           Palette professionnelle — desaturée, douce
           ========================================================= */
        :root {
            --sidebar-width: 270px;
            --sidebar-collapsed: 80px;
            --header-height: 64px;

            --slate-50:  #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;

            --accent:        #2f7a4f;
            --accent-soft:   rgba(47, 122, 79, 0.12);
            --accent-hover:  #265e3d;

            --primary-gradient: var(--accent);
            --sidebar-bg:    #1e293b;
            --sidebar-hover: rgba(255, 255, 255, 0.05);
            --text-muted:    #94a3b8;
            --border-color:  rgba(255, 255, 255, 0.06);

            --radius-sm: 6px;
            --radius:    8px;
            --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        * { box-sizing: border-box; }

        body {
            overflow-x: hidden;
            background: var(--slate-50);
            color: var(--slate-700);
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Wrapper principal */
        .operator-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar — fond sobre, ombre légère */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease;
            border-right: 1px solid rgba(255, 255, 255, 0.04);
        }

        /* Header sidebar */
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-img {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .logo-text {
            font-size: 1rem;
            font-weight: 600;
            color: #f1f5f9;
            letter-spacing: -0.01em;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            display: none;
        }

        /* Profil utilisateur */
        .sidebar-profile {
            padding: 0.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin: 0.75rem;
            border-radius: var(--radius);
        }

        .profile-avatar { position: relative; }

        .avatar-circle {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid var(--sidebar-bg);
            border-radius: 50%;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            color: white;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-role {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .nav-section { margin-bottom: 1.25rem; }

        .nav-section-title {
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 0 1rem;
            margin: 0 0 0.375rem;
            display: block;
        }

        .nav-item-custom {
            display: flex;
            align-items: center;
            padding: 0.45rem 0.625rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
            position: relative;
            margin: 1px 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.82rem;
        }

        .nav-item-custom:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
            text-decoration: none;
            transform: none;
        }

        .nav-item-custom.active {
            background: var(--accent-soft);
            color: #f1f5f9;
        }

        .nav-item-custom.active::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: var(--accent);
            border-radius: 2px;
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: var(--text-muted);
            border-radius: var(--radius-sm);
            margin-right: 10px;
            font-size: 0.82rem;
            transition: color .15s ease;
        }

        .nav-item-custom:hover .nav-icon,
        .nav-item-custom.active .nav-icon {
            color: #f1f5f9;
        }

        .nav-text {
            flex: 1;
            font-size: 0.82rem;
            font-weight: 400;
        }

        .nav-badge {
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-muted);
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 0.68rem;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }

        .nav-badge.warning { background: rgba(212, 167, 44, 0.15); color: #fcd34d; }
        .nav-badge.danger  { background: rgba(140, 67, 81, 0.18); color: #fca5a5; }
        .nav-badge.info    { background: rgba(59, 130, 246, 0.15); color: #93c5fd; }
        .nav-badge.success { background: rgba(47, 122, 79, 0.18); color: #86efac; }

        /* Sous-menu nouvelle organisation */
        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-submenu.show {
            max-height: 300px;
        }

        .nav-submenu-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .nav-submenu-item:hover {
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Footer sidebar */
        .sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
        }

        .sidebar-footer-item {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.45rem 0.5rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.78rem;
            transition: background-color .15s, color .15s;
            cursor: pointer;
        }

        .sidebar-footer-item:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
            transform: none;
            text-decoration: none;
        }

        .sidebar-footer-item.logout:hover {
            background: rgba(140, 67, 81, 0.18);
            color: #fca5a5;
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        /* Header content */
        .content-header {
            background: #fff;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--slate-200);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left { display: flex; align-items: center; gap: 1rem; }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--slate-600);
            cursor: pointer;
            padding: 0.5rem;
            display: none;
        }

        .page-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--slate-800);
            letter-spacing: -0.01em;
        }

        .header-right { display: flex; align-items: center; gap: 1rem; }

        /* Recherche header — soft */
        .header-search {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 0.4rem 0.875rem;
            min-width: 280px;
            transition: border-color .15s, background-color .15s;
        }

        .header-search:focus-within {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .header-search i { color: var(--slate-400); margin-right: 0.5rem; font-size: 0.82rem; }

        .search-input {
            background: none;
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.82rem;
            color: var(--slate-700);
        }
        .search-input::placeholder { color: var(--slate-400); }

        /* Actions header */
        .header-actions { display: flex; align-items: center; gap: 0.5rem; }

        .action-btn {
            position: relative;
            background: #fff;
            border: 1px solid var(--slate-200);
            width: 34px;
            height: 34px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color .15s, background-color .15s, color .15s;
            color: var(--slate-500);
            font-size: 0.85rem;
        }

        .action-btn:hover {
            background: var(--slate-50);
            border-color: var(--slate-300);
            color: var(--slate-700);
            transform: none;
        }

        .action-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: var(--accent);
            color: #fff;
            font-size: 0.62rem;
            padding: 1px 5px;
            border-radius: 9px;
            font-weight: 600;
            min-width: 16px;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        /* User menu */
        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #fff;
            border: 1px solid var(--slate-200);
            cursor: pointer;
            padding: 0.375rem 0.625rem;
            border-radius: var(--radius);
            transition: border-color .15s, background-color .15s;
        }

        .user-menu-toggle:hover {
            background: var(--slate-50);
            border-color: var(--slate-300);
        }

        .user-avatar-small {
            width: 28px;
            height: 28px;
            background: var(--accent);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.72rem;
        }

        /* Content body */
        .content-body {
            padding: 0;
            flex: 1;
            overflow-y: auto;
        }

        /* Breadcrumb custom */
        .breadcrumb-custom {
            background: transparent;
            padding: 0.875rem 1.5rem;
            margin-bottom: 0;
            font-size: 0.82rem;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: var(--slate-500);
            text-decoration: none;
            transition: color .15s;
        }

        .breadcrumb-custom .breadcrumb-item a:hover { color: var(--accent); }

        /* =========================================================
           Composants Bootstrap — versions douces
           ========================================================= */
        .content-body .card {
            border: 1px solid var(--slate-200);
            box-shadow: var(--shadow-xs);
            border-radius: var(--radius);
        }
        .content-body .card-header {
            background: #fff;
            border-bottom: 1px solid var(--slate-200);
            font-weight: 500;
            color: var(--slate-700);
        }
        .content-body .card-header.bg-primary,
        .content-body .card-header[style*="009e3f"],
        .content-body .card-header[style*="667eea"] {
            background: var(--slate-50) !important;
            color: var(--slate-700) !important;
            border-bottom-color: var(--slate-200);
        }

        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }
        .btn-outline-primary { color: var(--accent); border-color: var(--accent); }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .alert {
            border: 1px solid transparent;
            border-radius: var(--radius);
            border-left: 3px solid;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        .alert-success {
            background: rgba(47, 122, 79, 0.06);
            border-color: rgba(47, 122, 79, 0.18);
            border-left-color: var(--accent);
            color: #1e5435;
        }
        .alert-danger {
            background: rgba(140, 67, 81, 0.06);
            border-color: rgba(140, 67, 81, 0.18);
            border-left-color: #8c4351;
            color: #6b3340;
        }
        .alert-warning {
            background: rgba(212, 167, 44, 0.07);
            border-color: rgba(212, 167, 44, 0.20);
            border-left-color: #d4a72c;
            color: #7a5a17;
        }
        .alert-info {
            background: rgba(31, 58, 95, 0.06);
            border-color: rgba(31, 58, 95, 0.18);
            border-left-color: #1f3a5f;
            color: #1f3a5f;
        }

        .badge {
            font-weight: 500;
            font-size: 0.72rem;
            padding: 0.3em 0.6em;
            border-radius: 6px;
            letter-spacing: 0;
        }
        .badge.bg-success, .badge.badge-success {
            background-color: rgba(47, 122, 79, 0.12) !important;
            color: #1e5435 !important;
        }
        .badge.bg-danger, .badge.badge-danger {
            background-color: rgba(140, 67, 81, 0.12) !important;
            color: #6b3340 !important;
        }
        .badge.bg-warning, .badge.badge-warning {
            background-color: rgba(212, 167, 44, 0.15) !important;
            color: #7a5a17 !important;
        }
        .badge.bg-info, .badge.badge-info {
            background-color: rgba(31, 58, 95, 0.10) !important;
            color: #1f3a5f !important;
        }
        .badge.bg-primary, .badge.badge-primary {
            background-color: var(--accent-soft) !important;
            color: var(--accent-hover) !important;
        }
        .badge.bg-secondary, .badge.badge-secondary {
            background-color: var(--slate-100) !important;
            color: var(--slate-600) !important;
        }
        .badge.bg-light {
            background-color: var(--slate-100) !important;
            color: var(--slate-700) !important;
        }

        .table { color: var(--slate-700); }
        .table > :not(caption) > * > * { border-bottom-color: var(--slate-200); }
        .table thead th {
            font-weight: 500;
            color: var(--slate-500);
            font-size: 0.78rem;
            text-transform: none;
            letter-spacing: 0;
            background: var(--slate-50);
            border-bottom: 1px solid var(--slate-200);
        }
        .table-hover tbody tr:hover { background-color: var(--slate-50); }

        .form-control, .form-select {
            border-color: var(--slate-200);
            color: var(--slate-700);
            font-size: 0.875rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        /* Overlay mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            transition: opacity 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: block;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .header-search {
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .content-body {
                padding: 1rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .nav-item-custom {
            animation: slideIn 0.3s ease-out;
        }

        /* Styles pour les alertes */
        .alert {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        /* Ancien CSS navbar-custom pour éviter les conflits */
        .navbar-custom {
            display: none;
        }

        /* Correction pour le dropdown Bootstrap */
        .dropdown-menu-custom {
            min-width: 300px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 0.5rem 0;
        }

        .dropdown-item-custom {
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .dropdown-item-custom:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
    </style>

    @stack('styles')

    <!-- Responsive opérateur (mobile & tablette) + shim compat Bootstrap 5 — chargé en dernier -->
    <link rel="stylesheet" href="{{ asset('css/operator-responsive.css') }}">
</head>

<body>
    <div class="operator-wrapper">
        <!-- Sidebar moderne -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo et toggle -->
            <div class="sidebar-header">
                <a href="{{ route('operator.dashboard') }}" class="sidebar-logo">
                    <div class="logo-img">
                        <i class="fas fa-building"></i>
                    </div>
                    <span class="logo-text">DGELP</span>
                </a>
                <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Profil utilisateur -->
            <div class="sidebar-profile">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="status-indicator"></span>
                </div>
                <div class="profile-info">
                    <h6 class="profile-name">{{ auth()->user()->name }}</h6>
                    <span class="profile-role">
                        <i class="fas fa-user-tag mr-1"></i>
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">PRINCIPAL</span>

                    <a href="{{ route('operator.dashboard') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Tableau de bord</span>
                    </a>

                    <a href="{{ route('operator.dossiers.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.dossiers.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <span class="nav-text">Mes dossiers</span>
                        <span class="nav-badge">0</span>
                    </a>

                    <a href="{{ route('operator.dossiers.create') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.dossiers.create') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="nav-text">Nouvelle organisation</span>
                    </a>

                    @php
                        $draftsCount = \App\Models\OrganizationDraft::where('user_id', auth()->id())
                            ->where('expires_at', '>', now())
                            ->count();
                    @endphp
                    <a href="{{ route('operator.organisations.drafts.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.organisations.drafts.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-pencil-alt"></i>
                        </div>
                        <span class="nav-text">Brouillons</span>
                        @if($draftsCount > 0)
                            <span class="nav-badge" style="background: #FFD700; color: #002B7F;">{{ $draftsCount }}</span>
                        @endif
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">GESTION</span>

                    <a href="{{ route('operator.members.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.members.*') || request()->routeIs('operator.adherents.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Adhérents</span>
                    </a>

                    <a href="{{ route('operator.documents.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.files.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="nav-text">Documents</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">OBLIGATIONS</span>

                    <a href="{{ route('operator.declarations.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.declarations.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <span class="nav-text">Déclarations annuelles</span>
                    </a>

                    <a href="{{ route('operator.reports.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.reports.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="nav-text">Rapports d'activité</span>
                    </a>

                    <a href="{{ route('operator.grants.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.grants.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <span class="nav-text">Subventions</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">COMMUNICATION</span>

                    <a href="{{ route('operator.messages.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.messages.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="nav-text">Messages</span>
                        <span class="nav-badge danger">0</span>
                    </a>

                    <a href="{{ route('operator.notifications.index') }}"
                        class="nav-item-custom {{ request()->routeIs('operator.notifications.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <span class="nav-text">Notifications</span>
                        <span class="nav-badge info">0</span>
                    </a>

                    <a href="{{ route('calendrier') }}"
                        class="nav-item-custom {{ request()->routeIs('calendrier') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="nav-text">Calendrier</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">RESSOURCES</span>

                    <a href="{{ route('guides') }}" class="nav-item-custom" target="_blank">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">Guides pratiques</span>
                        <i class="fas fa-external-link-alt ml-auto"></i>
                    </a>

                    <a href="{{ route('documents.index') }}" class="nav-item-custom" target="_blank">
                        <div class="nav-icon">
                            <i class="fas fa-file-download"></i>
                        </div>
                        <span class="nav-text">Documents types</span>
                        <i class="fas fa-external-link-alt ml-auto"></i>
                    </a>
                </div>
            </nav>

            <!-- Actions du bas -->
            <div class="sidebar-footer">
                <a href="{{ route('operator.profile.index') }}" class="sidebar-footer-item">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline flex-fill">
                    @csrf
                    <button type="submit" class="sidebar-footer-item logout w-100">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <!-- Header minimaliste -->
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle d-md-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Espace Opérateur')</h1>
                </div>

                <div class="header-right">
                    <!-- Recherche -->
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher..." class="search-input">
                    </div>

                    <!-- Actions rapides -->
                    <div class="header-actions">
                        <div class="dropdown">
                            <button class="action-btn" data-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="action-badge">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom"
                                    href="{{ route('operator.notifications.index') }}">
                                    <small class="text-muted">Aucune nouvelle notification</small>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom text-center"
                                    href="{{ route('operator.notifications.index') }}">
                                    Voir toutes les notifications
                                </a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="action-btn" data-toggle="dropdown">
                                <i class="fas fa-envelope"></i>
                                <span class="action-badge">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <h6 class="dropdown-header">Messages</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom"
                                    href="{{ route('operator.messages.index') }}">
                                    <small class="text-muted">Aucun nouveau message</small>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom text-center"
                                    href="{{ route('operator.messages.index') }}">
                                    Voir tous les messages
                                </a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="user-menu-toggle" data-toggle="dropdown">
                                <div class="user-avatar-small">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom"
                                        href="{{ route('operator.profile.index') }}">
                                        <i class="fas fa-user mr-2"></i> Mon profil
                                    </a></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="{{ route('home') }}">
                                        <i class="fas fa-home mr-2"></i> Retour au site
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item dropdown-item-custom">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenu de la page -->
            <div class="content-body">
                <!-- Messages flash -->
                <div class="px-4 pt-3">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-dismiss="alert"></button>
                        </div>
                    @endif
                </div>

                <!-- Actions de page -->
                @hasSection('page-actions')
                    <div class="d-flex justify-content-end mb-3 px-4">
                        @yield('page-actions')
                    </div>
                @endif

                <!-- Contenu de la page -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Overlay mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Conteneur pour les alertes dynamiques -->
    <div id="alert-container"></div>

    <!-- Scripts -->
    <!-- ✅ jQuery (requis pour Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous"></script>

    <!-- ✅ Bootstrap 4.6.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/other-suggestion.js') }}"></script>
    {{-- Module commun upload - NOUVEAU --}}
    <!-- <script src="{{ asset('js/file-upload-common_.js') }}"></script> -->


    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const chevron = event.currentTarget.querySelector('.fa-chevron-down');

            submenu.classList.toggle('show');
            if (chevron) {
                chevron.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)';
            }
        }

        // Fermer le sidebar en cliquant sur un lien (mobile)
        document.querySelectorAll('.nav-item-custom, .nav-submenu-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768 && !item.querySelector('.fa-chevron-down')) {
                    toggleSidebar();
                }
            });
        });

        // Ouvrir automatiquement le sous-menu si une page enfant est active
        document.addEventListener('DOMContentLoaded', function () {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/dossiers/create/')) {
                document.getElementById('newOrgMenu').classList.add('show');
                const chevron = document.querySelector('[onclick*="newOrgMenu"] .fa-chevron-down');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        });
    </script>

    @stack('scripts')

    <!-- Formatage automatique des champs à la saisie (NIP, téléphone, nom/sigle, email) -->
    <script src="{{ asset('js/input-formatter.js') }}"></script>
</body>

</html>