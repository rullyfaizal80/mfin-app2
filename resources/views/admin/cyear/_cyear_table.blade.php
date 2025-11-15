<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">Tahun Ajaran</th>
                    <th scope="col">Mulai</th>
                    <th scope="col">Selesai</th>
                    <th scope="col">Catatan</th>
                    <th scope="col">Aktif</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $cyear)
                <tr>
                    <td>{{ $cyear->title }}</td>
                    <td>{{ \Carbon\Carbon::parse($cyear->date_start)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($cyear->date_end)->format('d M Y') }}</td>
                    <td>{{ $cyear->note }}</td>
                    <td>
                        @if($cyear->is_active == 'yes')
                            <span class="badge text-bg-success">Ya</span>
                        @else
                            <span class="badge text-bg-secondary">Tidak</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('cyear.edit', $cyear->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('cyear.destroy', $cyear->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Data yang terikat bisa jadi bermasalah.');">
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
@if ($list_data->hasPages())
<div class="card-footer bg-body-tertiary">
    {{ $list_data->links() }}
</div>
@endif