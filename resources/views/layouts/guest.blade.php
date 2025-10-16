<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login | MFIN')</title>

    {{-- Bootstrap & Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <style>
        .login-container { min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 1rem; box-shadow: 0 0 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div style="position: fixed; top: 15px; right: 20px; z-index: 1050;">
        <button class="btn btn-outline-secondary btn-sm" id="theme-toggle">
            {{-- Ikon akan diisi oleh JavaScript --}}
        </button>
    </div>

    <div class="login-container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Skrip untuk mengontrol tombol tema --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('theme-toggle');
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
        });
    </script>

    @stack('scripts')

</body>
</html>