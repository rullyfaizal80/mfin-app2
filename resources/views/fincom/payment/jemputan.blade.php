@extends('layouts.app')

@section('title', 'Entri Nilai Jemputan')

@section('content')
<style>
    .filter-box {
        background-color: var(--bs-gray-100, #f8f9fa); /* Warna default terang */
        padding: 15px;
        border: 1px solid var(--bs-border-color, #ddd);
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    /* Deteksi otomatis jika perangkat user menggunakan mode gelap */
    @media (prefers-color-scheme: dark) {
        .filter-box {
            background-color: #212529; /* Warna background gelap */
            border-color: #495057;     /* Warna border lebih gelap */
        }
    }

    /* Deteksi jika menggunakan fitur Dark Mode Bootstrap 5.3 (data-bs-theme="dark") */
    [data-bs-theme="dark"] .filter-box {
        background-color: #2b3035;
        border-color: #495057;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Entri Nilai Jemputan</h1>
</div>

{{-- NOTIFIKASI PESAN --}}
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

<form action="{{ route('fincom.payment.jemputan') }}" method="POST">
    @csrf

    {{-- KOTAK KONTROL (Kalkulasi & Sinkronisasi) --}}
    <div class="filter-box">
        <div class="row align-items-center">
            
            {{-- KOLOM 1: KALKULASI AKTUAL --}}
            <div class="col-md-6 border-end">
                <label class="fw-bold mb-1">Hitung Nilai Aktual :</label>
                <div class="input-group input-group-sm mb-1" style="max-width: 300px;">
                    <input type="text" name="percent" class="form-control text-center" value="100" maxlength="3">
                    <span class="input-group-text">% dari Maksimal</span>
                    <button type="submit" name="calc" value="1" class="btn btn-warning">Perbarui Aktual</button>
                </div>
                <small class="text-muted" style="font-size: 0.8rem;">
                    Nilai aktual adalah nilai yang dibayar oleh peserta jemputan.<br>
                    Pastikan ID kelas jemputan sudah diset di tabel OPTION.
                </small>
            </div>

            {{-- KOLOM 2: SINKRONISASI --}}
            <div class="col-md-6 ps-4">
                <button type="submit" name="sync" value="1" class="btn btn-info btn-sm mb-1 text-white">
                    <i class="bi bi-arrow-repeat"></i> Sinkron ke Komp. Pembayaran
                </button><br>
                <small class="text-muted" style="font-size: 0.8rem;">
                    Tindakan ini akan memperbarui nilai tagihan jemputan (piutang jemputan) pada data siswa.
                </small>
            </div>

        </div>
    </div>

    {{-- TABEL DATA JEMPUTAN --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="70">No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th class="text-center" width="200">Maksimal (Default)</th>
                            <th class="text-center" width="200">Aktual (Ditagihkan)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jemps as $index => $js)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $js->fullname }}</td>
                                <td>{{ $js->class_title }}</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="def[{{ $js->classuser_id }}]" 
                                               class="form-control text-end format-ribuan" 
                                               value="{{ number_format($js->jemp_def, 0, ',', '.') }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="act[{{ $js->classuser_id }}]" 
                                               class="form-control text-end format-ribuan" 
                                               value="{{ number_format($js->jemp_act, 0, ',', '.') }}">
                                        
                                        {{-- Hidden User ID --}}
                                        <input type="hidden" name="user[{{ $js->classuser_id }}]" value="{{ $js->user_id }}">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data siswa untuk kelas jemputan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- TOMBOL SIMPAN DEFAULT (Di bagian bawah tabel) --}}
        @if(count($jemps) > 0)
            <div class="card-footer text-center py-3">
                <button type="submit" name="save" value="1" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Default
                </button>
            </div>
        @endif
    </div>
</form>

{{-- JAVASCRIPT --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    $(document).ready(function() {
        // Logika sederhana untuk otomatis memberi titik ribuan saat kasir mengetik
        $('.format-ribuan').on('input', function() {
            let val = $(this).val().replace(/\./g, '');
            if($.isNumeric(val)) {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            } else {
                // Hapus karakter non-angka
                $(this).val(val.replace(/[^0-9]/g, ''));
            }
        });
    });
</script>
@endsection