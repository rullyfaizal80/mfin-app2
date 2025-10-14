@extends('layouts.app')

@section('title', 'Dashboard | MFIN')

@section('content')
    <header class="navbar sticky-top bg-body-tertiary flex-md-nowrap p-0 shadow-sm">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">
            <i class="fa fa-coins me-2"></i>MIMHa Finance
        </a>
        
        <div class="d-flex align-items-center ms-auto me-3">
            <div class="nav-item text-nowrap me-3">
                <button class="btn btn-outline-secondary btn-sm" id="theme-toggle">
                    {{-- Ikon akan diisi oleh JavaScript --}}
                </button>
            </div>
            <div class="nav-item text-nowrap me-3">
                <span class="navbar-text">
                    {{ session('fullname') }}
                </span>
            </div>
            <div class="nav-item text-nowrap">
                <a class="btn btn-outline-danger btn-sm" href="{{ route('logout') }}">
                    <i class="fa fa-sign-out-alt me-1"></i>Logout
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