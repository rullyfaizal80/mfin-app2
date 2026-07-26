@extends('layouts.app') {{-- Pastikan layout Anda memuat CSS/JS Bootstrap 5 --}}

@section('content')
<style>
    /* Styling khusus untuk dropdown autocomplete agar mendukung Dark Mode */
    .autocomplete-wrapper {
        position: relative;
    }
    #student-list {
        position: absolute;
        z-index: 1050;
        top: 100%;
        left: 0;
        right: 0;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    .res-fnis-item {
        display: block;
        padding: 0.5rem 1rem;
        color: var(--bs-body-color);
        text-decoration: none;
        border-bottom: 1px solid var(--bs-border-color-translucent);
    }
    .res-fnis-item:hover, .res-fnis-item:focus {
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
    }
    .res-fnis-item:last-child {
        border-bottom: none;
    }
</style>

<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var baseUrl = "{{ url('/') }}";
    var stat = "{{ $ket }}";
    var isi = $("#user_id").val();

    if(isi) {
        $.post(baseUrl + "/fincom/receivable/ax_getuserpayid/" + isi, function(data){
            $("#userpayitem_id").html(data);
        });
    }
	
    if(stat === "edit"){
        $("#fnis").prop('disabled', true);
        $("#fnis_btn").prop('disabled', true);
        $("#ref_no").prop('readonly', true);		
    }

    $("#fnis_btn").click(function(){
        var fnis = $("#fnis").val();
        $.post(baseUrl + "/fincom/payment/ax_getnis", {'nis': fnis}, function(data){
            $("div#student-list").html(data).slideDown(200);
        });
    });
	
    $("#student-list").on("click", ".res-fnis-item", function(e) {
        e.preventDefault();
        var user_id = $(this).attr('user_id');
        var user_name = $(this).attr('user_name');
        var nis = $(this).attr('user_nis');
		
        $.post(baseUrl + "/fincom/receivable/ax_getuserpayid/" + user_id, function(data){
            $("#userpayitem_id").html(data);
        });		
		
        $("#user_name").val(user_name);
        $("#user_id").val(user_id);
        $("#nis").val(nis);
		
        $("div#student-list").slideUp(200);
        $("#student_name").html(user_name);
		
        $.post(baseUrl + "/admin/app/ax_list_payitem", {'user_id': user_id}, function(data){
            $("div#payitem-list").html(data);
        });		
    });

    // Menutup dropdown jika klik di luar area
    $(document).click(function(event) {
        if (!$(event.target).closest('.autocomplete-wrapper').length) {
            $('#student-list').slideUp(200);
        }
    });

    $("#userpayitem_id").change(function(){
        var idp = $(this).val();
        if(idp > 0){
            $.post(baseUrl + "/fincom/receivable/ax_getuserpayment/" + idp, function(data){
                var dexp = typeof data === 'string' ? JSON.parse(data) : data;
                var bulanan = dexp.pay_repeat;
                
                $("#tvalue").val(dexp.payvalue);
                $("#pay_repeat").val(dexp.pay_repeat);
                $("#start_date").val(dexp.pay_start);

                if(bulanan === "once"){
                    $("#month").prop('disabled', true);
                } else if(bulanan === "yearly"){
                    $("#year").removeAttr("disabled");
                    $("#month").prop('disabled', true);
                } else {
                    $("#month").removeAttr("disabled");
                    $("#year").removeAttr("disabled");
                }	
            });
        }
    });
});
</script>

