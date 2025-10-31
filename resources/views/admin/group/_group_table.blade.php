<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Group Name</th>
                    <th scope="col">Ordering</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groups as $group)
                <tr>
                    <td>{{ $loop->iteration + $groups->firstItem() - 1 }}</td>
                    <td>{{ $group->group_name }}</td>
                    <td>{{ $group->ordering }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.group.edit', $group->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.group.destroy', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this group?');">
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
@if ($groups->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $groups->links() }}
</div>
@endif