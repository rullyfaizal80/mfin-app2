@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            <div class="card shadow-sm border-0 mb-4 bg-body">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-building me-2"></i> {{ $page_title }}</h5>
                </div>
                <div class="card-body">
                    
                    {{-- Form Filter --}}
                    <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded border">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Sekolah / Unit</label>
                            <select id="f_school" class="form-select form-select-sm">
                                <option value="-">- Semua Unit -</option>
                                @foreach($schools as $sch)
                                    <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Tahun Ajaran</label>
                            <select id="f_cyear" class="form-select form-select-sm">
                                <option value="-">- Semua Tahun -</option>
                                @foreach($cyears as $yr)
                                    {{-- Tambahkan pengecekan selected di bawah ini --}}
                                    <option value="{{ $yr->id }}" {{ $yr->id == $active_year_id ? 'selected' : '' }}>
                                        {{ $yr->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Tingkat</label>
                            <select id="f_grade" class="form-select form-select-sm">
                                <option value="-">- Semua Tingkat -</option>
                                @foreach($grades as $gr)
                                    <option value="{{ $gr->title }}">{{ $gr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Tipe Kelas</label>
                            <select id="f_type" class="form-select form-select-sm">
                                <option value="-">- Semua Tipe -</option>
                                @foreach($types as $ty)
                                    <option value="{{ $ty->title }}">{{ $ty->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-filter" class="btn btn-sm btn-primary w-100 fw-bold">
                                <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="table-responsive">
                        <table id="table-clist" class="table table-hover table-striped align-middle border" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Nama Kelas</th>
                                    <th width="10%" class="text-center">Tingkat</th>
                                    <th width="15%" class="text-center">Tipe</th>
                                    <th width="20%">Sekolah / Unit</th>
                                    <th width="15%" class="text-center">Tahun Ajaran</th>
                                    <th width="20%" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh DataTables AJAX --}}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // Inisialisasi DataTables Server-Side
    let table = $('#table-clist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('fincom.class_list.ajax') }}",
            "type": "POST",
            "data": function (d) {
                d._token   = "{{ csrf_token() }}";
                d.f_school = $('#f_school').val();
                d.f_cyear  = $('#f_cyear').val();
                d.f_grade  = $('#f_grade').val();
                d.f_type   = $('#f_type').val();
            }
        },
        "columns": [
            { "data": 0 }, 
            { "data": 1, "className": "text-center" }, 
            { "data": 2, "className": "text-center" }, 
            { "data": 3 }, 
            { "data": 4, "className": "text-center" }, 
            { "data": 5, "className": "text-center", "orderable": false } // Tombol Aksi
        ],
        "order": [[0, 'asc']],
        "language": {
            "search": "Cari Kelas / Sekolah:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div> Memuat data..."
        }
    });

    // Pemicu tombol filter (me-reload tabel secara AJAX)
    $('#btn-filter').on('click', function() {
        table.ajax.reload();
    });

});
</script>
@endpush