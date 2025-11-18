<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-striped table-sm"> {{-- Tambah table-sm biar ringkas --}}
            <thead>
                <tr>
                    <th scope="col" style="width: 3%;">
                        <input class="form-check-input" type="checkbox" id="select-all-students">
                    </th>
                    <th scope="col">Nama Siswa</th>
                    {{-- [DIHAPUS] Kolom NIS --}}
                    <th scope="col">Umur</th>
                    <th scope="col">TTL</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $student)
                @php
                    $age_year = 0; $age_month = 0;
                    if ($student->dateofbirth && $student->dateofbirth != '0000-00-00') {
                        $dob = new \DateTime($student->dateofbirth);
                        $now = new \DateTime();
                        $diff = $now->diff($dob);
                        $age_year = $diff->y; $age_month = $diff->m;
                    }
                    $dob_formatted = $student->dateofbirth && $student->dateofbirth != '0000-00-00' 
                                     ? \Carbon\Carbon::parse($student->dateofbirth)->format('d M Y') : '-';
                @endphp
                <tr>
                    <td>
                        <input class="form-check-input" type="checkbox" name="student_ids[]" value="{{ $student->user_id }}">
                    </td>
                    <td>{{ $student->fullname }}</td>
                    {{-- [DIHAPUS] Kolom NIS --}}
                    <td>{{ $age_year }} Thn, {{ $age_month }} Bln</td>
                    <td>{{ $student->placeofbirth }}, {{ $dob_formatted }}</td>
                    <td>
                        @if($student->user_is_active == 'yes') {{-- Sudah diperbaiki --}}
                            <span class="badge text-bg-success">Aktif</span>
                        @else
                            <span class="badge text-bg-secondary">Non-Aktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('class_user.edit', ['class_list_id' => $class_list_id, 'class_user_id' => $student->class_user_id]) }}" class="btn btn-sm btn-outline-warning py-0 px-1" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('class_user.destroy', ['class_list_id' => $class_list_id, 'class_user_id' => $student->class_user_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa ini dari kelas?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{-- Pagination --}}
@if ($list_data->hasPages())
    <div class="card-footer bg-body-tertiary py-1">
        {{ $list_data->links() }}
    </div>
@endif