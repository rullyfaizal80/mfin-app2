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
                        <a href="{{ route('admin.acl.edit', $group->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Permissions">
                            <i class="bi bi-pencil-square"></i> Edit Permissions
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Data tidak ditemukan.</td>
                </tr>
                @endforelse {{-- <-- Ini baris yang diperbaiki --}}
            </tbody>
        </table>
    </div>
</div>
@if ($groups->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $groups->links() }}
</div>
@endif