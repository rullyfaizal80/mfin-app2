@extends('layouts.app')

@section('title', 'User Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">User Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i>
                New
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-body-tertiary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">Filter Fullname:</span>
                        <input type="text" class="form-control form-control-sm" placeholder="Cari nama...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <div class="input-group input-group-sm justify-content-md-end">
                        <label class="input-group-text">Show</label>
                        <select class="form-select" style="max-width: 70px;">
                            <option selected>10</option>
                            <option value="1">25</option>
                            <option value="2">50</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                </div>
            </div>
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
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                            <td>{{ $user->fullname }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                @if(strtolower($user->is_active) == 'yes')
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>{{ $user->user_type ?? '-' }}</td>
                            <td>{{ $user->hp ?? $user->email ?? '/' }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-body-tertiary">
            {{-- Link Paginasi --}}
            {{ $users->links() }}
        </div>
    </div>
@endsection