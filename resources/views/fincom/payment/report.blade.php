@extends('layouts.app')

@section('title', $page_title)

@section('content')
<style>
    /* Styling form filter */
    .filter-box {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    /* Styling Dropdown Pencarian Siswa */
    #student-list {
        display: none;
        position: absolute;
        background: #fff;
        border: 1px solid #ccc;
        width: 100%;
        max-width: 400px;
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
    .res-fnis-item:hover { background-color: #e9ecef; }
    .res-fnis-item strong { color: #0d6efd; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">{{ $page_title }}</h1>
</div>

{{-- FORM FILTER --}}
<form action="{{ route('fincom.payment.report') }}" method="GET" autocomplete="off">
    <div class="filter-box">
        <div class="row align-items-center">
            
            {{-- Pencarian Siswa --}}
            <div class="col-md-5 position-relative">
                <label class="fw-bold mb-1">NIS / Nama :</label>
                <div class="input-group">
                    <input type="text" name="fnis" id="fnis" class="form-control form-control-sm" value="{{ $fnis }}" placeholder="Ketik NIS atau Nama...">
                    <button type="button" id="fnis_btn" class="btn btn-secondary btn-sm">Cari</button>
                    <input type="hidden" name="user_id" id="user_id" value="{{ $userId }}">
                </div>
                <div id="student-list"></div>
            </div>

            {{-- Tahun --}}
            <div class="col-md-2">
                <label class="fw-bold mb-1">Tahun :</label>
                <input type="text" name="year" class="form-control form-control-sm text-center" value="{{ $year }}" maxlength="4">
            </div>

            {{-- Tombol Filter --}}
            <div class="col-md-2 text-end">
                <label class="d-block mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            
        </div>
    </div>
</form>

{{-- TABEL DATA --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th>Nama Siswa</th>
                        {{-- Lebarkan dari 200 menjadi 350 agar tombol tidak terlipat --}}
                        <th width="350" class="text-center" style="white-space: nowrap;">Aksi Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $row)
                        <tr>
                            <td class="text-center">{{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}</td>
                            <td class="fw-bold">{{ $row->fullname }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    {{-- Link menuju Daftar Pembayaran --}}
    <a href="{{ route('fincom.payment.listyear', ['user_id' => $row->user_id, 'year' => $year]) }}" class="btn btn-outline-secondary" title="Daftar Pembayaran" target="_blank">
        <i class="bi bi-card-list"></i> Daftar Pembayaran
    </a>
                                    {{-- Link menuju Rekap Tahunan (Memanggil route recapyear yang sudah kita buat sebelumnya!) --}}
                                    <a href="{{ route('fincom.payment.recapyear', ['user_id' => $row->user_id, 'year' => $year]) }}" class="btn btn-outline-primary" title="Rekap Tahunan 12 Bulan" target="_blank">
                                        <i class="bi bi-calendar3"></i> Rekap Tahunan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Tidak ada data transaksi pembayaran di tahun tersebut.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 d-flex justify-content-end">
            {{ $students->links() }}
        </div>
    </div>
</div>

{{-- SCRIPT PENCARIAN SISWA --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

        // Jika kotak pencarian dikosongkan manual oleh user, hapus hidden ID
        $("#fnis").on('input', function() {
            if ($(this).val().trim() === '') { $("#user_id").val(''); }
        });

        $("#fnis_btn").click(function(){
            var keyword = $("#fnis").val();
            $("#student-list").html('<div class="p-2 text-center text-muted">Mencari...</div>').slideDown(100);
            
            $.post("{{ url('fincom/payment/ajax-getnis') }}", { nis: keyword }, function(data){
                $("#student-list").html(data);
            }).fail(function() {
                $("#student-list").html('<div class="p-2 text-center text-danger">Server Error.</div>');
            });
        });

        $(document).on("click", ".res-fnis-item", function() {
            var userId = $(this).attr('data-user_id');
            var userName = $(this).attr('data-user_name');
            var userNis = $(this).attr('data-user_nis');
            
            $("#fnis").val(userNis + ' - ' + userName);
            $("#user_id").val(userId);
            $("#student-list").slideUp(200);
        });

        $(document).mouseup(function(e) {
            var container = $("#student-list");
            var btn = $("#fnis_btn");
            if (!container.is(e.target) && container.has(e.target).length === 0 && !btn.is(e.target)) {
                container.slideUp(200);
            }
        });

        $("#fnis").keypress(function(e) {
            if(e.which == 13) { e.preventDefault(); $("#fnis_btn").click(); }
        });
    });
</script>
@endsection