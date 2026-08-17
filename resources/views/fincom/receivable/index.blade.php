@extends('layouts.app') {{-- Pastikan layout Anda memuat CSS/JS Bootstrap 5 & DataTables --}}

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">{{ $page_title }}</h5>
        </div>
        
        <div class="card-body">
            
            {{-- Alert Notifikasi & Error (Opsional) --}}
            @if(session('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

           <!-- Form Filter Pencarian -->
<form id="filterForm" class="mb-4">
    <div class="row g-3 align-items-end bg-body-tertiary p-3 rounded border">
        
        <!-- Filter Jenis Piutang -->
        <div class="col-md-4">
            <label for="fpayitem_list" class="form-label fw-semibold">Jenis Piutang</label>
            <select name="fpayitem_list" id="fpayitem_list" class="form-select">
                <option value="all"> - Semua - </option>
                @foreach($ptype as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $payitem_id ? 'selected' : '' }}>
                        {{ $p->title }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Filter Unit / Sekolah -->
        <div class="col-md-4">
            <label for="fschool" class="form-label fw-semibold">Unit / Sekolah</label>
            <select name="fschool" id="fschool" class="form-select">
                <option value="all"> - Pilih Sekolah - </option>
                @foreach($units as $u)
                    <option value="{{ $u->school_id }}" {{ $u->school_id == $school_id ? 'selected' : '' }}>
                        {{ $u->school }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Tombol Aksi -->
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" id="filter_btn" class="btn btn-primary px-4">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ url('fincom/receivable/reportall/'.$payitem_id.'/'.$school_id) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer"></i> Print
            </a>
        </div>
        
    </div>
</form>

            <hr class="my-4">

            <!-- Tabel Data Piutang (DataTables) -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle w-100" id="table-student">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="12%">NIS</th>
                            <th width="23%">Nama</th>
                            <th width="10%" class="text-center">Kls</th>
                            <th width="15%" class="text-center">Jenis</th>
                            <th width="20%" class="text-end">Nilai Piutang</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables akan memuat data di sini --}}
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // Inisialisasi DataTables meniru gaya ilist.blade.php
    var oTable = $('#table-student').DataTable({
        "processing": true,
        "serverSide": true,
        "deferLoading": 0, // <-- Tetap dipertahankan agar tidak otomatis meload saat halaman dibuka pertama kali
        "ajax": {
            // Pastikan nama rute sesuai dengan grup yang sudah kita buat ('fincom.receivable.ajax')
            "url": "{{ route('fincom.receivable.ajax') }}",
            "type": "POST",
            "data": function (d) {
                // Mengambil referensi dari ilist: kirim token eksplisit
                d._token = "{{ csrf_token() }}";
                
                // Ambil nilai filter untuk dikirim ke Controller
                d.payitem_id = $('#fpayitem_list').val();
                d.school_id = $('#fschool').val();
            },
            "error": function (xhr, error, thrown) {
                console.error("Detail Error Server:", xhr.responseText);
                alert("Terjadi kesalahan sistem saat memuat tabel. Silakan cek tab Console / Network.");
            }
        },
        "columns": [
            { "data": 0, "className": "text-center", "orderable": false, "searchable": false }, // No
            { "data": 1, "className": "text-start" },   // NIS
            { "data": 2, "className": "text-start" },   // Nama
            { "data": 3, "className": "text-center" },  // Kls
            { "data": 4, "className": "text-center" },  // Jenis
            { "data": 5, "className": "text-end fw-medium" }, // Nilai Piutang (meniru fw-medium dari ilist)
            { "data": 6, "className": "text-center", "orderable": false, "searchable": false } // Tombol Aksi
        ],
        "language": {
            "search": "Filter NIS & Nama Lengkap:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>",
            "emptyTable": "No data available in table",
            "infoEmpty": "Showing 0 to 0 of 0 entries"
        },
        "order": [[2, 'asc']], // Default urut berdasarkan Nama (index ke-2)
        "pagingType": "full_numbers",
        "searchDelay": 1000
    });

    // Memicu pencarian DataTables saat form filter disubmit
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        oTable.draw();
    });
});
</script>
@endpush
@endsection