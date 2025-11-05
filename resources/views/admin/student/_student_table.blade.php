<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">NIS</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Tgl Lahir</th>
                    <th scope="col">L/P</th>
                    <th scope="col">Telp/HP</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr>
                    <td>{{ $loop->iteration + $students->firstItem() - 1 }}</td>
                    <td>{{ $student->nis }}</td>
                    <td>{{ $student->fullname }}</td>
                    <td>
                        {{-- Cek jika tgl lahir tidak null --}}
                        {{ $student->dateofbirth ? date('d-m-Y', strtotime($student->dateofbirth)) : '-' }}
                    </td>
                    <td>
                        {{ $student->gender == 'M' ? 'L' : 'P' }}
                    </td>
                    <td>
                        {{-- Gabungkan telp rumah dan hp --}}
                        {{ $student->home_phone ?? $student->mobile_phone }}
                    </td>
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
                    <td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($students->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $students->links() }}
</div>
@endif