<div class="container-fluid py-4">
    <form action="{{ route('fincom.receivable.edit', $receivable->first()->id) }}" method="POST" id="form-edit">
        @csrf

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ $page_title }}</h5>
            </div>
            
            <div class="card-body">
                
                {{-- Alert Notifikasi & Error --}}
                @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @foreach($receivable as $r)
                <!-- Form Header -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIS / Nama</label>
                        <div class="input-group autocomplete-wrapper">
                            <input name="fnis" type="text" class="form-control" id="fnis" placeholder="Cari NIS..." maxlength="50" />
                            <button type="button" class="btn btn-outline-secondary" id="fnis_btn">Cari</button>
                            <input name="user_id" type="hidden" id="user_id" value="{{ $r->user_id }}" />
                            <input name="nis" type="hidden" id="nis" value="" />
                            <div id="student-list"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Siswa Terpilih <span class="text-danger">*</span></label>
                        <div id="student_name" class="form-control bg-body-secondary fw-bold" style="min-height: 38px;">
                            @foreach($users as $user)
                                @if($user->id == $r->user_id)
                                    {{ $user->fullname }}
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input name="tdate" type="date" class="form-control" id="tdate" value="{{ old('tdate', $r->tdate) }}" />
                        <input name="count_item" type="hidden" id="count_item" value="{{ $count_item > 0 ? $count_item : '' }}" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Referensi <span class="text-danger">*</span></label>
                        <input name="ref_no" type="text" class="form-control" id="ref_no" value="{{ old('ref_no', $r->ref_no != '' ? $r->ref_no : $refno) }}" maxlength="45" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Keseluruhan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input name="payvalue" type="text" class="form-control bg-body-secondary text-end fw-bold" id="payvalue" value="{{ old('payvalue', $r->tvalue > 0 ? number_format($r->tvalue, 0, ',', '.') : '') }}" readonly />
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Keterangan Umum</label>
                        <textarea name="note" class="form-control" id="note" rows="2" placeholder="Catatan transaksi (opsional)...">{{ $r->note }}</textarea>
                    </div>
                </div>
                @endforeach

                <hr class="my-4">

                <!-- Tabel Item Detail Piutang -->
                <h6 class="fw-bold mb-3">Rincian Piutang</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Jenis Piutang</th>
                                <th width="15%">Nominal (Rp)</th>
                                <th width="15%">Bulan</th>
                                <th width="10%">Tahun</th>
                                <th width="30%">Catatan Detail</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $itemId = $item->id;
                                    $tdate = $item->tdate;
                                    $month = date("m", strtotime($tdate));

                                    if($month == "08") $month = "15";
                                    elseif($month == "09") $month = "16";

                                    $year = date("Y", strtotime($tdate));
                                    $payRepeat = $item->pay_repeat;
                                    $debit = $item->debit;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="userpayitem_id_{{ $itemId }}" id="userpayitem_id_{{ $itemId }}" value="{{ $item->payitem_id }}"/>
                                        <span class="fw-medium">{{ $item->title }}</span>
                                    </td>
                                    <td>
                                        <input name="tvalue_{{ $itemId }}" type="text" class="form-control form-control-sm text-end" id="tvalue_{{ $itemId }}" value="{{ number_format($debit, 0, ',', '.') }}" />
                                        <input name="pay_repeat_{{ $itemId }}" type="hidden" id="pay_repeat_{{ $itemId }}" value="{{ $payRepeat }}" />
                                        <input name="start_date_{{ $itemId }}" type="hidden" id="start_date_{{ $itemId }}" value="{{ $item->pay_start }}" />
                                    </td>
                                    <td>
                                        <select id="month_{{ $itemId }}" name="month_{{ $itemId }}" class="form-select form-select-sm" @if($payRepeat == "once" || $payRepeat == "yearly") disabled @endif>
                                            <option value="01" {{ $month == "01" ? 'selected' : '' }}>Januari</option>
                                            <option value="02" {{ $month == "02" ? 'selected' : '' }}>Februari</option>
                                            <option value="03" {{ $month == "03" ? 'selected' : '' }}>Maret</option>
                                            <option value="04" {{ $month == "04" ? 'selected' : '' }}>April</option>
                                            <option value="05" {{ $month == "05" ? 'selected' : '' }}>Mei</option>
                                            <option value="06" {{ $month == "06" ? 'selected' : '' }}>Juni</option>
                                            <option value="07" {{ $month == "07" ? 'selected' : '' }}>Juli</option>
                                            <option value="15" {{ $month == "15" ? 'selected' : '' }}>Agustus</option>
                                            <option value="16" {{ $month == "16" ? 'selected' : '' }}>September</option>
                                            <option value="10" {{ $month == "10" ? 'selected' : '' }}>Oktober</option>
                                            <option value="11" {{ $month == "11" ? 'selected' : '' }}>November</option>
                                            <option value="12" {{ $month == "12" ? 'selected' : '' }}>Desember</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input name="year_{{ $itemId }}" type="text" class="form-control form-control-sm text-center" id="year_{{ $itemId }}" value="{{ $year }}" @if($payRepeat == "once") disabled @endif />
                                    </td>
                                    <td>
                                        <input name="tnote_{{ $itemId }}" type="text" class="form-control form-control-sm" id="tnote_{{ $itemId }}" value="{{ $item->note }}" placeholder="Catatan..." />
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="submit" name="update_{{ $itemId }}" value="Upd" class="btn btn-sm btn-outline-primary" title="Update Baris">Upd</button>
                                            <button type="submit" name="del_{{ $itemId }}" value="Del" class="btn btn-sm btn-outline-danger" title="Hapus Baris" onclick="return confirm('Yakin ingin menghapus baris item ini?');">Del</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Baris Tambah Item Baru -->
                            <tr class="table-secondary">
                                <td>
                                    <select id="userpayitem_id" name="userpayitem_id" class="form-select form-select-sm">
                                        <option value="">-- Pilih Piutang --</option>
                                        <!-- Diisi via AJAX -->
                                    </select>
                                </td>
                                <td>
                                    <input name="tvalue" type="text" class="form-control form-control-sm text-end" id="tvalue" value="0" />
                                    <input name="pay_repeat" type="hidden" id="pay_repeat" />
                                    <input name="start_date" type="hidden" id="start_date" />
                                </td>
                                <td>
                                    <select id="month" name="month" class="form-select form-select-sm">
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="15">Agustus</option>
                                        <option value="16">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </td>
                                <td>
                                    <input name="year" type="text" class="form-control form-control-sm text-center" id="year" value="{{ date('Y') }}" />
                                </td>
                                <td>
                                    <input name="tnote" type="text" class="form-control form-control-sm" id="tnote" placeholder="Catatan item baru..." />
                                </td>
                                <td class="text-center">
                                    <button type="submit" name="add" value="Add" class="btn btn-sm btn-primary w-100">Tambah</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Hidden Flag -->
                <input name="submit" type="hidden" id="submit" value="1" />

            </div>

            <!-- Footer / Action Buttons -->
            <div class="card-footer bg-transparent py-3">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" name="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data keseluruhan ini?');">
                        Hapus Permanen
                    </button>
                    <a href="{{ route('fincom.receivable.ilist') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" name="save" value="Simpan" class="btn btn-success px-4">
                        Simpan Transaksi
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection