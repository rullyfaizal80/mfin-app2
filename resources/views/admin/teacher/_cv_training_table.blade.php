<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered">
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Judul</th>
                <th>Penyelenggara</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($training as $t)
            <tr>
                <td>{{ \Carbon\Carbon::parse($t->year)->format('Y') }}</td>
                <td>{{ $t->title }}</td>
                <td>{{ $t->provider }}</td>
                <td class="text-center">
                    <form action="{{ route('teacher.cv.training.destroy', $t->id) }}" method="POST" class="d-inline" data-table-target="#training-table-container">
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