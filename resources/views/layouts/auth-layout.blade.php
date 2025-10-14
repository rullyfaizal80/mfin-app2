<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login | MFIN')</title>

    {{-- ✅ Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- ✅ Font Awesome untuk ikon --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
         body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: var(--bs-body-bg);
        transition: background-color 0.4s, color 0.4s;
    }

    .login-card {
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 0 30px rgba(0,0,0,0.1);
        background-color: var(--bs-body-bg);

        /* 🌟 Animasi awal */
        opacity: 0;
        transform: translateY(40px);
        animation: fadeSlideIn 0.8s ease-out forwards;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .theme-switch {
        position: absolute;
        top: 15px;
        right: 20px;
    }

    /* 🌈 Efek hover pada tombol */
    .btn-primary {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-primary:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    html, body {
    transition: background-color 0.5s ease, color 0.5s ease;
    }

    </style>
</head>
<body>
    <div class="theme-switch">
        <button class="btn btn-outline-secondary btn-sm" id="toggleTheme">
            <i class="fa fa-moon"></i>
        </button>
    </div>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('toggleTheme');
        toggleBtn.addEventListener('click', () => {
        const htmlTag = document.documentElement;
        const current = htmlTag.getAttribute('data-bs-theme');
        const next = current === 'light' ? 'dark' : 'light';
        htmlTag.style.transition = 'background-color 0.5s, color 0.5s';
        htmlTag.setAttribute('data-bs-theme', next);
        toggleBtn.innerHTML = next === 'light'
            ? '<i class="fa fa-moon"></i>'
            : '<i class="fa fa-sun"></i>';
        });
    </script>
</body>
</html>
