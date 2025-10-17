{{-- File: resources/views/admin/user/_user_table.blade.php (Lengkap & Benar) --}}

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            {{-- [PERBAIKAN] Tambahkan kembali thead yang hilang di sini --}}
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
                        <a href="#" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></a>
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