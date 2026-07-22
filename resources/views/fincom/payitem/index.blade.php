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
            
            <form action="{{ route('fincom.payitem.student.store') }}" method="POST" id="formAddPayitem">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- KOLOM KIRI (Detail Komponen) -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Kode<span class="text-danger">*</span></label>
                                <input type="text" name="payitem_code" class="form-control" required>
                                
                                {{-- KOTAK INFORMASI KODE STANDAR --}}
                               <div class="mt-2 p-2 info-box-code rounded" style="font-size: 0.85rem;">
    <strong class="text-body">Keterangan Kode Standar:</strong>
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
                                <!-- Pastikan name="title" -->
                                <input type="text" name="title" class="form-control" required>
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

                            <!-- TAMBAHKAN KODE NOMINAL DI SINI -->
                            <div class="form-group mb-3">
                                <label class="fw-bold">Nilai</label>
                                <input type="text" name="payvalue" id="payvalue" class="form-control text-end" value="0">
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

<!-- ========================= -->
<!-- MODAL EDIT DATA COMPONENT -->
<!-- ========================= -->
<div class="modal fade" id="modalEditPayitem" tabindex="-1" aria-labelledby="modalEditPayitemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="modalEditPayitemLabel">Edit Komponen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditPayitem" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <!-- KOLOM KIRI -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="payitem_code" id="edit_payitem_code" class="form-control" required>
                                
                                <div class="mt-2 p-2 info-box-code rounded" style="font-size: 0.85rem;">
    <strong class="text-body">Keterangan Kode Standar:</strong>
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
                                <input type="text" name="title" id="edit_title" class="form-control">
                            </div>

                            <div class="form-group mb-3">
                                <label class="fw-bold">Tipe</label>
                                <select name="payitem_type" id="edit_payitem_type" class="form-control">
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
                                <label class="fw-bold">Nilai</label>
                                <input type="text" name="payvalue" id="edit_payvalue" class="form-control text-end" value="0">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Urutan</label>
                                <input type="number" name="ordering" id="edit_ordering" class="form-control" value="1">
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="col-md-6 border-start">
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Kas</label>
                                <select name="coa_cash" id="edit_coa_cash" class="form-control select2-edit" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_cash as $coa)
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Hutang</label>
                                <select name="coa_payable" id="edit_coa_payable" class="form-control select2-edit" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_payable as $coa)
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Biaya</label>
                                <select name="coa_cost" id="edit_coa_cost" class="form-control select2-edit" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_cost as $coa)
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Piutang</label>
                                <select name="coa_receivable" id="edit_coa_receivable" class="form-control select2-edit" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_receivable as $coa)
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="fw-bold">Akun Pendapatan</label>
                                <select name="coa_revenue" id="edit_coa_revenue" class="form-control select2-edit" style="width: 100%;">
                                    <option value="0">-- Pilih Akun --</option>
                                    @foreach($coa_revenue as $coa)
                                        <option value="{{ $coa->coa_code }}">[{{ $coa->coa_code }}] {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
    <!-- TOMBOL SINKRON DI SISI KIRI -->
    <button type="button" class="btn btn-info text-white" id="btnSyncPayitem">
        <i class="bi bi-arrow-repeat"></i> Sinkronkan Ke Siswa
    </button>

    <!-- TOMBOL BATAL & SIMPAN DI SISI KANAN -->
    <div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Perbarui Data</button>
    </div>
</div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Fix Select2 di Mode Gelap (Bootstrap 5 & AdminLTE) */
    [data-bs-theme="dark"] .select2-container--default .select2-selection--single,
    body.dark-mode .select2-container--default .select2-selection--single {
        background-color: #212529 !important;
        border-color: #495057 !important;
        color: #f8f9fa !important;
    }

    [data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered,
    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f8f9fa !important;
    }

    [data-bs-theme="dark"] .select2-dropdown,
    body.dark-mode .select2-dropdown {
        background-color: #212529 !important;
        border-color: #495057 !important;
        color: #f8f9fa !important;
    }

    [data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected],
    body.dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd !important;
        color: #fff !important;
    }

    [data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true],
    body.dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #343a40 !important;
    }

    [data-bs-theme="dark"] .select2-search__field,
    body.dark-mode .select2-search__field {
        background-color: #2b3035 !important;
        color: #fff !important;
        border-color: #495057 !important;
    }

    /* Fix Kotak Petunjuk agar adaptif dengan Mode Gelap */
    .info-box-code {
        background-color: rgba(108, 117, 125, 0.1) !important;
        border: 1px solid rgba(108, 117, 125, 0.2) !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {

    // 1. INSIALISASI DATATABLES
    var table = $('#table-payitem').DataTable({
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

    // 2. INISIALISASI SELECT2 (UNTUK MODAL TAMBAH & EDIT)
    if ($.fn.select2) {
        // Select2 di Modal Tambah Data
        $('.select2').select2({
            dropdownParent: $('#modalPayitem'),
            width: '100%'
        });

        // Select2 di Modal Edit Data
        $('.select2-edit').select2({
            dropdownParent: $('#modalEditPayitem'),
            width: '100%'
        });
    }

    // Variabel global dalam scope script untuk menyimpan ID komponen yang sedang aktif/diedit
    let activePayitemId = null;

    // 3. EVENT LISTENER TOMBOL EDIT (POPULATE DATA KE MODAL)
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        
        let id = $(this).data('id');
        activePayitemId = id; // Simpan ID ke variabel untuk proses sinkronisasi

        if(!id) {
            alert("Error: ID tidak ditemukan pada tombol edit.");
            return;
        }
        
        let editUrl = "{{ route('fincom.payitem.student.edit', ':id') }}".replace(':id', id);
        let updateUrl = "{{ route('fincom.payitem.student.update', ':id') }}".replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Set Action Form Modal Edit
                $('#formEditPayitem').attr('action', updateUrl);

                // Isi Form Input biasa
                $('#edit_payitem_code').val(data.payitem_code);
                $('#edit_title').val(data.title);
                $('#edit_payitem_type').val(data.payitem_type);
                $('#edit_payvalue').val(data.payvalue ?? 0);
                $('#edit_ordering').val(data.ordering ?? 1);

                // Set Value Select2 & Trigger Change agar tampilan Select2 terupdate
                if ($.fn.select2) {
                    $('#edit_coa_cash').val(data.coa_cash ?? '0').trigger('change');
                    $('#edit_coa_payable').val(data.coa_payable ?? '0').trigger('change');
                    $('#edit_coa_cost').val(data.coa_cost ?? '0').trigger('change');
                    $('#edit_coa_receivable').val(data.coa_receivable ?? '0').trigger('change');
                    $('#edit_coa_revenue').val(data.coa_revenue ?? '0').trigger('change');
                } else {
                    $('#edit_coa_cash').val(data.coa_cash ?? '0');
                    $('#edit_coa_payable').val(data.coa_payable ?? '0');
                    $('#edit_coa_cost').val(data.coa_cost ?? '0');
                    $('#edit_coa_receivable').val(data.coa_receivable ?? '0');
                    $('#edit_coa_revenue').val(data.coa_revenue ?? '0');
                }

                // Tampilkan Modal Edit
                var modalElement = document.getElementById('modalEditPayitem');
                var modalEdit = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalEdit.show();
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Gagal mengambil data dari server!");
            }
        });
    });

    // 4. EVENT HANDLER TOMBOL SINKRONKAN
    $('#btnSyncPayitem').on('click', function() {
        if (!activePayitemId) {
            alert('ID Komponen tidak valid.');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menyinkronkan komponen ini ke data tagihan siswa?')) {
            return;
        }

        let syncUrl = "{{ route('fincom.payitem.student.sync', ':id') }}".replace(':id', activePayitemId);
        let $btn = $(this);

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyinkronkan...');

        $.ajax({
            url: syncUrl,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                alert(response.message || 'Proses sinkronisasi berhasil!');
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                console.error("Sync Error:", xhr.responseText);
                alert('Gagal melakukan sinkronisasi: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan sistem.'));
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Sinkronkan Ke Siswa');
            }
        });
    });

    // 5. EVENT LISTENER UNTUK FORM TAMBAH DATA (AJAX SUBMIT)
    $('#formAddPayitem').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                alert(response.message); 

                // Tutup Modal
                var modalElement = document.getElementById('modalPayitem');
                if(modalElement) {
                    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.hide();
                    
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
                }

                // Reset Form Input biasa & Select2 kembali ke default ('0')
                form[0].reset();
                if ($.fn.select2) {
                    form.find('.select2').val('0').trigger('change');
                }

                // Refresh DataTables
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = 'Data gagal disimpan:\n';
                    $.each(errors, function(key, value) {
                        errorMsg += '- ' + value[0] + '\n';
                    });
                    alert(errorMsg);
                } else {
                    alert('Terjadi kesalahan sistem. Silakan cek Console (F12).');
                    console.error("Error Detail:", xhr.responseText);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Data');
            }
        });
    });

});
</script>
@endpush