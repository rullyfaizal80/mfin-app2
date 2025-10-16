<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Keuangan')</title>

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- 💡 [PENTING] Skrip yang sama persis seperti di guest.blade.php --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    
    {{-- CSS untuk sidebar --}}
    <style>
        body { background-color: var(--bs-secondary-bg); }
        .sidebar { border-right: 1px solid var(--bs-border-color); }
        .sidebar-nav .nav-link { display: flex; align-items: center; font-size: 15px; font-weight: 500; color: var(--bs-body-color); padding: 10px 15px; border-radius: 4px; transition: 0.3s; }
        .sidebar-nav .nav-link:hover { color: #0d6efd; background: var(--bs-tertiary-bg); }
        .sidebar-nav .nav-link i { font-size: 16px; margin-right: 10px; }
        .sidebar-nav .nav-link.collapsed i.bi-chevron-down { transform: rotate(0deg); transition: 0.3s; }
        .sidebar-nav .nav-link:not(.collapsed) i.bi-chevron-down { transform: rotate(-180deg); transition: 0.3s; }
        .sidebar-nav .nav-content { padding: 0; margin: 0; list-style: none; }
        .sidebar-nav .nav-content a { display: flex; align-items: center; font-size: 14px; padding: 10px 0 10px 40px; transition: 0.3s; }
        .sidebar-nav .nav-content a:hover { color: #0d6efd; }
        .sidebar-nav .nav-content a i { font-size: 6px; margin-right: 8px; line-height: 0; }
    </style>
</head>
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Tambahkan baris ini --}}
    @stack('scripts')
    {{-- Skrip yang sama persis untuk mengontrol tombol toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kita cari tombolnya di mana pun dia berada (di navbar)
            const toggleBtn = document.getElementById('theme-toggle'); 
            if (toggleBtn) {
                const htmlTag = document.documentElement;

                const applyTheme = (theme) => {
                    htmlTag.setAttribute('data-bs-theme', theme);
                    toggleBtn.innerHTML = theme === 'dark' ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
                };
                
                applyTheme(htmlTag.getAttribute('data-bs-theme'));

                toggleBtn.addEventListener('click', () => {
                    const newTheme = htmlTag.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', newTheme);
                    applyTheme(newTheme);
                });
            }
        });
    </script>
</body>
</html>
