<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">Tipe</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $type)
                <tr>
                    <td>{{ $type->title }}</td>
                    <td class="text-center">
                        <a href="{{ route('ctype.edit', $type->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('ctype.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Data yang terikat bisa jadi bermasalah.');">
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
                    <td colspan="2" class="text-center text-muted">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($list_data->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $list_data->links() }}
</div>
@endif