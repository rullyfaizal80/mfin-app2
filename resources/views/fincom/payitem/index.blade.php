@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Komponen Satuan Siswa</h3>
            <div class="card-tools">
                <a href="{{ route('fincom.payitem.student.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table id="table-payitem" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Komponen</th>
                        <th>Tipe</th>
                        <th>Nominal</th>
                        <th>Akun COA</th>
                        <th width="100px">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#table-payitem').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: "{{ route('fincom.payitem.student.data') }}", 
            type: "POST", 
            // PERBAIKAN DI BARIS INI: Gunakan function(d)
            data: function (d) {
                d._token = "{{ csrf_token() }}";
            }
        },
        columns: [
            { className: "text-center" }, 
            null, 
            { className: "text-center" }, 
            { className: "text-end" }, 
            null, 
            { className: "text-center", orderable: false }
        ]
    });
});
</script>
@endpush