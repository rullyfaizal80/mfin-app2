@extends('layouts.app')

@section('title', 'Daftar Pembayaran')

@section('content')
<style>
    /* Styling agar mirip aplikasi lama */
    .filter-box {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    /* Styling untuk Dropdown Pencarian Siswa */
    #student-list {
        display: none;
        position: absolute;
        background: #fff;
        border: 1px solid #ccc;
        width: 300px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .res-fnis-item {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        font-size: 13px;
    }
    .res-fnis-item:hover {
        background-color: #e9ecef;
    }
    .res-fnis-item strong { color: #0d6efd; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Daftar Pembayaran</h1>
</div>

{{-- FORM FILTER --}}
<form action="{{ route('fincom.payment.index') }}" method="GET" autocomplete="off">
    <div class="filter-box">
        <div class="row align-items-center">
            
            {{-- KOLOM 1: PENCARIAN SISWA --}}
            <div class="col-md-5">
                <label class="fw-bold mb-1">NIS / Nama :</label>
                <div class="input-group">
                    {{-- Input Text Tampil --}}
                    <input type="text" name="fnis" id="fnis" class="form-control form-control-sm" value="{{ $fnis }}" placeholder="Ketik NIS atau Nama...">
                    
                    {{-- Tombol Cari --}}
                    <button type="button" id="fnis_btn" class="btn btn-secondary btn-sm">Cari</button>
                    
                    {{-- Hidden ID untuk dikirim ke controller --}}
                    <input type="hidden" name="user_id" id="user_id" value="{{ $user_id }}">
                </div>
                
                {{-- Container Hasil Pencarian (Dropdown) --}}
                <div id="student-list"></div>
            </div>

            {{-- KOLOM 2: BULAN --}}
            <div class="col-md-3">
                <label class="fw-bold mb-1">Bulan :</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">- Semua -</option>
                    <option value="01" {{ $curMonth == '01' ? 'selected' : '' }}>Januari</option>
                    <option value="02" {{ $curMonth == '02' ? 'selected' : '' }}>Februari</option>
                    <option value="03" {{ $curMonth == '03' ? 'selected' : '' }}>Maret</option>
                    <option value="04" {{ $curMonth == '04' ? 'selected' : '' }}>April</option>
                    <option value="05" {{ $curMonth == '05' ? 'selected' : '' }}>Mei</option>
                    <option value="06" {{ $curMonth == '06' ? 'selected' : '' }}>Juni</option>
                    <option value="07" {{ $curMonth == '07' ? 'selected' : '' }}>Juli</option>
                    <option value="08" {{ $curMonth == '08' ? 'selected' : '' }}>Agustus</option>
                    <option value="09" {{ $curMonth == '09' ? 'selected' : '' }}>September</option>
                    <option value="10" {{ $curMonth == '10' ? 'selected' : '' }}>Oktober</option>
                    <option value="11" {{ $curMonth == '11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{ $curMonth == '12' ? 'selected' : '' }}>Desember</option>
                </select>
            </div>

            {{-- KOLOM 3: TAHUN --}}
            <div class="col-md-2">
                <label class="fw-bold mb-1">Tahun :</label>
                <input type="text" name="year" class="form-control form-control-sm text-center" value="{{ $curYear }}" maxlength="4">
            </div>

            {{-- KOLOM 4: TOMBOL SUBMIT --}}
            <div class="col-md-2 text-end">
                <label class="d-block mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </div>
    </div>
</form>

{{-- TABEL DATA --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="100">Tanggal</th>
                        <th width="120">Referensi</th>
                        <th width="250">Nama Siswa</th>
                        <th>Keterangan</th>
                        <th class="text-end" width="120">Nilai</th>
                        <th class="text-center" width="80">Opsi</th>
                        <th class="text-center" width="80">Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $row)
                        <tr>
                            <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                            <td>{{ $row->ref_no }}</td>
                            <td class="fw-bold">{{ $row->student_name }}</td>
                            <td>
                                {{ $row->payfor }}
                                @if($row->note)
                                    <br><small class="text-muted text-italic">{{ $row->note }}</small>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($row->credit, 0, ',', '.') }}</td>
                            
                            {{-- Placeholder Tombol Opsi (Edit/Hapus) --}}
                            <td class="text-center">
                                <a href="#" class="btn btn-xs btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                            {{-- Placeholder Tombol Cetak --}}
                            <td class="text-center">
                                <a href="#" class="btn btn-xs btn-secondary" title="Cetak"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data pembayaran ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 d-flex justify-content-end">
            {{ $payments->links() }}
        </div>
    </div>
</div>

{{-- JAVASCRIPT --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    $(document).ready(function() {
        
        // 1. Logika Klik Tombol "Cari"
        $("#fnis_btn").click(function(){
            var keyword = $("#fnis").val();
            
            // Tampilkan loading/text sementara
            $("#student-list").html('<div class="p-2 text-center text-muted">Mencari...</div>').slideDown(100);

            // AJAX Request ke Laravel Route
            $.get("{{ route('fincom.payment.ajax_student') }}", { q: keyword }, function(data){
                var html = '';
                
                if(data.length > 0) {
                    $.each(data, function(index, item){
                        // Format tampilan per item dropdown
                        html += '<div class="res-fnis-item" ' +
                                    'data-id="' + item.id + '" ' +
                                    'data-name="' + item.fullname + '" ' +
                                    'data-nis="' + item.nis + '">' +
                                    '<strong>' + item.nis + '</strong> - ' + item.fullname + 
                                '</div>';
                    });
                } else {
                    html = '<div class="p-2 text-center text-danger">Data tidak ditemukan</div>';
                }

                $("#student-list").html(html);
            });
        });

        // 2. Logika Klik Item Hasil Pencarian
        $(document).on("click", ".res-fnis-item", function() {
            var userId = $(this).data('id');
            var userName = $(this).data('name');
            var userNis = $(this).data('nis');

            // Masukkan nilai ke input
            $("#fnis").val(userNis + ' - ' + userName); // Tampilkan NIS - Nama di box
            $("#user_id").val(userId); // Simpan ID di hidden input

            // Sembunyikan list
            $("#student-list").slideUp(200);
        });

        // 3. Sembunyikan list jika klik di luar area
        $(document).mouseup(function(e) {
            var container = $("#student-list");
            var btn = $("#fnis_btn");
            if (!container.is(e.target) && container.has(e.target).length === 0 && !btn.is(e.target)) {
                container.slideUp(200);
            }
        });

    });
</script>

@endsection