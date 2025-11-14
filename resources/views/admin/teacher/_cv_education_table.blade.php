<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered">
        <thead>
            <tr>
                <th>Jenjang</th>
                <th>Institusi</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($education as $edu)
            <tr>
                <td>{{ $edu->level }}</td>
                <td>{{ $edu->institution }}</td>
                <td>{{ \Carbon\Carbon::parse($edu->start_date)->format('d M Y') }}</td>
                <td>{{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('d M Y') : '-' }}</td>
                <td class="text-center">
                    <form action="{{ route('teacher.cv.education.destroy', $edu->id) }}" method="POST" class="d-inline" data-table-target="#education-table-container">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm py-0 px-1 btn-delete-cv" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>