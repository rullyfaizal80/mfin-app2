@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header border-bottom-0 pt-4 pb-0">
            <h5 class="mb-0 fw-bold text-danger">{{ $page_title }}</h5>
            <hr class="border-danger border-2 opacity-100 mb-0 mt-3">
        </div>
        
        <div class="card-body">
            <!-- Form Filter Kelas -->
            <form id="filterForm" class="mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="fclass_list" class="col-form-label">Kelas</label>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <select name="fclass_list" id="fclass_list" class="form-select form-select-sm">
                            @foreach($classes as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $class_id ? 'selected' : '' }}>
                                    {{ sprintf("%s / %s / %s", $p->title, $p->subject, $p->y_title) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-light border shadow-sm px-3">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabel Data -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle w-100" id="table-student">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="15%">NIS</th>
                            <th width="25%">Nama</th>
                            <th width="15%" class="text-center">Tanggal Lahir</th>
                            <th width="10%" class="text-center">Jenis Kelamin</th>
                            <th width="15%" class="text-end">Nilai Piutang</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Letakkan script langsung di dalam content agar PASTI ter-load -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    console.log("DataTables JS Berhasil Dimuat!");

    var oTable = $('#table-student').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('fincom.receivable.report.ajax') }}",
            "type": "POST",
            "data": function (d) {
                d._token = "{{ csrf_token() }}";
                d.class_id = $('#fclass_list').val(); 
            }
        },
        "columns": [
            { "data": 0, "className": "text-center", "orderable": false, "searchable": false },
            { "data": 1, "className": "text-start" },
            { "data": 2, "className": "text-start" },
            { "data": 3, "className": "text-center" },
            { "data": 4, "className": "text-center" },
            { "data": 5, "className": "text-end fw-medium" },
            { "data": 6, "className": "text-center", "orderable": false, "searchable": false }
        ],
        "language": {
            "search": "Filter NIS & Nama Lengkap: ",
            "lengthMenu": "Show _MENU_ entries",
            "emptyTable": "Tidak ada data tersedia",
            "processing": "Mengambil data..."
        },
        "order": [[2, 'asc']],
        "pagingType": "full_numbers"
    });

    // Aksi Klik Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault(); 
        oTable.draw(); 
    });
});
</script>
@endsection