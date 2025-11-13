<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                {{-- Kolom sesuai ax_get_teacher di kode lama --}}
                <tr>
                    <th scope="col">NIK</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Telp/HP</th>
                    <th scope="col">Email</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                <tr>
                    {{-- NIK adalah kolom pertama --}}
                    <td>{{ $teacher->nik ?? '-' }}</td>
                    <td>{{ $teacher->fullname }}</td>
                    <td>
                        {{ $teacher->mobile_phone ?? $teacher->home_phone ?? '-' }}
                    </td>
                    <td>{{ $teacher->email ?? '-' }}</td>
                    <td class="text-center">
                        <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('teacher.destroy', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
@if ($teachers->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $teachers->links() }}
</div>
@endif