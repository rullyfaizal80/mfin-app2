@extends('layouts.guest')

@section('content')
<div class="card login-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" width="80" class="mb-2">
            <h4 class="text-muted">MIMHa Finance</h4>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><small>{{ $error }}</small></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required>
            </div>
            <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    
    {{-- [PERBAIKAN] Bungkus input dan button dengan .input-group --}}
    <div class="input-group">
        <input type="password" class="form-control" id="password" name="password" required>
        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
            <i class="bi bi-eye-slash"></i>
        </button>
    </div>
</div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>
        </form>

        <small class="text-muted text-center d-block mt-4">
            &copy; {{ date('Y') }} MIMHa Finance
        </small>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const icon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function () {
            // Ubah tipe input dari 'password' ke 'text' atau sebaliknya
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Ganti ikon mata
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    });
</script>
@endpush