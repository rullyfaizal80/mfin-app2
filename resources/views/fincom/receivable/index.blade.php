@extends('layouts.app')

@section('content')

{{-- Bagian Alert Notifikasi --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4 bg-body-tertiary">
                
                {{-- Header Card --}}
                <div class="card-header bg-primary-subtle text-primary-emphasis border-bottom border-primary-subtle py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-wallet2 me-2"></i> {{ $page_title }}</h5>
                </div>

                <div class="card-body">
                    {{-- Form Filter --}}
                    <form action="{{ route('fincom.receivable.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                        
                        <div class="col-md-4">
                            <label for="fpayitem_list" class="form-label small fw-bold text-body">Jenis Piutang</label>
                            <select name="fpayitem_list" id="fpayitem_list" class="form-select border-secondary-subtle">
                                <option value="all">- Semua -</option>
                                @foreach($ptype as $p)
                                    <option value="{{ $p->id }}" {{ $payitem_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="fschool" class="form-label small fw-bold text-body">Unit / Sekolah</label>
                            <select name="fschool" id="fschool" class="form-select border-secondary-subtle">
                                <option value="all">- Pilih Sekolah -</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->school_id }}" {{ $school_id == $u->school_id ? 'selected' : '' }}>
                                        {{ $u->school }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('fincom.receivable.reportall', ['payitem_id' => $payitem_id, 'school_id' => $school_id]) }}" target="_blank" class="btn btn-secondary">
                                <i class="bi bi-printer"></i> Print
                            </a>
                        </div>
                    </form>

                    <hr class="my-4 border-secondary-subtle">

                    {{-- Tabel Responsive --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-secondary-subtle" id="table-receivable" style="width:100%">
                            <thead class="table-group-divider text-nowrap">
                                <tr>
                                    <th class="bg-body-tertiary" width="50">No</th>
                                    <th class="bg-body-tertiary">NIS</th>
                                    <th class="bg-body-tertiary">Nama Lengkap</th>
                                    <th class="bg-body-tertiary">Kelas</th>
                                    <th class="bg-body-tertiary">Jenis Piutang</th>
                                    <th class="bg-body-tertiary text-end">Nilai Piutang</th>
                                    <th class="bg-body-tertiary text-center" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="small text-body">
                                {{-- Data akan diisi oleh DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Dukungan Dark Mode Bootstrap 5 untuk DataTables */
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
{{-- DataTables JS --}}
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('#table-receivable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('fincom.receivable.ajax') }}",
            "type": "POST",
            "data": function (d) {
                // Menambahkan CSRF dan parameter filter ke request AJAX
                d._token = "{{ csrf_token() }}";
                d.payitem_id = "{{ $payitem_id }}";
                d.school_id = "{{ $school_id }}";
            },
            "error": function (xhr, error, thrown) {
                console.error("XHR Response: ", xhr.responseText);
                alert('Gagal mengambil data dari server.');
            }
        },
        "columns": [
            { "data": 0, "className": "text-center" }, // No
            { "data": 1 }, // NIS
            { "data": 2 }, // Nama
            { "data": 3, "className": "text-center" }, // Kelas
            { "data": 4, "className": "text-center" }, // Jenis
            { "data": 5, "className": "text-end fw-medium" }, // Nilai Piutang
            { "data": 6, "className": "text-center", "orderable": false } // Aksi
        ],
        "order": [[2, 'asc']], // Default urut berdasarkan Nama Lengkap
        "language": {
            "search": "Cari NIS / Nama:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>",
            "emptyTable": "Tidak ada data piutang yang ditemukan",
            "zeroRecords": "Tidak ditemukan data yang sesuai dengan filter/pencarian"
        }
    });
});
</script>
@endpush
@endsection