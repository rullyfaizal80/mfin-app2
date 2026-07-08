@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            <div class="card shadow-sm border-0 mb-4 bg-body-tertiary">
                <div class="card-header bg-primary-subtle text-primary-emphasis border-bottom border-primary-subtle py-3">
    <h5 class="card-title mb-0"><i class="bi bi-building me-2"></i> {{ $page_title }}</h5>
</div>
                <div class="card-body pt-4">
                    
                    {{-- Form Filter --}}
                    <div class="row g-3 align-items-end mb-4 bg-body p-3 rounded border border-secondary-subtle shadow-sm">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-secondary">Sekolah / Unit</label>
                            <select id="f_school" class="form-select form-select-sm border-secondary-subtle">
                                <option value="-">- Semua Unit -</option>
                                @foreach($schools as $sch) <option value="{{ $sch->id }}">{{ $sch->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-body-secondary">Tahun Ajaran</label>
                            <select id="f_cyear" class="form-select form-select-sm border-secondary-subtle">
                                <option value="-">- Semua Tahun -</option>
                                @foreach($cyears as $yr) <option value="{{ $yr->id }}" {{ $yr->id == $active_year_id ? 'selected' : '' }}>{{ $yr->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-body-secondary">Tingkat</label>
                            <select id="f_grade" class="form-select form-select-sm border-secondary-subtle">
                                <option value="-">- Semua Tingkat -</option>
                                @foreach($grades as $gr) <option value="{{ $gr->title }}">{{ $gr->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-secondary">Tipe Kelas</label>
                            <select id="f_type" class="form-select form-select-sm border-secondary-subtle">
                                <option value="-">- Semua Tipe -</option>
                                @foreach($types as $ty) <option value="{{ $ty->title }}">{{ $ty->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-filter" class="btn btn-sm btn-outline-primary w-100 fw-bold shadow-sm">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="table-responsive">
                        <table id="table-clist" class="table table-hover table-striped align-middle border-secondary-subtle" style="width:100%">
                            <thead class="table-group-divider">
                                <tr>
                                    <th class="bg-body-tertiary text-body" width="20%">Nama Kelas</th>
                                    <th class="bg-body-tertiary text-body text-center" width="10%">Tingkat</th>
                                    <th class="bg-body-tertiary text-body text-center" width="15%">Tipe</th>
                                    <th class="bg-body-tertiary text-body" width="20%">Sekolah / Unit</th>
                                    <th class="bg-body-tertiary text-body text-center" width="15%">Tahun Ajaran</th>
                                    <th class="bg-body-tertiary text-body text-center" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody class="small text-body">
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

@push('styles')
<style>
    /* Dukungan Mode Gelap untuk Navigasi Halaman DataTables */
    [data-bs-theme="dark"] .page-item .page-link {
        background-color: var(--bs-body-bg);
        border-color: var(--bs-border-color);
        color: var(--bs-body-color);
    }
    [data-bs-theme="dark"] .page-item.active .page-link {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }
    [data-bs-theme="dark"] .page-item.disabled .page-link {
        background-color: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
        color: var(--bs-secondary-color);
    }
</style>
@endpush

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