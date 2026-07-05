@extends('layouts.app') {{-- Pastikan layout utama Anda benar --}}

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4 bg-body">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-people-fill me-2"></i> {{ $page_title }}</h5>
                </div>
                <div class="card-body">
                    {{-- Form Filter --}}
                    <form action="{{ route('fincom.student_list.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label for="fclass_list" class="form-label small fw-bold">Pilih Kelas</label>
                            <select name="f_class_list" id="f_class_list" class="form-control" data-url="{{ url('fincom/student_list') }}">
                                <option value="0">- Semua Kelas -</option>
                                
                                {{-- Gunakan $dk dari Controller dan sintaks Blade @foreach --}}
                                @foreach($dk as $p)
                                    <option value="{{ $p->id }}" {{ $class_list_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->kelas }} / {{ $p->tahun }}
                                    </option>
                                @endforeach
                                
                            </select>
                        </div>
                        
                    </form>

                    <hr class="my-4 opacity-25">

                    {{-- Tabel Responsive --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border" id="table-student" style="width:100%">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th width="50">No</th>
                                    <th>NIS</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas</th>
                                    <th>Tgl Lahir</th>
                                    <th width="50">L/P</th>
                                    <th>Kontak</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="small">
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
    /* Dukungan Mode Gelap untuk DataTables */
    [data-bs-theme="dark"] .table-light {
        --bs-table-bg: #2b3035;
        --bs-table-color: #fff;
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
// Tunggu sampai elemen HTML dimuat semua
    document.addEventListener('DOMContentLoaded', function() {
        
        const selectClass = document.getElementById('f_class_list');
        
        if (selectClass) {
            selectClass.addEventListener('change', function() {
                // Ambil base url dari atribut data-url
                const baseUrl = this.getAttribute('data-url');
                // Lakukan redirect
                window.location.href = baseUrl + '/' + this.value;
            });
        }
        
    });


$(document).ready(function() {
    var class_id = "{{ $class_list_id }}";
    
    // Pastikan URL terbentuk dengan benar (menangani jika class_id adalah 0)
    var ajaxUrl = "{{ route('fincom.student_list.ajax', ':id') }}";
    ajaxUrl = ajaxUrl.replace(':id', class_id);

    $('#table-student').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajaxUrl,
            "type": "POST",
            "data": function (d) {
                d._token = "{{ csrf_token() }}"; // Proteksi CSRF Laravel
            },
            "error": function (xhr, error, thrown) {
                // Jika error, kita bisa melihat detailnya di console F12
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
        "order": [[2, 'asc']], // Default urut berdasarkan Nama (Kolom ke-3)
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