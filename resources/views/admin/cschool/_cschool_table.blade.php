<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Kepala Sekolah</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $school)
                <tr>
                    <td>{{ $school->name }}</td>
                    {{-- Replikasi format alamat dari kode lama --}}
                    <td>
                        {{ $school->street }}<br>
                        {{ $school->city }} - {{ $school->postalcode }}<br>
                        {{ $school->telephone }} / {{ $school->fax }}<br>
                        Email: {{ $school->email }}, Website: {{ $school->website }}
                    </td>
                    <td>{{ $school->headmaster_name }}</td>
                    <td class="text-center">
                        <a href="{{ route('cschool.edit', $school->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('cschool.destroy', $school->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Data yang terikat bisa jadi bermasalah.');">
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
@if ($list_data->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $list_data->links() }}
</div>
@endif