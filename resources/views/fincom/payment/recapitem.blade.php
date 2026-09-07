@extends('layouts.app')

@section('title', $page_title)

@section('content')
<style>
    /* Styling agar mirip aplikasi lama (Disamakan dengan index) */
    .filter-box {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    /* CSS Khusus Print (Menyembunyikan form filter dan elemen yang tidak perlu dicetak) */
    @media print {
        body { background-color: #fff !important; }
        .d-print-none, .navbar, .sidebar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table-light { color: #000 !important; background-color: #eee !important; }
        .print-header { display: block !important; margin-bottom: 20px; text-align: center; }
    }
</style>

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom d-print-none">
    <h1 class="h3">{{ $page_title }}</h1>
</div>

{{-- HEADER KHUSUS KETIKA DICETAK (Hanya muncul di kertas) --}}
<div class="print-header d-none">
    <h3>{{ $page_title }}</h3>
    @if($isFilter)
        <p class="mb-0">Bulan: {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</p>
    @endif
</div>

{{-- FORM FILTER --}}
<form action="{{ route('fincom.payment.recapitem') }}" method="GET" class="d-print-none" autocomplete="off">
    <input type="hidden" name="filter" value="1">
    
    <div class="filter-box">
        <div class="row align-items-center">
            
            {{-- Filter Bulan --}}
            <div class="col-md-3">
                <label class="fw-bold mb-1">Bulan :</label>
                <select name="month" class="form-select form-select-sm">
                    @for($i = 1; $i <= 12; $i++)
                        @php $m = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Filter Tahun --}}
            <div class="col-md-2">
                <label class="fw-bold mb-1">Tahun :</label>
                <input type="text" name="year" class="form-control form-control-sm text-center" value="{{ $year }}" maxlength="4">
            </div>

            {{-- Filter Komponen --}}
            <div class="col-md-4">
                <label class="fw-bold mb-1">Komponen Pembayaran :</label>
                <select name="fpayitem" class="form-select form-select-sm">
                    <option value="0">- Semua Komponen -</option>
                    @foreach($payitems as $pi)
                        <option value="{{ $pi->id }}" {{ $fpayitem == $pi->id ? 'selected' : '' }}>
                            {{ $pi->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Tampilkan & Cetak --}}
            <div class="col-md-3 text-end">
                <label class="d-block mb-1">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
                    
                    {{-- Tombol Cetak hanya muncul di sebelah filter jika ada data --}}
                    @if($isFilter && count($reports) > 0)
                        <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm w-100" title="Cetak Laporan">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</form>

{{-- KONDISI: TAMPILKAN TABEL JIKA TOMBOL FILTER SUDAH DIKLIK --}}
@if($isFilter)
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th class="text-center" width="100">Tanggal</th>
                            <th width="150">Referensi</th>
                            <th width="200">Nama Siswa</th>
                            <th width="100">Kelas</th>
                            <th width="150">Komponen</th>
                            <th>Note</th>
                            <th class="text-end" width="120">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $index => $row)
                            <tr>
                                {{-- Penomoran berkelanjutan mengikuti pagination --}}
                                <td class="text-center">{{ ($reports->currentPage() - 1) * $reports->perPage() + $index + 1 }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                                <td>{{ $row->ref_no }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                <td>{{ $row->class_title ?? '-' }}</td>
                                <td>{{ $row->payitem }}</td>
                                <td>{{ $row->note }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->payment, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Tidak ada data pembayaran untuk periode & komponen ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION --}}
            @if(count($reports) > 0)
            <div class="p-3 d-flex justify-content-end d-print-none">
                {{ $reports->links() }}
            </div>
            @endif
            
        </div>
    </div>

{{-- JIKA AWAL MEMBUKA HALAMAN (BELUM DIFILTER) --}}
@else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search text-secondary" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Pilih Filter Pencarian</h5>
        <p>Silakan pilih bulan, tahun, dan komponen, lalu klik "Tampilkan" untuk melihat rekap pembayaran.</p>
    </div>
@endif

@endsection