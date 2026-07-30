<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Keuangan')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    {{-- [TAMBAHAN] CSS Bootstrap & FontAwesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    {{-- [PERBAIKAN] CSS DataTables (Wajib agar tabelnya rapi) --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; background-color: var(--bs-secondary-bg); }
        .main-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { height: 100%; overflow-y: auto; }
        main { overflow-y: auto; }
        .sidebar { border-right: 1px solid var(--bs-border-color); }
        .sidebar-nav .nav-link { display: flex; align-items: center; font-size: 15px; font-weight: 500; color: var(--bs-body-color); padding: 10px 15px; border-radius: 4px; transition: 0.3s; }
        .sidebar-nav .nav-link:hover { color: #0d6efd; background: var(--bs-tertiary-bg); }
        .sidebar-nav .nav-link i { font-size: 16px; margin-right: 10px; }
        .sidebar-nav .nav-link.collapsed i.bi-chevron-down { transform: rotate(0deg); transition: 0.3s; }
        .sidebar-nav .nav-link:not(.collapsed) i.bi-chevron-down { transform: rotate(-180deg); transition: 0.3s; }
        .sidebar-nav ul { list-style: none; padding-left: 0; }
        .sidebar-nav .nav-content { padding: 0; margin: 0; }
        .sidebar-nav .nav-content a { padding-left: 40px !important; font-size: 14px; }
        .sidebar-nav .nav-content .nav-content a { padding-left: 55px !important; }
        .sidebar-nav .nav-content a:hover { color: #0d6efd; }
        .navbar-toggler { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .navbar-toggler-icon { width: 1.2em; height: 1.2em; }

        header.navbar { min-height: 56px; }
        .sidebar { width: 200px; }

        @media (max-width: 767.98px) {
            #sidebarMenu {
                position: fixed;
                top: 56px; 
                left: -200px;
                width: 200px;
                height: calc(100vh - 56px);
                background-color: var(--bs-body-bg);
                z-index: 1045;
                transition: left 0.3s ease;
                overflow-y: auto;
                border-top: 1px solid var(--bs-border-color);
            }
            #sidebarMenu.show { left: 0; }
            body.sidebar-open::after {
                content: ""; position: fixed; top: 56px; left: 0; width: 100%; height: calc(100vh - 56px); background: rgba(0, 0, 0, 0.25); z-index: 1040;
            }
        }
    </style>
</head>
<body>
    
    <header class="navbar sticky-top bg-body-tertiary flex-md-nowrap p-0 shadow-sm align-items-center">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-4 d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.ico') }}" alt="Logo" width="24" height="24" class="me-2">MIMHa Finance
        </a>
        <button class="navbar-toggler d-md-none collapsed me-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="d-flex align-items-center ms-auto me-3 gap-3">
            <div class="navbar-text d-none d-sm-block text-end">
                <div>{{ session('fullname') }}</div>
                <div id="realtime-clock" class="small">Memuat jam...</div>
            </div>
            <div class="nav-item text-nowrap">
                <button class="btn btn-outline-secondary btn-sm" id="theme-toggle"></button>
            </div>
            <div class="nav-item text-nowrap">
                <a class="btn btn-outline-danger btn-sm" href="{{ route('logout') }}" title="Logout">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
            <div class="position-sticky pt-3 sidebar-sticky">
                @include('partials.sidebar')
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            @yield('content')
        </main>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="{{ asset('assets/js/main.js') }}"></script>

@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggleBtn = document.getElementById('theme-toggle'); 
        if (themeToggleBtn) {
            const htmlTag = document.documentElement;
            const applyTheme = (theme) => {
                htmlTag.setAttribute('data-bs-theme', theme);
                themeToggleBtn.innerHTML = theme === 'dark' ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
            };
            applyTheme(htmlTag.getAttribute('data-bs-theme'));
            themeToggleBtn.addEventListener('click', () => {
                const newTheme = htmlTag.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });
        }

        const clockElement = document.getElementById('realtime-clock');
        function updateClock() {
            if (clockElement) {
                const now = new Date();
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                clockElement.textContent = now.toLocaleDateString('id-ID', options).replace(/pukul/g, '');
            }
        }
        updateClock(); 
        setInterval(updateClock, 1000); 

        const sidebarMenu = document.getElementById('sidebarMenu');
        if(sidebarMenu) {
            sidebarMenu.addEventListener('shown.bs.collapse', () => document.body.classList.add('sidebar-open'));
            sidebarMenu.addEventListener('hidden.bs.collapse', () => document.body.classList.remove('sidebar-open'));
        }
    });
</script>
</body>
</html>