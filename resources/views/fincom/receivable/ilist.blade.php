@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4 bg-body-tertiary">
                
                {{-- Header Card --}}
                <div class="card-header bg-primary-subtle text-primary-emphasis border-bottom border-primary-subtle py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i> {{ $page_title }}</h5>
                </div>

                <div class="card-body">

                    {{-- Notifikasi Sukses / Pesan --}}
@if(session('message'))
    <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Notifikasi Gagal / Error --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
                    {{-- Tabel Responsive --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-secondary-subtle" id="table-ilist" style="width:100%">
                           {{-- Ganti Bagian Thead Tabel HTML --}}
<thead class="table-group-divider text-nowrap">
    <tr>
        <th class="bg-body-tertiary" width="50">No</th>
        <th class="bg-body-tertiary">Ref No</th>
        <th class="bg-body-tertiary">Tanggal</th>
        <th class="bg-body-tertiary">Name</th>
        <th class="bg-body-tertiary">Piutang</th>
        <th class="bg-body-tertiary text-end">Nilai</th>
        <th class="bg-body-tertiary">Catatan</th>
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
<script type="text/javascript">
$(document).ready(function() {
    $('#table-ilist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('fincom.receivable.ilist.ajax') }}",
            "type": "POST",
            "data": function (d) {
                d._token = "{{ csrf_token() }}";
            },
            "error": function (xhr, error, thrown) {
                console.error(xhr.responseText);
                alert("Terjadi kesalahan sistem. Silakan cek tab Console / Network.");
            }
        },
        "columns": [
            { "data": 0, "className": "text-center" },        // No
            { "data": 1 },                                    // Ref No
            { "data": 2 },                                    // Tanggal
            { "data": 3 },                                    // Name
            { "data": 4 },                                    // Piutang
            { "data": 5, "className": "text-end fw-medium" }, // Nilai
            { "data": 6 },                                    // Catatan
            { "data": 7, "className": "text-center", "orderable": false } // Aksi
        ],
        "order": [[1, 'desc']], 
        "language": {
            "search": "Filter Ref No & Nama Lengkap:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>",
            "emptyTable": "Loading data from server" // Disamakan dengan CI lama
        }
    });
});
</script>
@endpush
@endsection