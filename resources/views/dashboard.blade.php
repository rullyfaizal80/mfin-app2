@extends('layouts.app')

@section('title', 'Dashboard | MFIN')

@section('content')
    <header class="navbar sticky-top bg-body-tertiary flex-md-nowrap p-0 shadow-sm">
        {{-- Header Anda tetap sama --}}
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">
            <i class="fa fa-coins me-2"></i>MIMHa Finance
        </a>
        
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

    {{-- [PERUBAHAN] Menggunakan class "main-container" dan menghapus "container-fluid" --}}
    <div class="main-container">
        {{-- "row" tidak lagi diperlukan di sini karena sudah dihandle oleh Flexbox --}}
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
@endsection

@push('scripts')
<script>
    // Script jam Anda tetap sama
    function updateClock() {
        const clockElement = document.getElementById('realtime-clock');
        if (clockElement) {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            clockElement.textContent = now.toLocaleDateString('id-ID', options).replace(/pukul/g, '');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endpush