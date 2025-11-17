<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">Nama Kelas / Jurusan</th>
                    <th scope="col">Tingkat / Grup</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Sekolah</th>
                    <th scope="col">Tahun Ajaran</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $class)
                <tr>
                    {{-- Replikasi format kolom dari kode lama --}}
                    <td>
                        <strong>{{ $class->class_list_title }}</strong><br>
                        <small class="text-muted">{{ $class->csubject_title }}</small>
                    </td>
                    <td>{{ $class->cgrade_title }} {{ $class->cgroup_title }}</td>
                    <td>{{ $class->ctype_title }}</td>
                    <td>{{ $class->cschool_name }}</td>
                    <td>{{ $class->cyear_title }}</td>
                    <td class="text-center">
                        {{-- TODO: Buat route sclass/class_user/index/{id} --}}
                        <a href="#" class="btn btn-sm btn-outline-success" title="Siswa Kelas">
                            <i class="bi bi-people-fill"></i>
                        </a>
                        <a href="{{ route('class_list.edit', $class->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('class_list.destroy', $class->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Semua siswa di kelas ini akan terpengaruh.');">
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
                    <td colspan="6" class="text-center text-muted">Data tidak ditemukan. Sesuaikan filter Anda.</td>
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