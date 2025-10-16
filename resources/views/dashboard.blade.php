@extends('layouts.app')

@section('title', 'Dashboard | MFIN')

@section('content')
    <header class="navbar sticky-top bg-body-tertiary flex-md-nowrap p-0 shadow-sm">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">
            <i class="fa fa-coins me-2"></i>MIMHa Finance
        </a>
        
        {{-- [PERUBAHAN] Mengatur ulang urutan elemen di dalam div ini --}}
        <div class="d-flex align-items-center ms-auto me-3 gap-3">
            
            <div class="navbar-text d-none d-sm-block text-end">
                <div>{{ session('fullname') }}</div>
                <div id="realtime-clock" class="small">Memuat jam...</div>
            </div>

            <div class="nav-item text-nowrap">
                <button class="btn btn-outline-secondary btn-sm" id="theme-toggle">
                    {{-- Ikon akan diisi oleh JavaScript dari layouts/app.blade.php --}}
                </button>
            </div>
            
            <div class="nav-item text-nowrap">
                <a class="btn btn-outline-danger btn-sm" href="{{ route('logout') }}" title="Logout">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    @include('partials.sidebar')
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                <h4>Selamat Datang!</h4>
                <p>Anda telah berhasil login ke sistem.</p>
            </main>
        </div>
    </div>
@endsection

{{-- [BARU] Menambahkan JavaScript khusus untuk halaman ini --}}
@push('scripts')
<script>
    // Fungsi untuk mengupdate jam setiap detik
    function updateClock() {
        const clockElement = document.getElementById('realtime-clock');
        if (clockElement) {
            const now = new Date();
            // Format: Hari, DD Bulan YYYY JJ:MM:SS (dalam bahasa Indonesia)
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false 
            };
            // .replace untuk menghapus kata 'pukul' yang kadang muncul
            clockElement.textContent = now.toLocaleDateString('id-ID', options).replace(/pukul/g, '');
        }
    }

    // Panggil fungsi updateClock sekali saat halaman dimuat, lalu ulangi setiap detik
    document.addEventListener('DOMContentLoaded', function() {
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endpush