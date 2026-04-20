@extends('layouts.app')

@section('title', $page_title . ' | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">{{ $page_title }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" onclick="addPeriod()">
            <i class="bi bi-plus-circle"></i> Tambah Periode Baru
        </button>
    </div>
</div>

{{-- ALERT PESAN --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- FILTER TAHUN --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('fincom.period.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Filter Tahun</label>
                <input type="number" name="fyear" class="form-control text-center" value="{{ $year }}" maxlength="4">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- TABEL DATA --}}
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-hover w-100 datatable">
            <thead class="table-dark text-center align-middle">
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Mulai</th>
                    <th width="15%">Sampai</th>
                    <th width="10%">Untuk</th>
                    <th>Keterangan</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d M Y', strtotime($row->period_start)) }}</td>
                    <td class="text-center">{{ date('d M Y', strtotime($row->period_end)) }}</td>
                    <td class="text-center">
                        @if($row->ttype == 'student')
                            <span class="badge bg-info text-dark">Siswa</span>
                        @else
                            <span class="badge bg-success">Guru</span>
                        @endif
                    </td>
                    <td>{{ $row->note }}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            {{-- Daftar Proses (Placeholder URL, sesuaikan jika route batch sudah ada) --}}
                            <a href="#" class="btn btn-sm btn-outline-primary" title="Daftar Proses">
                                <i class="bi bi-card-checklist"></i> Proses
                            </a>
                            
                            {{-- Tombol Edit (Kirim data ke Modal) --}}
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editPeriod({{ $row->id }}, '{{ $row->period_start }}', '{{ $row->period_end }}', '{{ $row->ttype }}', '{{ addslashes($row->note) }}')">
                                <i class="bi bi-pencil"></i>
                            </button>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('fincom.period.destroy', $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus periode ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL CREATE & EDIT PERIODE --}}
<div class="modal fade" id="periodModal" tabindex="-1" aria-labelledby="periodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="periodForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header text-white" id="modalHeader" style="background-color: #0d6efd;">
                    <h5 class="modal-title" id="periodModalLabel">Tambah Periode Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="date" name="period_start" id="period_start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Sampai</label>
                            <input type="date" name="period_end" id="period_end" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Tipe / Untuk</label>
                            <select name="ttype" id="ttype" class="form-select" required>
                                <option value="student">Siswa</option>
                                <option value="teacher">Guru</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Keterangan (Opsional)</label>
                            <textarea name="note" id="note" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Inisialisasi DataTables bawaan template Anda
    $(document).ready(function() {
        $('.datatable').DataTable({
            "language": {
                "search": "Cari Keterangan:",
                "emptyTable": "Tidak ada data periode di tahun ini"
            }
        });
    });

    // Fungsi untuk membuka Modal Tambah
    function addPeriod() {
        $('#periodForm').attr('action', "{{ route('fincom.period.store') }}");
        $('#formMethod').val('POST');
        $('#periodModalLabel').text('Tambah Periode Baru');
        $('#modalHeader').css('background-color', '#0d6efd'); // Warna Biru
        $('#btnSubmit').text('Simpan Periode');
        
        // Kosongkan form
        $('#period_start').val("{{ date('Y-m-01') }}"); // Default awal bulan ini
        $('#period_end').val("{{ date('Y-m-t') }}");   // Default akhir bulan ini
        $('#ttype').val('student');
        $('#note').val('');
        
        $('#periodModal').modal('show');
    }

    // Fungsi untuk membuka Modal Edit
    function editPeriod(id, start, end, type, note) {
        let url = "{{ route('fincom.period.update', ':id') }}".replace(':id', id);
        
        $('#periodForm').attr('action', url);
        $('#formMethod').val('PUT'); // Wajib PUT untuk update di Laravel
        $('#periodModalLabel').text('Edit Periode');
        $('#modalHeader').css('background-color', '#ffc107'); // Warna Kuning/Warning
        $('#btnSubmit').text('Update Periode');
        
        // Isi form dengan data lama
        $('#period_start').val(start);
        $('#period_end').val(end);
        $('#ttype').val(type);
        $('#note').val(note);
        
        $('#periodModal').modal('show');
    }
</script>
@endsection