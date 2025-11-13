<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama</th>
                    <th scope="col">L/P</th>
                    <th scope="col">Telp/HP</th>
                    <th scope="col">Telp Kantor</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parents as $parent)
                <tr>
                    <td>{{ $loop->iteration + $parents->firstItem() - 1 }}</td>
                    <td>{{ $parent->fullname }}</td>
                    <td>
                        {{ $parent->gender == 'M' ? 'L' : 'P' }}
                    </td>
                    <td>
                        {{ $parent->mobile_phone ?? $parent->home_phone ?? '-' }}
                    </td>
                    <td>{{ $parent->company_phone ?? '-' }}</td>
                    <td class="text-center">
                        <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                    <td colspan="6" class="text-center text-muted">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($parents->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $parents->links() }}
</div>
@endif