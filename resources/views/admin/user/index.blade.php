@extends('layouts.app')

@section('title', 'User Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">User Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i>
                New
            </a>
        </div>
    </div>

    <div class="card">
      {{-- File: resources/views/admin/user/index.blade.php --}}

<div class="card-header bg-body-tertiary">
    <form action="{{ route('admin.user.index') }}" method="GET">
        {{-- [PERUBAHAN] Ganti .row menjadi .d-flex untuk layout yang lebih baik --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            
            {{-- Bagian Pencarian --}}
            <div class="input-group input-group-sm" style="max-width: 300px;"> {{-- <-- Beri batas lebar maksimal --}}
                <input type="text" 
                       class="form-control" 
                       name="search"
                       placeholder="Cari nama atau username..." 
                       value="{{ $searchTerm ?? '' }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            {{-- Bagian Dropdown "Show" --}}
            <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                <label class="input-group-text">Show</label>
                <select class="form-select" name="perPage" onchange="this.form.submit()">
                    <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="input-group-text">entries</span>
            </div>
            
        </div>
    </form>
</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Lengkap</th>
                            <th scope="col">Username</th>
                            <th scope="col">Aktif</th>
                            <th scope="col">Tipe</th>
                            <th scope="col">HP/Email</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                            <td>{{ $user->fullname }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                @if(strtolower($user->is_active) == 'yes')
                                    <span class="badge text-bg-success">Yes</span>
                                @else
                                    <span class="badge text-bg-danger">No</span>
                                @endif
                            </td>
                            <td>{{ $user->user_type ?? '-' }}</td>
                            <td>{{ $user->hp ?? $user->email ?? '/' }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
        <div class="card-footer bg-body-tertiary">
            {{ $users->links() }}
        </div>
        @endif
    </div>
@endsection