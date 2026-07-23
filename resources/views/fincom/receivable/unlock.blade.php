@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 d-flex justify-content-center">
    <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
        <div class="card-header bg-warning-subtle text-warning-emphasis border-bottom border-warning-subtle py-3 text-center">
            <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i> {{ $page_title }}</h5>
        </div>

        <div class="card-body p-4">
            
            @if(session('error'))
                <div class="alert alert-danger small">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('fincom.receivable.unlock.process', $id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="admin_username" class="form-label">Username Admin</label>
                    <input type="text" class="form-control" id="admin_username" name="admin_username" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="admin_password" class="form-label">Password Admin</label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-unlock"></i> Buka Kunci Edit
                    </button>
                    <a href="{{ route('fincom.receivable.ilist') }}" class="btn btn-outline-secondary">Batal & Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection