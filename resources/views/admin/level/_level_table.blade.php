{{-- File: resources/views/admin/level/_level_table.blade.php --}}

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Level Title</th>
                    <th scope="col">Grade</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($levels as $level)
                <tr>
                    <td>{{ $loop->iteration + $levels->firstItem() - 1 }}</td>
                    <td>{{ $level->title }}</td>
                    <td>{{ $level->grade }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.level.edit', $level->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.level.destroy', $level->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this level?');">
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
                    <td colspan="4" class="text-center text-muted">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($levels->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $levels->links() }}
</div>
@endif