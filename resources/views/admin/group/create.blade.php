@extends('layouts.app')

@section('title', 'Create New Group | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Create New Group</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.group.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left-circle"></i>
                Back to Group List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Tampilkan error validasi jika ada --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <h5 class="alert-heading">Terjadi Kesalahan!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- [PERHATIAN] Pastikan 'action' mengarah ke route 'admin.group.store' --}}
            <form action="{{ route('admin.group.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="group_name" class="form-label">Group Name *</label>
                    <input type="text" class="form-control @error('group_name') is-invalid @enderror" id="group_name" name="group_name" value="{{ old('group_name') }}" required>
                    @error('group_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="ordering" class="form-label">Ordering *</label>
                    <input type="number" class="form-control @error('ordering') is-invalid @enderror" id="ordering" name="ordering" value="{{ old('ordering') }}" required>
                    <small class="text-muted">Ordering determines the display order (1, 2, 3...).</small>
                    @error('ordering')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Group
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection