<!DOCTYPE html>
<html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Administration') - DGELP</title>

        <!-- Bootstrap 4 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">

        <!-- Google Fonts (SRI non applicable — contenu dynamique) -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet" crossorigin="anonymous">

        <style>
            /* =========================================================
               PALETTE PROFESSIONNELLE — desaturée, soft, contraste maîtrisé
               ========================================================= */
            :root {
                /* Couleurs originales conservées pour rétro-compat */
                --gabon-green:  #2f7a4f;       /* vert sourd (au lieu de #009e3f flashy) */
                --gabon-yellow: #d4a72c;       /* jaune ocre (au lieu de #ffcd00) */
                --gabon-blue:   #1f3a5f;       /* bleu profond doux */
                --gabon-red:    #8c4351;       /* bordeaux feutré */

                /* Système de couleurs neutres (slate) */
                --slate-50:  #f8fafc;
                --slate-100: #f1f5f9;
                --slate-200: #e2e8f0;
                --slate-300: #cbd5e1;
                --slate-400: #94a3b8;
                --slate-500: #64748b;
                --slate-600: #475569;
                --slate-700: #334155;
                --slate-800: #1e293b;
                --slate-900: #0f172a;

                /* Accent principal — vert sourd, lisible mais discret */
                --accent:        #2f7a4f;
                --accent-soft:   rgba(47, 122, 79, 0.12);
                --accent-hover:  #265e3d;

                /* Sidebar — bleu nuit doux plutôt que navy saturé */
                --sidebar-bg:    #1e293b;       /* slate-800 */
                --sidebar-text:  #cbd5e1;       /* slate-300 */
                --sidebar-muted: #94a3b8;       /* slate-400 */
                --sidebar-hover: rgba(255, 255, 255, 0.05);

                --sidebar-width: 270px;
                --header-height: 64px;

                --radius-sm: 6px;
                --radius:    8px;
                --radius-lg: 10px;

                --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
                --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
            }

            /* Reset et base */
            * { box-sizing: border-box; }

            body {
                margin: 0;
                font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
                background: var(--slate-50);
                color: var(--slate-700);
                font-size: 14px;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            /* Layout principal */
            .admin-layout {
                display: flex;
                min-height: 100vh;
            }

            /* Sidebar — fond slate-800 sobre */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: var(--sidebar-width);
                height: 100vh;
                background: var(--sidebar-bg);
                overflow-y: auto;
                z-index: 1000;
                border-right: 1px solid rgba(255, 255, 255, 0.04);
            }

            /* Header sidebar avec logos */
            .sidebar-header {
                padding: 1.25rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            }

            .logo-section {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .main-logo {
                background: var(--accent);
                color: #fff;
                width: 40px;
                height: 40px;
                border-radius: var(--radius);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .logo-text-group {
                display: flex;
                flex-direction: column;
            }

            .sidebar-title {
                color: #f1f5f9;
                font-size: 1rem;
                font-weight: 600;
                margin: 0;
                line-height: 1.2;
                letter-spacing: -0.01em;
            }

            .sidebar-subtitle {
                color: var(--sidebar-muted);
                font-size: 0.78rem;
                font-weight: 500;
                margin: 0;
                line-height: 1.2;
                letter-spacing: 0.02em;
            }

            .settings-icon {
                background: transparent;
                color: var(--sidebar-muted);
                width: 32px;
                height: 32px;
                border-radius: var(--radius-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                border: 1px solid rgba(255, 255, 255, 0.06);
                transition: all .15s ease;
            }

            /* Profil utilisateur — bloc sobre */
            .sidebar-profile {
                padding: 0.75rem;
                margin: 1rem 1rem 1.25rem 1rem;
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: var(--radius);
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .profile-avatar {
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
                position: relative;
            }

            .profile-avatar::after {
                content: '';
                position: absolute;
                bottom: 0;
                right: 0;
                width: 10px;
                height: 10px;
                background: #4ade80;
                border: 2px solid var(--sidebar-bg);
                border-radius: 50%;
            }

            .profile-info h6 {
                color: #f1f5f9;
                margin: 0;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .profile-info small {
                color: var(--sidebar-muted);
                font-size: 0.72rem;
                font-weight: 400;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            /* Navigation — sections sobres */
            .nav-section {
                margin-bottom: 0.125rem;
            }

            .nav-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.55rem 0.75rem;
                margin: 0 0.75rem 0.125rem 0.75rem;
                border-radius: var(--radius-sm);
                background: transparent;
                cursor: pointer;
                transition: background-color .15s ease;
                border: none;
            }

            .nav-section-header:hover {
                background: var(--sidebar-hover);
            }

            .nav-section-header.active {
                background: rgba(255, 255, 255, 0.06);
            }

            .nav-section-title-content {
                display: flex;
                align-items: center;
                color: var(--sidebar-text);
                gap: 10px;
            }

            .nav-section-icon {
                width: 22px;
                height: 22px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
                color: var(--sidebar-muted);
                border-radius: var(--radius-sm);
                font-size: 0.85rem;
                transition: color .15s ease;
            }

            .nav-section-header.active .nav-section-icon,
            .nav-section-header:hover .nav-section-icon {
                color: #f1f5f9;
            }

            .nav-section-title {
                font-size: 0.78rem;
                font-weight: 500;
                text-transform: none;
                letter-spacing: 0;
                color: var(--sidebar-text);
            }

            .nav-section-toggle {
                color: var(--sidebar-muted);
                font-size: 0.7rem;
                transition: transform .2s ease;
            }

            .nav-section-header.active .nav-section-toggle {
                transform: rotate(180deg);
                color: var(--sidebar-text);
            }

            .nav-section-badge {
                background: rgba(255, 255, 255, 0.07);
                color: var(--sidebar-muted);
                padding: 1px 7px;
                border-radius: 10px;
                font-size: 0.68rem;
                font-weight: 500;
                margin-left: 8px;
                font-variant-numeric: tabular-nums;
            }

            /* Sous-sections (accordéon) */
            .nav-subsection {
                max-height: 0;
                overflow: hidden;
                transition: max-height .3s ease, padding .2s ease;
                padding: 0 0.5rem;
                margin-left: 1.75rem;
                border-left: 1px solid rgba(255, 255, 255, 0.05);
            }

            .nav-subsection.active,
            .nav-subsection.open {
                max-height: 1200px;
                padding: 0.25rem 0.5rem 0.5rem;
            }

            .nav-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .nav-item {
                position: relative;
                margin-bottom: 1px;
            }

            /* Liens de navigation — sobres et compacts */
            .nav-link-custom {
                display: flex;
                align-items: center;
                padding: 0.45rem 0.6rem;
                color: var(--sidebar-text);
                text-decoration: none;
                transition: background-color .15s ease, color .15s ease;
                position: relative;
                border-radius: var(--radius-sm);
                border: none;
                font-size: 0.82rem;
            }

            .nav-link-custom:hover {
                color: #f1f5f9;
                background: var(--sidebar-hover);
                text-decoration: none;
                transform: none;
            }

            .nav-link-custom.active {
                background: var(--accent-soft);
                color: #f1f5f9;
                box-shadow: none;
            }

            .nav-link-custom.active::before {
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
                margin-right: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
                color: var(--sidebar-muted);
                border-radius: var(--radius-sm);
                font-size: 0.78rem;
                transition: color .15s ease;
            }

            .nav-link-custom:hover .nav-icon,
            .nav-link-custom.active .nav-icon {
                color: #f1f5f9;
                transform: none;
            }

            .nav-text {
                flex: 1;
                font-size: 0.82rem;
                font-weight: 400;
            }

            .nav-badge {
                background: rgba(255, 255, 255, 0.07);
                color: var(--sidebar-muted);
                padding: 1px 6px;
                border-radius: 10px;
                font-size: 0.68rem;
                font-weight: 500;
                min-width: 18px;
                text-align: center;
                font-variant-numeric: tabular-nums;
            }

            /* Variantes de badges atténuées (couleurs juste suggérées) */
            .nav-badge.warning { background: rgba(212, 167, 44, 0.15); color: #fcd34d; }
            .nav-badge.info    { background: rgba(59, 130, 246, 0.15); color: #93c5fd; }
            .nav-badge.success { background: rgba(47, 122, 79, 0.18); color: #86efac; }
            .nav-badge.users   { background: rgba(140, 67, 81, 0.18); color: #fca5a5; }
            .nav-badge.roles   { background: rgba(31, 58, 95, 0.30); color: #93c5fd; }

            .nav-badge.permissions {
                background: #ff6b35;
                color: white;
            }

            /* ✅ GÉOLOCALISATION - SOUS-ACCORDÉONS SPÉCIALISÉS */
            .geo-subsection {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease, padding 0.3s ease;
                padding: 0;
                border-left: 2px solid transparent;
                margin-left: 1rem;
            }

            .geo-subsection.active {
                max-height: 500px;
                padding: 0.25rem 0;
                border-left-color: rgba(255, 205, 0, 0.3);
            }

            .geo-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.6rem 0.75rem;
                margin: 0.25rem 0;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.05);
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid transparent;
            }

            .geo-section-header:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 205, 0, 0.2);
            }

            .geo-section-header.expanded {
                background: rgba(0, 158, 63, 0.2);
                border-color: var(--gabon-yellow);
            }

            .geo-header-content {
                display: flex;
                align-items: center;
                color: rgba(255, 255, 255, 0.9);
            }

            .geo-header-icon {
                width: 30px;
                height: 30px;
                margin-right: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 6px;
                font-size: 0.9rem;
            }

            .geo-header-text {
                font-size: 0.8rem;
                font-weight: 600;
            }

            .geo-level-indicator {
                font-size: 0.6rem;
                color: var(--gabon-yellow);
                background: rgba(255, 205, 0, 0.2);
                padding: 2px 6px;
                border-radius: 8px;
                margin-left: 6px;
            }

            .geo-toggle-icon {
                color: rgba(255, 255, 255, 0.6);
                font-size: 0.7rem;
                transition: transform 0.3s ease;
            }

            .geo-section-header.expanded .geo-toggle-icon {
                transform: rotate(180deg);
            }

            /* Contenu principal */
            .main-content {
                margin-left: var(--sidebar-width);
                flex: 1;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* Header principal */
            .main-header {
                background: #fff;
                height: var(--header-height);
                border-bottom: 1px solid var(--slate-200);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 1.5rem;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .header-left { display: flex; align-items: center; gap: 1rem; }

            .header-title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 600;
                color: var(--slate-800);
                display: flex;
                align-items: center;
                gap: 0.5rem;
                letter-spacing: -0.01em;
            }

            .header-right { display: flex; align-items: center; gap: 0.75rem; }

            /* Barre de recherche — discrète */
            .search-container { position: relative; width: 280px; }

            .search-input {
                width: 100%;
                padding: 0.45rem 0.875rem 0.45rem 2.25rem;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius);
                font-size: 0.82rem;
                background: var(--slate-50);
                transition: border-color .15s, background-color .15s;
                color: var(--slate-700);
            }

            .search-input::placeholder { color: var(--slate-400); }

            .search-input:focus {
                outline: none;
                border-color: var(--accent);
                box-shadow: 0 0 0 3px var(--accent-soft);
                background: #fff;
            }

            .search-icon {
                position: absolute;
                left: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--slate-400);
                font-size: 0.82rem;
            }

            .header-actions { display: flex; align-items: center; gap: 0.5rem; }

            .action-btn {
                position: relative;
                width: 34px;
                height: 34px;
                border: 1px solid var(--slate-200);
                background: #fff;
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
                border-color: var(--slate-300);
                color: var(--slate-700);
                background: var(--slate-50);
            }

            .notification-badge {
                position: absolute;
                top: -3px;
                right: -3px;
                background: var(--accent);
                color: #fff;
                font-size: 0.62rem;
                font-weight: 600;
                padding: 1px 5px;
                border-radius: 9px;
                min-width: 16px;
                text-align: center;
                font-variant-numeric: tabular-nums;
            }

            /* Menu utilisateur (header) */
            .user-menu {
                display: flex;
                align-items: center;
                gap: 0.625rem;
                padding: 0.375rem 0.625rem;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius);
                cursor: pointer;
                transition: border-color .15s, background-color .15s;
                background: #fff;
            }

            .user-menu:hover {
                border-color: var(--slate-300);
                background: var(--slate-50);
            }

            .user-avatar-header {
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

            .user-info-header { display: flex; flex-direction: column; line-height: 1.25; }

            .user-name {
                font-size: 0.82rem;
                font-weight: 500;
                color: var(--slate-800);
                margin: 0;
            }

            .user-role {
                font-size: 0.7rem;
                color: var(--slate-500);
            }

            /* Zone de contenu */
            .content-area {
                flex: 1;
                padding: 1.5rem;
                overflow-y: auto;
                background: var(--slate-50);
            }

            /* Cartes Bootstrap — bordure subtile, ombre minimale */
            .content-area .card {
                border: 1px solid var(--slate-200);
                box-shadow: var(--shadow-xs);
                border-radius: var(--radius);
            }

            .content-area .card-header {
                background: #fff;
                border-bottom: 1px solid var(--slate-200);
                font-weight: 500;
                color: var(--slate-700);
            }

            .content-area .card-header.bg-primary,
            .content-area .card-header[style*="009e3f"],
            .content-area .card-header[style*="003f7f"],
            .content-area .card-header.bg-warning,
            .content-area .card-header.bg-danger,
            .content-area .card-header.bg-info,
            .content-area .card-header.bg-success,
            .content-area .card-header.bg-secondary {
                background: var(--slate-50) !important;
                color: var(--slate-700) !important;
                border-bottom-color: var(--slate-200);
            }

            /* Boutons Bootstrap — atténuation des couleurs vives */
            .btn-primary {
                background-color: var(--accent);
                border-color: var(--accent);
            }
            .btn-primary:hover, .btn-primary:focus {
                background-color: var(--accent-hover);
                border-color: var(--accent-hover);
                box-shadow: 0 0 0 3px var(--accent-soft);
            }
            .btn-outline-primary {
                color: var(--accent);
                border-color: var(--accent);
            }
            .btn-outline-primary:hover, .btn-outline-primary:focus {
                background-color: var(--accent);
                border-color: var(--accent);
                color: #fff;
            }

            /* Messages d'alerte — versions soft */
            .alert {
                border: 1px solid transparent;
                border-radius: var(--radius);
                border-left: 3px solid;
                margin-bottom: 1rem;
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
                border-left-color: var(--gabon-red);
                color: #6b3340;
            }

            .alert-warning {
                background: rgba(212, 167, 44, 0.07);
                border-color: rgba(212, 167, 44, 0.20);
                border-left-color: var(--gabon-yellow);
                color: #7a5a17;
            }

            .alert-info {
                background: rgba(31, 58, 95, 0.06);
                border-color: rgba(31, 58, 95, 0.18);
                border-left-color: var(--gabon-blue);
                color: #1f3a5f;
            }

            /* Badges Bootstrap — atténués */
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
            .badge.bg-dark {
                background-color: var(--slate-700) !important;
            }
            .badge.bg-light {
                background-color: var(--slate-100) !important;
                color: var(--slate-700) !important;
            }

            /* Tables Bootstrap — séparateurs légers */
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

            /* Forms */
            .form-control, .form-select {
                border-color: var(--slate-200);
                color: var(--slate-700);
                font-size: 0.875rem;
            }
            .form-control:focus, .form-select:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px var(--accent-soft);
            }

            /* Suppression des dégradés Gabon partout (rétro-compat) */
            .bg-gabon, [class*="bg-gabon"] {
                background: var(--accent) !important;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                }

                .sidebar.active {
                    transform: translateX(0);
                }

                .main-content {
                    margin-left: 0;
                }

                .search-container {
                    display: none;
                }

                .header-title {
                    font-size: 1.2rem;
                }
            }

            /* Scrollbar personnalisée */
            .sidebar::-webkit-scrollbar {
                width: 6px;
            }

            .sidebar::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.1);
            }

            .sidebar::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 3px;
            }

            /* ✅ ANIMATION DE CHARGEMENT POUR UX */
            .nav-link-custom.loading {
                opacity: 0.7;
                pointer-events: none;
            }

            .nav-link-custom.loading::after {
                content: '';
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                width: 12px;
                height: 12px;
                border: 2px solid transparent;
                border-top: 2px solid currentColor;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: translateY(-50%) rotate(0deg);
                }

                100% {
                    transform: translateY(-50%) rotate(360deg);
                }
            }
        </style>
        {{-- CKEditor for WYSIWYG fields --}}
        <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js" integrity="sha384-IBDNY5TVKWr+u1841ldzW99oyOoUBpoGeouNuoXVwF0PBFR3v10dzwm09xNIEeiG" crossorigin="anonymous"></script>

    </head>

    <body>
        <div class="admin-layout">
            <!-- Sidebar optimisée avec accordéons -->
            <aside class="sidebar" id="sidebar">
                <!-- Logo et titre -->
                <div class="sidebar-header">
                    <div class="logo-section">
                        <div class="main-logo">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="logo-text-group">
                            <h5 class="sidebar-title">DGELP</h5>
                            <div class="sidebar-subtitle">Admin</div>
                        </div>
                    </div>
                    <div class="settings-icon">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                </div>

                <!-- Profil utilisateur -->
                <div class="sidebar-profile">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="profile-info">
                        <h6>{{ auth()->user()->name ?? 'Administrateur DGELP' }}</h6>
                        <small>
                            <i class="fas fa-crown"></i>
                            {{ auth()->user()->role ?? 'Administrateur' }}
                        </small>
                    </div>
                </div>

                <!-- Navigation avec accordéons -->
                <nav class="sidebar-nav">
                    <!-- ✅ TABLEAU DE BORD - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('dashboard')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-tachometer-alt"></i>
                                <span class="nav-section-title">Tableau de Bord</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-dashboard">
                            <ul class="nav-list">
                                <li class="nav-item">
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-eye"></i>
                                        <span class="nav-text">Vue d'ensemble</span>
                                    </a>
                                </li>
                                @if(Route::has('admin.analytics'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.analytics') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-chart-line"></i>
                                            <span class="nav-text">Analytiques</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    @php
                        // ============================================================
                        // OPÉRATIONS REGROUPÉES PAR TYPE — chaque type d'opération
                        // expose ses sous-statuts (brouillon, en attente, en cours,
                        // terminé, rejeté, annulé, tous).
                        // ============================================================
                        $operationsMenu = [
                            ['code' => 'creation',             'libelle' => 'Création',              'icon' => 'fas fa-plus-circle text-success'],
                            ['code' => 'modification',         'libelle' => 'Modification',          'icon' => 'fas fa-edit text-info'],
                            ['code' => 'cessation',            'libelle' => 'Cessation',             'icon' => 'fas fa-ban text-danger'],
                            ['code' => 'ajout_adherent',       'libelle' => 'Ajout Adhérent',        'icon' => 'fas fa-user-plus text-success'],
                            ['code' => 'retrait_adherent',     'libelle' => 'Retrait Adhérent',      'icon' => 'fas fa-user-minus text-warning'],
                            ['code' => 'declaration_activite', 'libelle' => 'Déclaration Activité',  'icon' => 'fas fa-file-alt text-primary'],
                            ['code' => 'changement_statutaire','libelle' => 'Changement Statutaire', 'icon' => 'fas fa-clipboard-check text-info'],
                            ['code' => 'correction',           'libelle' => 'Correction',            'icon' => 'fas fa-pen-fancy text-warning'],
                        ];

                        // Pré-calcul des compteurs par (type_operation, statut)
                        $opCounts = [];
                        if (class_exists('App\\Models\\Dossier')) {
                            $rows = \App\Models\Dossier::selectRaw('type_operation, statut, COUNT(*) as total')
                                ->groupBy('type_operation', 'statut')
                                ->get();
                            foreach ($rows as $row) {
                                $opCounts[$row->type_operation][$row->statut] = $row->total;
                                $opCounts[$row->type_operation]['_total'] = ($opCounts[$row->type_operation]['_total'] ?? 0) + $row->total;
                            }
                        }

                        $currentTypeOp = request()->input('type_operation');
                    @endphp

                    {{-- ====== ACTIONS RAPIDES ====== --}}
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('quick-actions')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-bolt"></i>
                                <span class="nav-section-title">Actions rapides</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-quick-actions">
                            <ul class="nav-list">
                                @if(Route::has('admin.organisations.create'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.organisations.create') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.organisations.create') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-plus-circle text-success"></i>
                                            <span class="nav-text">Nouvelle Organisation</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Route::has('admin.operations.select-organisation'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.operations.select-organisation') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.operations.select-organisation') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list-alt"></i>
                                            <span class="nav-text">Lancer une opération</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Route::has('admin.dossiers.tous'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.dossiers.tous') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.dossiers.tous') && !$currentTypeOp ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-folder-open text-primary"></i>
                                            <span class="nav-text">Tous les dossiers</span>
                                            @php $totalAll = class_exists('App\\Models\\Dossier') ? \App\Models\Dossier::count() : 0; @endphp
                                            @if($totalAll > 0)
                                                <span class="nav-badge">{{ $totalAll }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                                @if(Route::has('admin.organisations.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.organisations.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.organisations.index') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-building"></i>
                                            <span class="nav-text">Toutes les Organisations</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- ====== OPÉRATIONS — chaque type a ses sous-statuts ====== --}}
                    @foreach($operationsMenu as $op)
                        @php
                            $opTotal = $opCounts[$op['code']]['_total'] ?? 0;
                            $sectionId = 'op-' . str_replace('_', '-', $op['code']);
                            $isCurrent = $currentTypeOp === $op['code'];

                            $statusItems = [
                                ['key' => 'brouillon', 'label' => 'Brouillons',    'route' => 'admin.dossiers.brouillons',   'icon' => 'fas fa-edit',         'badge' => '#6b7280'],
                                ['key' => 'soumis',    'label' => 'En attente',    'route' => 'admin.dossiers.en-attente',   'icon' => 'fas fa-clock',        'badge' => '#f59e0b'],
                                ['key' => 'en_cours',  'label' => 'En cours',      'route' => 'admin.workflow.en-cours',     'icon' => 'fas fa-cogs',         'badge' => '#3b82f6'],
                                ['key' => 'accepte',   'label' => 'Terminés',      'route' => 'admin.workflow.termines',     'icon' => 'fas fa-check-circle', 'badge' => '#10b981'],
                                ['key' => 'rejete',    'label' => 'Rejetés',       'route' => 'admin.dossiers.rejetes',      'icon' => 'fas fa-times-circle', 'badge' => '#dc2626'],
                                ['key' => 'annule',    'label' => 'Annulés',       'route' => 'admin.dossiers.annules',      'icon' => 'fas fa-ban',          'badge' => '#991b1b'],
                                ['key' => null,        'label' => 'Tous',          'route' => 'admin.dossiers.tous',         'icon' => 'fas fa-list',         'badge' => '#0d6efd'],
                            ];
                        @endphp
                        <div class="nav-section">
                            <div class="nav-section-header" onclick="toggleSection('{{ $sectionId }}')">
                                <div class="nav-section-title-content">
                                    <i class="nav-section-icon {{ $op['icon'] }}"></i>
                                    <span class="nav-section-title">{{ $op['libelle'] }}</span>
                                    @if($opTotal > 0)
                                        <span class="nav-section-badge">{{ $opTotal }}</span>
                                    @endif
                                </div>
                                <i class="nav-section-toggle fas fa-chevron-down"></i>
                            </div>
                            <div class="nav-subsection {{ $isCurrent ? 'open' : '' }}" id="section-{{ $sectionId }}">
                                <ul class="nav-list">
                                    {{-- Lien "Nouveau" pour lancer une opération de ce type --}}
                                    @php
                                        if ($op['code'] === 'creation') {
                                            // Création = nouvelle organisation
                                            $newRoute = Route::has('admin.organisations.create') ? route('admin.organisations.create') : null;
                                        } elseif (Route::has('admin.operations.select-organisation')) {
                                            $newRoute = route('admin.operations.select-organisation', ['operation' => $op['code']]);
                                        } else {
                                            $newRoute = null;
                                        }
                                    @endphp
                                    @if($newRoute)
                                        <li class="nav-item">
                                            <a href="{{ $newRoute }}" class="nav-link-custom">
                                                <i class="nav-icon fas fa-plus-circle text-success"></i>
                                                <span class="nav-text"><strong>Nouveau</strong></span>
                                            </a>
                                        </li>
                                    @endif

                                    @foreach($statusItems as $item)
                                        @if(Route::has($item['route']))
                                            @php
                                                $count = $item['key']
                                                    ? ($opCounts[$op['code']][$item['key']] ?? 0)
                                                    : $opTotal;
                                                $isActive = $currentTypeOp === $op['code'] && request()->routeIs($item['route']);
                                            @endphp
                                            <li class="nav-item">
                                                <a href="{{ route($item['route'], ['type_operation' => $op['code']]) }}"
                                                   class="nav-link-custom {{ $isActive ? 'active' : '' }}">
                                                    <i class="nav-icon {{ $item['icon'] }}"></i>
                                                    <span class="nav-text">{{ $item['label'] }}</span>
                                                    @if($count > 0)
                                                        <span class="nav-badge" style="background: {{ $item['badge'] }};">{{ $count }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach

                    <!-- ✅ BASE DE DONNÉES NIP - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('database')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-database"></i>
                                <span class="nav-section-title">Base de Données</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-database">
                            <ul class="nav-list">
                                @if(Route::has('admin.nip-database.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.nip-database.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.nip-database.index') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Base NIP</span>
                                            <span class="nav-badge info">2,847</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.nip-database.import'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.nip-database.import') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.nip-database.import') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-upload"></i>
                                            <span class="nav-text">Import NIP</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.nip-database.template'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.nip-database.template') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-download"></i>
                                            <span class="nav-text">Template Excel</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>



                    <!-- ✅ DOCUMENTS OFFICIELS - ACCORDÉON ⭐ NOUVEAU MODULE -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('documents')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-file-alt"></i>
                                <span class="nav-section-title">Documents Officiels</span>
                                @php
                                    $totalDocuments = class_exists('App\Models\DocumentGeneration') ? \App\Models\DocumentGeneration::count() : 0;
                                @endphp
                                @if($totalDocuments > 0)
                                    <span class="nav-section-badge">{{ $totalDocuments }}</span>
                                @endif
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-documents">
                            <ul class="nav-list">
                                <!-- Templates de Documents -->
                                @if(Route::has('admin.document-templates.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.document-templates.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.document-templates*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-file-code"></i>
                                            <span class="nav-text">Templates Documents</span>
                                            @php
                                                $totalTemplates = class_exists('App\Models\DocumentTemplate') ? \App\Models\DocumentTemplate::count() : 0;
                                            @endphp
                                            @if($totalTemplates > 0)
                                                <span class="nav-badge info">{{ $totalTemplates }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                                <!-- Créer un nouveau template -->
                                @if(Route::has('admin.document-templates.create'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.document-templates.create') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.document-templates.create') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-plus-circle"></i>
                                            <span class="nav-text">Nouveau Template</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- Séparateur visuel -->
                                <li class="nav-item"
                                    style="margin: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
                                </li>

                                <!-- Documents générés -->
                                @if(Route::has('admin.documents.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.documents.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.documents.index') || request()->routeIs('admin.documents.show') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-file-pdf"></i>
                                            <span class="nav-text">Documents Générés</span>
                                            @php
                                                $totalGeneres = class_exists('App\Models\DocumentGeneration') ? \App\Models\DocumentGeneration::where('is_valid', true)->count() : 0;
                                            @endphp
                                            @if($totalGeneres > 0)
                                                <span class="nav-badge success">{{ $totalGeneres }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                                <!-- Générer un document -->
                                @if(Route::has('admin.documents.create'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.documents.create') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.documents.create') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-file-medical"></i>
                                            <span class="nav-text">Générer Document</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- Vérifications publiques (index admin) -->
                                @if(Route::has('admin.document-verifications.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.document-verifications.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.document-verifications*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-shield-alt"></i>
                                            <span class="nav-text">Vérifications QR</span>
                                            @php
                                                $totalVerifications = class_exists('App\Models\DocumentVerification') ? \App\Models\DocumentVerification::count() : 0;
                                            @endphp
                                            @if($totalVerifications > 0)
                                                <span class="nav-badge info">{{ $totalVerifications }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>


                    <!-- ✅ UTILISATEURS - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('users')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-users"></i>
                                <span class="nav-section-title">Utilisateurs</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-users">
                            <ul class="nav-list">
                                @if(Route::has('admin.users.operators'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.users.operators') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.users.operators') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-cog"></i>
                                            <span class="nav-text">Opérateurs</span>
                                            <span class="nav-badge users">12</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.users.agents'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.users.agents') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.users.agents') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-tie"></i>
                                            <span class="nav-text">Agents</span>
                                            <span class="nav-badge">25</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.users.create'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.users.create') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-plus"></i>
                                            <span class="nav-text">Nouvel Agent</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- ✅ RÔLES & PERMISSIONS - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('roles')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-user-shield"></i>
                                <span class="nav-section-title">Rôles & Permissions</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-roles">
                            <ul class="nav-list">
                                @if(Route::has('admin.roles.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.roles.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-tag"></i>
                                            <span class="nav-text">Gestion Rôles</span>
                                            <span class="nav-badge roles">8</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.permissions.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.permissions.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-key"></i>
                                            <span class="nav-text">Permissions</span>
                                            <span class="nav-badge permissions">47</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.permissions.matrix'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.permissions.matrix') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.permissions.matrix') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-table"></i>
                                            <span class="nav-text">Matrice Permission</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>


                    <!-- ✅ CONFIGURATION WORKFLOW - NOUVELLE SECTION -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('workflow-config')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-project-diagram"></i>
                                <span class="nav-section-title">Configuration Workflow</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-workflow-config">
                            <ul class="nav-list">
                                @if(Route::has('admin.workflow-steps.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.workflow-steps.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.workflow-steps.*') && !request()->routeIs('admin.workflow-steps.timeline') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-stream"></i>
                                            <span class="nav-text">Étapes Workflow</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.workflow-steps.timeline'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.workflow-steps.timeline') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.workflow-steps.timeline') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-sitemap"></i>
                                            <span class="nav-text">Timeline Workflow</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.validation-entities.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.validation-entities.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.validation-entities.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-shield-check"></i>
                                            <span class="nav-text">Entités de Validation</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.workflow.templates'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.workflow.templates') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.workflow.templates') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-layer-group"></i>
                                            <span class="nav-text">Templates Workflow</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- ✅ CONFIGURATION - ACCORDÉON CORRIGÉ -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('config')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-cogs"></i>
                                <span class="nav-section-title">Configuration</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-config">
                            <ul class="nav-list">
                                <!-- GÉOLOCALISATION GABON - LIENS SIMPLES CORRIGÉS -->
                                @if(Route::has('admin.geolocalisation.provinces.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.provinces.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.provinces.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-map"></i>
                                            <span class="nav-text">Provinces</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/provinces') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-map"></i>
                                            <span class="nav-text">Provinces</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.departements.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.departements.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.departements.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-building"></i>
                                            <span class="nav-text">Départements</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/departements') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-building"></i>
                                            <span class="nav-text">Départements</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.communes-villes.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.communes-villes.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.communes.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-city"></i>
                                            <span class="nav-text">Communes/Villes</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/communes') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-city"></i>
                                            <span class="nav-text">Communes/Villes</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.arrondissements.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.arrondissements.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.arrondissements.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-landmark"></i>
                                            <span class="nav-text">Arrondissements</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/arrondissements') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-landmark"></i>
                                            <span class="nav-text">Arrondissements</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.cantons.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.cantons.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.cantons.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-tree"></i>
                                            <span class="nav-text">Cantons</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/cantons') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-tree"></i>
                                            <span class="nav-text">Cantons</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.regroupements.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.regroupements.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.regroupements.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-home"></i>
                                            <span class="nav-text">Regroupements</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/regroupements') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-home"></i>
                                            <span class="nav-text">Regroupements</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.geolocalisation.localites.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.localites.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.localites.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-map-pin"></i>
                                            <span class="nav-text">Localités</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/localites') }}" class="nav-link-custom">
                                            <i class="nav-icon fas fa-map-pin"></i>
                                            <span class="nav-text">Localités</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- SÉPARATEUR VISUEL -->
                                <li class="nav-item"
                                    style="margin: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
                                </li>

                                <!-- CONFIGURATION TRADITIONNELLE -->
                                @if(Route::has('admin.referentiels.types-organisations'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.referentiels.types-organisations') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.referentiels.types-organisations') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-building"></i>
                                            <span class="nav-text">Types Organisations</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.referentiels.document-types.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.referentiels.document-types.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.referentiels.document-types.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-file-alt"></i>
                                            <span class="nav-text">Types Documents</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.referentiels.fonctions.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.referentiels.fonctions.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.referentiels.fonctions.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-tag"></i>
                                            <span class="nav-text">Fonctions Membres</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.referentiels.domaines-activite.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.referentiels.domaines-activite.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.referentiels.domaines-activite.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-industry"></i>
                                            <span class="nav-text">Domaines d'activité</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.suggestions.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.suggestions.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.suggestions*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-lightbulb"></i>
                                            <span class="nav-text">Suggestions en attente</span>
                                            @php
                                                $pendingSuggestions = 0;
                                                try {
                                                    $pendingSuggestions = \App\Models\Fonction::where('suggestion_status', 'pending')->count()
                                                        + \App\Models\DomaineActivite::where('suggestion_status', 'pending')->count();
                                                } catch (\Exception $e) {}
                                            @endphp
                                            @if($pendingSuggestions > 0)
                                                <span class="nav-badge warning">{{ $pendingSuggestions }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                                @if(Route::has('admin.settings.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.settings.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-sliders-h"></i>
                                            <span class="nav-text">Paramètres</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- ✅ RAPPORTS - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('reports')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-chart-bar"></i>
                                <span class="nav-section-title">Rapports</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-reports">
                            <ul class="nav-list">
                                @if(Route::has('admin.reports.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.reports.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-file-chart-line"></i>
                                            <span class="nav-text">Rapports Généraux</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.exports.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.exports.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.exports*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-download"></i>
                                            <span class="nav-text">Exports</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- ✅ SYSTÈME - ACCORDÉON -->
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleSection('system')">
                            <div class="nav-section-title-content">
                                <i class="nav-section-icon fas fa-server"></i>
                                <span class="nav-section-title">Système</span>
                            </div>
                            <i class="nav-section-toggle fas fa-chevron-down"></i>
                        </div>
                        <div class="nav-subsection" id="section-system">
                            <ul class="nav-list">
                                @if(Route::has('admin.notifications.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.notifications.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-bell"></i>
                                            <span class="nav-text">Notifications</span>
                                            <span class="nav-badge warning">3</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Route::has('admin.activity-logs.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.activity-logs.index') }}"
                                            class="nav-link-custom {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-history"></i>
                                            <span class="nav-text">Logs d'Activité</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>


                    {{-- ========== PORTAIL PUBLIC - CMS ========== --}}
                    <div class="nav-section">
                        <ul class="nav-list" style="padding: 0.25rem 0;">
                            <li class="nav-item">
                                <a href="{{ route('admin.portail.dashboard') }}"
                                   class="nav-link-custom {{ request()->routeIs('admin.portail*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-globe"></i>
                                    <span class="nav-text">Portail Public</span>
                                    @php $msgNonLus = \App\Models\PortailMessage::where('statut','non_lu')->count(); @endphp
                                    @if($msgNonLus > 0)
                                        <span class="nav-badge warning">{{ $msgNonLus }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- ========== API - INTEROPÉRABILITÉ ========== --}}
                    <div class="nav-section">
                        <ul class="nav-list" style="padding: 0.25rem 0;">
                            <li class="nav-item">
                                <a href="{{ route('admin.api.tokens.index') }}"
                                   class="nav-link-custom {{ request()->routeIs('admin.api.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plug"></i>
                                    <span class="nav-text">API / Interopérabilité</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.corrections.index') }}"
                                   class="nav-link-custom {{ request()->routeIs('admin.corrections.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-pen-fancy"></i>
                                    <span class="nav-text">Corrections</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                </nav>
            </aside>

            <!-- Contenu principal -->
            <main class="main-content">
                <!-- Header principal -->
                <header class="main-header">
                    <div class="header-left">
                        <h1 class="header-title">
                            <i class="fas fa-shield-alt" style="color: var(--gabon-blue);"></i>
                            @yield('title', 'Administration DGELP')
                        </h1>
                    </div>

                    <div class="header-right">
                        <!-- Recherche -->
                        <div class="search-container">
                            <i class="search-icon fas fa-search"></i>
                            <input type="text" class="search-input" placeholder="Rechercher dans l'administration...">
                        </div>

                        <!-- Actions -->
                        <div class="header-actions">
                            <button class="action-btn" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge">3</span>
                            </button>

                            <button class="action-btn" title="Messages">
                                <i class="fas fa-envelope"></i>
                                <span class="notification-badge">2</span>
                            </button>

                            <!-- Menu utilisateur -->
                            <div class="dropdown">
                                <div class="user-menu" data-toggle="dropdown">
                                    <div class="user-avatar-header">
                                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'AD' }}
                                    </div>
                                    <div class="user-info-header">
                                        <div class="user-name">{{ auth()->user()->name ?? 'Admin DGELP' }}</div>
                                        <div class="user-role">{{ auth()->user()->role ?? 'Administrateur' }}</div>
                                    </div>
                                    <i class="fas fa-chevron-down ml-2" style="color: #6b7280; font-size: 0.8rem;"></i>
                                </div>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                                        <i class="fas fa-user mr-2"></i> Mon profil
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cog mr-2"></i> Paramètres
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item"
                                            style="border: none; background: none; width: 100%; text-align: left;">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Zone de contenu -->
                <div class="content-area">
                    <!-- Messages d'alerte -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Contenu de la page -->
                    @yield('content')
                </div>
            </main>
        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function () {
                console.log('DGELP Admin Layout Optimisé - Accordéons Activés');

                // Auto-dismiss des alertes
                setTimeout(function () {
                    $('.alert').fadeOut();
                }, 5000);

                // Charger les préférences d'accordéon sauvegardées
                loadAccordionPreferences();

                // Gestion responsive mobile
                if (window.innerWidth <= 768) {
                    $('.header-left').prepend('<button class="btn btn-link p-0 mr-3" onclick="toggleMobileSidebar()"><i class="fas fa-bars"></i></button>');
                }

                // Animation hover sur les liens de navigation
                $('.nav-link-custom').hover(function () {
                    if (!$(this).hasClass('active')) {
                        $(this).css('transform', 'translateX(3px)');
                    }
                }, function () {
                    if (!$(this).hasClass('active')) {
                        $(this).css('transform', 'translateX(0)');
                    }
                });

                // ✅ Dropdown menu utilisateur (fix pour div non-interactif)
                $('.user-menu').on('click', function (e) {
                    e.stopPropagation();
                    var $menu = $(this).next('.dropdown-menu');
                    var isVisible = $menu.hasClass('show');
                    // Fermer tous les dropdowns ouverts
                    $('.dropdown-menu.show').removeClass('show');
                    if (!isVisible) {
                        $menu.addClass('show');
                    }
                });

                // Fermer le dropdown en cliquant ailleurs
                $(document).on('click', function () {
                    $('.dropdown-menu.show').removeClass('show');
                });
            });

            // ✅ FONCTION PRINCIPALE D'ACCORDÉON UNIFIÉ AVEC FERMETURE AUTOMATIQUE
            function toggleSection(sectionId) {
                const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
                const subsection = document.getElementById(`section-${sectionId}`);

                if (header && subsection) {
                    const isActive = header.classList.contains('active');

                    // ✅ FERMER TOUTES LES AUTRES SECTIONS D'ABORD
                    closeAllSections(sectionId);

                    if (isActive) {
                        // Fermer la section actuelle si elle était ouverte
                        header.classList.remove('active');
                        subsection.classList.remove('active');
                        header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-up');
                        header.querySelector('.nav-section-toggle').classList.add('fa-chevron-down');
                    } else {
                        // Ouvrir la section
                        header.classList.add('active');
                        subsection.classList.add('active');
                        header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-down');
                        header.querySelector('.nav-section-toggle').classList.add('fa-chevron-up');
                    }

                    // Sauvegarder les préférences
                    saveAccordionPreferences();

                    // Animation fluide
                    if (subsection.classList.contains('active')) {
                        subsection.style.maxHeight = subsection.scrollHeight + 'px';
                    } else {
                        subsection.style.maxHeight = '0px';
                    }
                }
            }

            // ✅ FONCTION POUR FERMER TOUTES LES SECTIONS SAUF CELLE SPÉCIFIÉE
            function closeAllSections(exceptSectionId = null) {
                document.querySelectorAll('.nav-section-header.active').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick) {
                        const match = onclick.match(/toggleSection\('([^']+)'\)/);
                        if (match && match[1] !== exceptSectionId) {
                            const sectionId = match[1];
                            const subsection = document.getElementById(`section-${sectionId}`);

                            // Fermer cette section
                            header.classList.remove('active');
                            if (subsection) {
                                subsection.classList.remove('active');
                                subsection.style.maxHeight = '0px';
                            }

                            // Mettre à jour l'icône
                            const toggle = header.querySelector('.nav-section-toggle');
                            if (toggle) {
                                toggle.classList.remove('fa-chevron-up');
                                toggle.classList.add('fa-chevron-down');
                            }
                        }
                    }
                });
            }

            // ✅ FONCTION SPÉCIALISÉE POUR GÉOLOCALISATION
            function toggleGeoSection(sectionId) {
                const header = document.querySelector(`[onclick="toggleGeoSection('${sectionId}')"]`);
                const subsection = document.getElementById(`geo-${sectionId}`);

                if (header && subsection) {
                    const isExpanded = header.classList.contains('expanded');

                    if (isExpanded) {
                        // Fermer la sous-section géo
                        header.classList.remove('expanded');
                        subsection.classList.remove('active');
                        header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-up');
                        header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-down');
                    } else {
                        // Ouvrir la sous-section géo
                        header.classList.add('expanded');
                        subsection.classList.add('active');
                        header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-down');
                        header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-up');
                    }

                    // Sauvegarder les préférences géo
                    saveGeoPreferences();

                    // Animation fluide pour les sections géo
                    if (subsection.classList.contains('active')) {
                        subsection.style.maxHeight = subsection.scrollHeight + 'px';
                    } else {
                        subsection.style.maxHeight = '0px';
                    }
                }
            }

            // ✅ SAUVEGARDE DES PRÉFÉRENCES D'ACCORDÉON
            function saveAccordionPreferences() {
                try {
                    const activeSections = [];
                    document.querySelectorAll('.nav-section-header.active').forEach(header => {
                        const onclick = header.getAttribute('onclick');
                        if (onclick) {
                            const match = onclick.match(/toggleSection\('([^']+)'\)/);
                            if (match) {
                                activeSections.push(match[1]);
                            }
                        }
                    });
                    localStorage.setItem('sglp_active_sections', JSON.stringify(activeSections));
                } catch (e) {
                    console.log('Erreur lors de la sauvegarde des préférences accordéon:', e);
                }
            }

            // ✅ SAUVEGARDE DES PRÉFÉRENCES GÉOLOCALISATION
            function saveGeoPreferences() {
                try {
                    const expandedGeoSections = [];
                    document.querySelectorAll('.geo-section-header.expanded').forEach(header => {
                        const onclick = header.getAttribute('onclick');
                        if (onclick) {
                            const match = onclick.match(/toggleGeoSection\('([^']+)'\)/);
                            if (match) {
                                expandedGeoSections.push(match[1]);
                            }
                        }
                    });
                    localStorage.setItem('sglp_geo_sections', JSON.stringify(expandedGeoSections));
                } catch (e) {
                    console.log('Erreur lors de la sauvegarde des préférences géo:', e);
                }
            }

            // ✅ CHARGEMENT DES PRÉFÉRENCES SAUVEGARDÉES
            function loadAccordionPreferences() {
                try {
                    // Charger les sections principales
                    const savedSections = localStorage.getItem('sglp_active_sections');
                    if (savedSections) {
                        const activeSections = JSON.parse(savedSections);
                        activeSections.forEach(sectionId => {
                            const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
                            const subsection = document.getElementById(`section-${sectionId}`);
                            if (header && subsection) {
                                header.classList.add('active');
                                subsection.classList.add('active');
                                header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-down');
                                header.querySelector('.nav-section-toggle').classList.add('fa-chevron-up');
                            }
                        });
                    }

                    // Charger les sections géolocalisation
                    const savedGeoSections = localStorage.getItem('sglp_geo_sections');
                    if (savedGeoSections) {
                        const expandedGeoSections = JSON.parse(savedGeoSections);
                        expandedGeoSections.forEach(sectionId => {
                            const header = document.querySelector(`[onclick="toggleGeoSection('${sectionId}')"]`);
                            const subsection = document.getElementById(`geo-${sectionId}`);
                            if (header && subsection) {
                                header.classList.add('expanded');
                                subsection.classList.add('active');
                                header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-down');
                                header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-up');
                            }
                        });
                    }
                } catch (e) {
                    console.log('Erreur lors du chargement des préférences:', e);
                }
            }

            // ✅ FONCTIONS UTILITAIRES GÉOLOCALISATION
            function geoGlobalSearch() {
                const searchTerm = prompt('Rechercher dans toutes les entités géographiques:');
                if (searchTerm && searchTerm.trim()) {
                    const searchUrl = '{{ route("admin.dashboard") }}' + '?geo_search=' + encodeURIComponent(searchTerm);
                    window.location.href = searchUrl;
                }
            }

            function geoHierarchyViewer() {
                @if(Route::has('admin.geolocalisation.provinces.index'))
                    window.open('{{ route("admin.geolocalisation.provinces.index") }}?view=hierarchy', '_blank');
                @else
                    alert('Fonctionnalité en cours de développement');
                @endif
        }

            function geoStatistics() {
                @if(Route::has('admin.analytics'))
                    window.open('{{ route("admin.analytics") }}?section=geolocalisation', '_blank');
                @else
                    alert('Module analytics en cours de développement');
                @endif
        }

            function geoExportAll() {
                if (confirm('Exporter toutes les données géographiques du Gabon ?')) {
                    @if(Route::has('admin.exports.index'))
                        window.location.href = '{{ route("admin.exports.index") }}?type=geolocalisation&format=excel';
                    @else
                        alert('Module export en cours de développement');
                    @endif
            }
            }

            // ✅ FONCTIONS RESPONSIVES
            function toggleMobileSidebar() {
                $('#sidebar').toggleClass('active');
            }

            // Fermer le sidebar mobile en cliquant sur un lien
            $('.nav-link-custom').on('click', function () {
                if (window.innerWidth <= 768) {
                    $('#sidebar').removeClass('active');
                }
            });

            // ✅ RECHERCHE AMÉLIORÉE
            $('.search-input').on('keypress', function (e) {
                if (e.which === 13) { // Entrée
                    const searchTerm = $(this).val().trim();
                    if (searchTerm) {
                        window.location.href = '{{ route("admin.dashboard") }}?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });



            // ✅ GESTION DES ERREURS GRACIEUSES
            window.addEventListener('error', function (e) {
                console.log('Erreur JavaScript interceptée:', e.message);
            });

            // ✅ PROTECTION CONTRE LES ROUTES MANQUANTES
            $('a[href=""]').on('click', function (e) {
                e.preventDefault();
                alert('Cette fonctionnalité est en cours de développement');
            });

            // ✅ ACCORDÉON INTELLIGENT - FERMETURE AUTOMATIQUE DES AUTRES SECTIONS
            function toggleSectionExclusive(sectionId) {
                // Fermer toutes les autres sections
                document.querySelectorAll('.nav-section-header.active').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick && !onclick.includes(sectionId)) {
                        const match = onclick.match(/toggleSection\('([^']+)'\)/);
                        if (match) {
                            toggleSection(match[1]);
                        }
                    }
                });

                // Ouvrir/fermer la section actuelle
                toggleSection(sectionId);
            }

            // ✅ RACCOURCIS CLAVIER
            document.addEventListener('keydown', function (e) {
                // Ctrl + B pour toggle sidebar sur mobile
                if (e.ctrlKey && e.key === 'b' && window.innerWidth <= 768) {
                    e.preventDefault();
                    toggleMobileSidebar();
                }

                // Echap pour fermer tous les accordéons
                if (e.key === 'Escape') {
                    document.querySelectorAll('.nav-section-header.active').forEach(header => {
                        const onclick = header.getAttribute('onclick');
                        if (onclick) {
                            const match = onclick.match(/toggleSection\('([^']+)'\)/);
                            if (match) {
                                toggleSection(match[1]);
                            }
                        }
                    });
                }
            });

            // ✅ MISE À JOUR DES COMPTEURS EN TEMPS RÉEL (SI API DISPONIBLE)
            @if(Route::has('admin.api.stats.realtime'))
                function updateRealtimeStats() {
                    $.get('{{ route("admin.api.stats.realtime") }}', function (data) {
                        if (data) {
                            // Mettre à jour les badges de compteurs
                            Object.keys(data).forEach(key => {
                                const badge = $(`.nav-text:contains("${key}")`).siblings('.nav-badge');
                                if (badge.length && data[key]) {
                                    badge.text(data[key]);
                                }
                            });
                        }
                    }).fail(function () {
                        console.log('API stats temps réel non disponible');
                    });
                }

                // Mise à jour toutes les 2 minutes
                setInterval(updateRealtimeStats, 120000);
            @endif

            console.log('✅ Layout Admin DGELP - Accordéons optimisés chargés avec succès');
        </script>

        <script src="{{ asset('js/other-suggestion.js') }}"></script>

        @stack('scripts')

        <!-- Formatage automatique des champs à la saisie (NIP, téléphone, nom/sigle, email) -->
        <script src="{{ asset('js/input-formatter.js') }}"></script>
    </body>

</html>