<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered">
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Nama Organisasi</th>
                <th>Posisi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organization as $org)
            <tr>
                <td>{{ \Carbon\Carbon::parse($org->year)->format('Y') }}</td>
                <td>{{ $org->organization_name }}</td>
                <td>{{ $org->position }}</td>
                <td class="text-center">
                    <form action="{{ route('teacher.cv.organization.destroy', $org->id) }}" method="POST" class="d-inline" data-table-target="#organization-table-container">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm py-0 px-1 btn-delete-cv" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>