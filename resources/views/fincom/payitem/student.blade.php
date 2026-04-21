@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama Anda --}}

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            {{-- Alert Success --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Setting Komponen Satuan Siswa</h3>
                </div>
                
                <div class="card-body">
                    {{-- FORM INPUT (Sebelah Atas/Kiri) --}}
                    <form action="{{ route('fincom.payitem.student.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payitem_id" id="payitem_id" value="{{ $payitem_id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Kode Komponen <span class="text-danger">*</span></label>
                                    <input type="text" name="payitem_code" class="form-control" value="{{ $details->payitem_code ?? '' }}" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Nama Komponen / Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ $details->title ?? '' }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label>Tipe Tagihan</label>
                                    <select name="payitem_type" class="form-control">
                                        <option value="add" {{ ($details->payitem_type ?? '') == 'add' ? 'selected' : '' }}>PENAMBAH (ADD)</option>
                                        <option value="sub" {{ ($details->payitem_type ?? '') == 'sub' ? 'selected' : '' }}>PENGURANG (SUB)</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Nominal Standar</label>
                                    <input type="text" name="payvalue" id="payvalue" class="form-control text-end" value="{{ number_format($details->payvalue ?? 0, 0, ',', '.') }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label>Urutan (Ordering)</label>
                                    <input type="number" name="ordering" class="form-control" value="{{ $details->ordering ?? 1 }}">
                                </div>
                            </div>

                            <div class="col-md-6 border-start">
                                <h5>Setting Akun (COA)</h5>
                                <div class="form-group mb-2">
                                    <label>Kas (Cash)</label>
                                    <select name="coa_cash" class="form-control select2">
                                        <option value="0">-- Pilih Akun --</option>
                                        @foreach($coa_cash as $coa)
                                            <option value="{{ $coa->id }}" {{ ($details->coa_cash ?? 0) == $coa->id ? 'selected' : '' }}>[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Piutang (Receivable)</label>
                                    <select name="coa_receivable" class="form-control select2">
                                        <option value="0">-- Pilih Akun --</option>
                                        @foreach($coa_receivable as $coa)
                                            <option value="{{ $coa->id }}" {{ ($details->coa_receivable ?? 0) == $coa->id ? 'selected' : '' }}>[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Hutang (Payable)</label>
                                    <select name="coa_payable" class="form-control select2">
                                        <option value="0">-- Pilih Akun --</option>
                                        @foreach($coa_payable as $coa)
                                            <option value="{{ $coa->id }}" {{ ($details->coa_payable ?? 0) == $coa->id ? 'selected' : '' }}>[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Pendapatan (Revenue)</label>
                                    <select name="coa_revenue" class="form-control select2">
                                        <option value="0">-- Pilih Akun --</option>
                                        @foreach($coa_revenue as $coa)
                                            <option value="{{ $coa->id }}" {{ ($details->coa_revenue ?? 0) == $coa->id ? 'selected' : '' }}>[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Biaya (Cost)</label>
                                    <select name="coa_cost" class="form-control select2">
                                        <option value="0">-- Pilih Akun --</option>
                                        @foreach($coa_cost as $coa)
                                            <option value="{{ $coa->id }}" {{ ($details->coa_cost ?? 0) == $coa->id ? 'selected' : '' }}>[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center border-top pt-3">
                            @if(!$details)
                                <button type="submit" name="add_button" class="btn btn-success"><i class="bi bi-plus-lg"></i> Tambah Baru</button>
                            @else
                                <button type="submit" name="save_button" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                                <a href="{{ route('fincom.payitem.student') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" name="sync_button" class="btn btn-warning" onclick="return confirm('Yakin akan men-singkron data akun ke semua siswa?')">
                                    <i class="bi bi-arrow-repeat"></i> Sinkron Akun ke Siswa
                                </button>
                            @endif
                        </div>
                    </form>

                    <hr class="my-5">

                    {{-- TABEL DATA (DataTables) --}}
                    <div class="table-responsive">
                        <table id="table-payitem" class="table table-bordered table-striped w-100">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Komponen</th>
                                    <th>Tipe</th>
                                    <th>Nominal</th>
                                    <th>Akun COA</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Inisialisasi DataTables AJAX
    $('#table-payitem').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('fincom.payitem.ax_get_student') }}",
            type: "POST",
            data: function (d) {
                d._token = "{{ csrf_token() }}";
            }
        },
        columns: [
            { class: "text-center" },
            { class: "text-start" },
            { class: "text-center" },
            { class: "text-end" },
            { class: "text-start", style: "font-size: 0.85rem;" },
            { class: "text-center", orderable: false }
        ],
        language: {
            search: "Filter Kode/Nama: "
        }
    });

    // 2. Auto Format Rupiah untuk input nominal
    $('#payvalue').on('keyup', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(new Intl.NumberFormat('id-ID').format(val));
    });
});
</script>
@endpush