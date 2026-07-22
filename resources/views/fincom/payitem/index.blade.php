@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Komponen Satuan Siswa</h3>
            <div class="card-tools">
                <!-- TOMBOL BERUBAH MENJADI PEMICU MODAL -->
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPayitem">
                    <i class="bi bi-plus-lg"></i> Tambah Baru
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <table id="table-payitem" class="table table-bordered table-striped w-100">
                <thead class="table-dark text-center">
                    <tr>
                        <th>KODE</th>
                        <th>NAMA</th>
                        <th>TIPE</th>
                        <th>NILAI</th>
                        <th>AKUN</th>
                        <th width="100px">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ========================= -->
<!-- MODAL TAMBAH DATA COMPONENT -->
<!-- ========================= -->
<div class="modal fade" id="modalPayitem" tabindex="-1" aria-labelledby="modalPayitemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPayitemLabel">Tambah Komponen Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('fincom.payitem.student.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- KOLOM KIRI (Detail Komponen) -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Kode<span class="text-danger">*</span></label>
                                <input type="text" name="payitem_code" class="form-control" required>
                                
                                {{-- KOTAK INFORMASI KODE STANDAR --}}
                                <div class="mt-2 p-2 bg-light border rounded text-muted" style="font-size: 0.85rem;">
                                    <strong class="text-dark">Keterangan Kode Standar:</strong>
                                    <ul class="mb-0 ps-3 mt-1">
                                        <li><strong>GKASPENDAPATAN</strong> : Kas - Piutang - Pendapatan</li>
                                        <li><strong>GKASHUTANG</strong> : Kas - Piutang - Hutang</li>
                                        <li><strong>GTABUNGAN</strong> : Kas - Hutang</li>
                                        <li><strong>GKASPIUTANG</strong> : Kas - Piutang</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Nama</label>
                                <input type="text" name="title" class="form-control">
                            </div>

                            <!-- DROPDOWN TIPE YANG SUDAH DIPERBARUI -->
                            <div class="form-group mb-3">
                                <label class="fw-bold">Tipe</label>
                                <select name="payitem_type" class="form-control">
                                    <option value="tuition">tuition</option>
                                    <option value="salary">salary</option>
                                    <option value="expense">expense</option>
                                    <option value="income">income</option>
                                    <option value="loan">loan</option>
                                    <option value="saving">saving</option>
                                    <option value="deposit">deposit</option>
                                    <option value="natura">natura</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Urutan</label>
                                <input type="number" name="ordering" class="form-control" value="1">
                            </div>
                        </div>

                        <!-- KOLOM KANAN (Setting Akun) -->
                        <div class="col-md-6 border-start">
                                                       
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Kas</label>
                                <select name="coa_cash" class="form-control select2" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_cash as $coa)
                                        <!-- PERBAIKAN DI SINI: value menggunakan coa_code -->
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Hutang</label>
                                <select name="coa_payable" class="form-control select2" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_payable as $coa)
                                        <!-- PERBAIKAN DI SINI -->
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Biaya</label>
                                <select name="coa_cost" class="form-control select2" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_cost as $coa)
                                        <!-- PERBAIKAN DI SINI -->
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Piutang</label>
                                <select name="coa_receivable" class="form-control select2" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_receivable as $coa)
                                        <!-- PERBAIKAN DI SINI -->
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Pendapatan</label>
                                <select name="coa_revenue" class="form-control select2" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_revenue as $coa)
                                        <!-- PERBAIKAN DI SINI -->
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#table-payitem').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: "{{ route('fincom.payitem.student.data') }}", 
            type: "POST", 
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

    // Perbaikan Modal dengan Select2 (Jika Anda menggunakan Select2)
    // Pastikan dropdown select2 berjalan normal di dalam modal
    $('.select2').select2({
        dropdownParent: $('#modalPayitem')
    });
});
</script>
@endpush