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
                
                {{-- Warna biru diubah menjadi seragam dengan view sebelumnya --}}
                <div class="card-header bg-primary-subtle text-primary-emphasis border-bottom border-primary-subtle py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-people-fill me-2"></i> {{ $page_title }}</h5>
                </div>

                <div class="card-body">
                    {{-- Form Filter --}}
                    <form action="{{ route('fincom.student_list.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label for="fclass_list" class="form-label small fw-bold text-body">Pilih Kelas</label>
                            <select name="f_class_list" id="f_class_list" class="form-select border-secondary-subtle" data-url="{{ url('fincom/student_list') }}">
                                <option value="0">- Semua Kelas -</option>
                                @foreach($dk as $p) <option value="{{ $p->id }}" {{ $class_list_id == $p->id ? 'selected' : '' }}>{{ $p->kelas }} / {{ $p->tahun }}</option> @endforeach
                            </select>
                        </div>
                    </form>

                    <hr class="my-4 border-secondary-subtle">

                    {{-- Tabel Responsive --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-secondary-subtle" id="table-student" style="width:100%">
                            <thead class="table-group-divider text-nowrap">
                                <tr>
                                    <th class="bg-body-tertiary" width="50">No</th>
                                    <th class="bg-body-tertiary">NIS</th>
                                    <th class="bg-body-tertiary">Nama Lengkap</th>
                                    <th class="bg-body-tertiary">Kelas</th>
                                    <th class="bg-body-tertiary">Tgl Lahir</th>
                                    <th class="bg-body-tertiary" width="50">L/P</th>
                                    <th class="bg-body-tertiary">Kontak</th>
                                    <th class="bg-body-tertiary text-center">Aksi</th>
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
    /* Menggunakan variabel Bootstrap bawaan agar warna pagination DataTables juga menyesuaikan mode gelap */
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
    
    .page-table-col-center { text-align: center; }
    .page-table-col-left { text-align: left; }
</style>
@endpush

@push('scripts')
{{-- DataTables JS --}}
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const selectClass = document.getElementById('f_class_list');
        if (selectClass) {
            selectClass.addEventListener('change', function() {
                const baseUrl = this.getAttribute('data-url');
                window.location.href = baseUrl + '/' + this.value;
            });
        }
    });

$(document).ready(function() {
    var class_id = "{{ $class_list_id }}";
    
    var ajaxUrl = "{{ route('fincom.student_list.ajax', ':id') }}";
    ajaxUrl = ajaxUrl.replace(':id', class_id);

    $('#table-student').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajaxUrl,
            "type": "POST",
            "data": function (d) {
                d._token = "{{ csrf_token() }}";
            },
            "error": function (xhr, error, thrown) {
                console.error("XHR Response: ", xhr.responseText);
                alert('Gagal mengambil data. Pastikan route dan controller sudah sesuai.');
            }
        },
        "columns": [
            { "data": 0, "className": "text-center" }, // No
            { "data": 1 }, // NIS
            { "data": 2 }, // Nama
            { "data": 3 }, // Kelas
            { "data": 4, "className": "text-center" }, // Tgl Lahir
            { "data": 5, "className": "text-center" }, // L/P
            { "data": 6 }, // Kontak
            { "data": 7, "className": "text-center", "orderable": false } // Aksi
        ],
        "order": [[2, 'asc']], // Default urut berdasarkan Nama
        "language": {
            "search": "Cari NIS / Nama:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>"
        }
    });
});
</script>
@endpush
@endsection