@extends('layouts.app')

@section('title', 'Edit Level | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Level</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.level.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left-circle"></i>
                Back to Level List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.level.update', $level->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- <-- Penting untuk memberitahu Laravel ini adalah metode UPDATE --}}

                <div class="mb-3">
                    <label for="title" class="form-label">Level Title *</label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $level->title) }}" required>
                </div>

                <div class="mb-3">
                    <label for="grade" class="form-label">Grade *</label>
                    <input type="number" class="form-control" id="grade" name="grade" value="{{ old('grade', $level->grade) }}" required>
                    <small class="text-muted">Grade determines the display order (e.g., 1, 2, 3).</small>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Update Level
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection