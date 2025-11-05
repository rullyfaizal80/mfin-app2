<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Fullname</th>
                    <th scope="col">Username</th>
                    <th scope="col">Status</th>
                    {{-- Kita akan tambahkan kolom Group di sini nanti --}}
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
                        @if ($user->is_active == 'yes')
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">Inactive</span>
                        @endif
                    </td>
                    
                    {{-- [PERUBAHAN] Kolom Aksi sekarang fungsional --}}
                    <td class="text-center">
                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        
                        <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Data tidak ditemukan.</td>
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