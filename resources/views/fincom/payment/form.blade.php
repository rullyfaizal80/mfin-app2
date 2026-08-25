@extends('layouts.app')

@section('title', isset($payment) ? 'Edit Pembayaran' : 'Entri Pembayaran')

@section('content')
<style>
    #student-list {
        display: none; position: absolute; background: #fff; border: 1px solid #ccc;
        width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .res-fnis-item { padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #eee; }
    .res-fnis-item:hover { background-color: #e9ecef; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">{{ isset($payment) ? 'Edit Pembayaran Siswa' : 'Entri Pembayaran Baru' }}</h1>
</div>

{{-- FORM UTAMA --}}
<form action="{{ isset($payment) ? url('fincom/payment/update/'.$payment->id) : url('fincom/payment/store') }}" method="POST" id="form-payment">
    @csrf
    @if(isset($payment)) @method('PUT') @endif

    {{-- TAMBAHKAN BLOK INI UNTUK MELIHAT ERROR DATABASE / VALIDASI --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Gagal Menyimpan!</strong> Periksa isian berikut:
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger shadow-sm">
            <strong>System Error:</strong> {{ session('error') }}
        </div>
    @endif
    {{-- BATAS TAMBAHAN --}}

    <div class="row">
        {{-- BAGIAN ATAS: INFORMASI TRANSAKSI --}}
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Informasi Transaksi</div>
                <div class="card-body">
                    <div class="row">
                        {{-- Kolom Kiri: Data Siswa & Total --}}
                        <div class="col-md-6">
                            {{-- Pencarian Siswa --}}
                            <div class="mb-3 position-relative">
                                <label class="form-label fw-bold">NIS / Nama Siswa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="fnis" id="fnis" class="form-control" 
                                           value="{{ $payment->student->nis ?? '' }}" placeholder="Ketik NIS atau Nama"
                                           {{ isset($payment) ? 'disabled' : '' }}>
                                    <button type="button" id="fnis_btn" class="btn btn-secondary" {{ isset($payment) ? 'disabled' : '' }}>Cari</button>
                                </div>
                                <div id="student-list"></div>
                                
                                {{-- Hidden inputs --}}
                                <input type="hidden" name="user_id" id="user_id" value="{{ $payment->user_id ?? '' }}">
                                <input type="hidden" name="nis" id="nis" value="{{ $payment->student->nis ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <div id="student_name" class="form-control bg-light" style="min-height: 38px;">
                                    {{ $payment->student->fullname ?? '-' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">Total Pembayaran (Rp) <span class="text-danger">*</span></label>
                                <input type="text" name="payvalue" id="payvalue" class="form-control form-control-lg text-end fw-bold text-primary" 
                                       value="{{ number_format($payment->tvalue ?? 0, 0, ',', '.') }}" readonly>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Referensi, Tanggal, Keterangan --}}
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tdate" id="tdate" class="form-control" 
                                           value="{{ $payment->tdate ?? date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Referensi <span class="text-danger">*</span></label>
                                    <input type="text" name="ref_no" id="ref_no" class="form-control" 
                                           value="{{ $payment->ref_no ?? $refno }}" {{ isset($payment) ? 'readonly' : '' }}>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="note" id="note" class="form-control" rows="4">{{ $payment->note ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN BAWAH: RINCIAN TAGIHAN & PEMBAYARAN --}}
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Rincian Pembayaran</div>
                <div class="card-body p-0">
                    
                    {{-- Container AJAX untuk List Tagihan --}}
                    <div id="list-payment" class="p-3 table-responsive">
                        <div class="text-center text-muted py-3">Pilih siswa terlebih dahulu untuk melihat tagihan.</div>
                    </div>

                    {{-- Form Tambah Item Manual (Dari select box) --}}
                    <div class="bg-light p-3 border-top">
                        <h6 class="fw-bold mb-3">Tambah Pembayaran Manual (Opsional)</h6>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small">Jenis Pembayaran</label>
                                <select name="userpayitem_id" id="userpayitem_id" class="form-select form-select-sm">
                                    <option value="0">-- Pilih --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Nilai</label>
                                <input type="text" name="tvalue" id="tvalue" class="form-control form-control-sm text-end" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Bulan</label>
                                <select id="month" name="month" class="form-select form-select-sm" disabled>
                                    @for($i=1; $i<=12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Tahun</label>
                                <input type="text" name="year" id="year" class="form-control form-control-sm" value="{{ date('Y') }}" disabled>
                            </div>
                            <div class="col-md-1">
                                <button type="button" name="add" id="add" class="btn btn-primary btn-sm w-100">Tambah</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- TOMBOL AKSI DI BAWAH --}}
            <div class="text-end mt-2">
                @if(isset($payment))
                    <button type="submit" name="action" value="delete" class="btn btn-danger px-4" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                @endif
                <button type="submit" name="action" value="save" class="btn btn-success px-4" onclick="return confirm('Pastikan Nominal dan Tunai/Non-Tunai sudah sesuai. Simpan?')">Simpan</button>
                <button type="submit" name="action" value="save_print" class="btn btn-info text-white px-4">Simpan & Print</button>
            </div>
        </div>
    </div>
</form>

{{-- JAVASCRIPT AJAX & LOGIC --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // Setup Header CSRF untuk semua request AJAX JQuery di Laravel
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    var stat = "{{ isset($payment) ? 'edit' : 'new' }}";
    var isi = $("#user_id").val();

    // Inisialisasi saat Edit
    if (isi > 0) {
        loadUserPayItems(isi);
        if(stat === "edit"){
            loadListPaymentEdit("{{ $payment->id ?? 0 }}");
        } else {
            loadListPayment(isi);
        }
    }

    // PENCARIAN SISWA (Gunakan POST karena mengirim {nis: fnis})
    $("#fnis_btn").click(function() {
        var fnis = $("#fnis").val();
        $("#student-list").html('<div class="p-2 text-center text-muted">Mencari...</div>').slideDown(100);

        $.post("{{ url('fincom/payment/ajax-getnis') }}", { nis: fnis }, function(data) {
            $("#student-list").html(data);
        });
    });

    // PILIH SISWA
    $("#student-list").on("click", ".res-fnis-item", function() {
        var user_id = $(this).attr('data-user_id');
        var user_name = $(this).attr('data-user_name');
        var nis = $(this).attr('data-user_nis');
        
        loadUserPayItems(user_id);
        loadListPayment(user_id);

        $("#student_name").html(user_name);
        $("#user_id").val(user_id);
        $("#nis").val(nis);
        $("#fnis").val(nis + ' - ' + user_name);
        
        $("#student-list").slideUp(200);
    });

    // FUNGSI LOAD DATA AJAX (Menggunakan GET karena hanya fetch data)
    function loadUserPayItems(userId) {
        $.get("{{ url('fincom/payment/ajax-getuserpayid') }}/" + userId, function(data) {
            $("#userpayitem_id").html(data);
        });
    }

    function loadListPayment(userId) {
        $.get("{{ url('fincom/payment/ajax-list-payment') }}/" + userId, function(data) {
            $("#list-payment").html(data);
        });
    }

    function loadListPaymentEdit(trxId) {
        $.get("{{ url('fincom/payment/ajax-list-payment-edit') }}/" + trxId, function(data) {
            $("#list-payment").html(data);
        });
    }

    // PERUBAHAN DROPDOWN JENIS PEMBAYARAN MANUAL
    $("#userpayitem_id").change(function() {
        var isi = $("#user_id").val();
        var idp = $(this).val();
        
        if(idp > 0) {
            // Menggunakan $.get dan menghapus JSON.parse karena Laravel otomatis mengembalikan objek JSON
            $.get("{{ url('fincom/payment/ajax-getuserpayment') }}/" + isi + "/" + idp, function(dexp) {
                // Gunakan dexp.credit atau dexp.debit sesuai struktur kolom Anda. Di Laravel biasanya ini credit.
                $("#tvalue").val(dexp.credit || dexp.debit || 0);
                
                if(dexp.pay_repeat === "monthly") {
                    $("#month").removeAttr("disabled");
                } else {
                    $("#month").prop('disabled', true);
                }
                
                if(dexp.pay_repeat !== "once") {
                    $("#year").removeAttr("disabled");
                } else {
                    $("#year").prop('disabled', true);
                }
            });
        }
    });

    // LOGIKA PERHITUNGAN CHECKBOX TAGIHAN
    $("#list-payment").on("click", ".list-box", function() {
        var no = $(this).attr('no');
        
        // Paksa nilai menjadi String sebelum dikenai replace()
        var payvalStr = String($("#payvalue").val());
        var tvalStr = String($("#tvaluee_" + no).val());
        
        var payvalue = parseFloat(payvalStr.replace(/\./g, '')) || 0;
        var tvalue = parseFloat(tvalStr.replace(/\./g, '')) || 0;

        if($(this).is(':checked')) {
            payvalue += tvalue;
        } else {
            payvalue -= tvalue;
        }

        $("#payvalue").val(payvalue.toLocaleString('id-ID'));
    });

    // LOGIKA INPUT EDIT NILAI LIST TAGIHAN
    $("#list-payment").on("change", ".listvalue", function() {
        var no = $(this).attr('no');
        
        var payvalStr = String($("#payvalue").val());
        var newvalStr = String($(this).val());
        var oldvalStr = String($("#payvalue_" + no).val());
        
        var payvalue = parseFloat(payvalStr.replace(/\./g, '')) || 0;
        var newValue = parseFloat(newvalStr.replace(/\./g, '')) || 0;
        var oldValue = parseFloat(oldvalStr.replace(/\./g, '')) || 0;

        // Hitung selisih
        var total = payvalue - oldValue + newValue;
        
        $("#payvalue").val(total.toLocaleString('id-ID'));
        $("#payvalue_" + no).val(newValue); // Update nilai awal ke nilai baru
    });

    // Tutup dropdown siswa jika klik di luar
    $(document).mouseup(function(e) {
        var container = $("#student-list");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.slideUp(200);
        }
    });

});
</script>
@endsection