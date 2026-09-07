@extends('layouts.app')

@section('title', $page_title)

@section('content')

{{-- ========================================== --}}
{{-- 1. ELEMEN LOADING OVERLAY (ANIMASI)        --}}
{{-- ========================================== --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; backdrop-filter: blur(3px);">
    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem; border-width: 0.3em;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h4 class="mt-4 text-primary fw-bold">Sedang Memproses Data...</h4>
    <p class="text-muted">Mohon tunggu sebentar, sistem sedang merekap piutang.</p>
</div>
{{-- ========================================== --}}


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">{{ $page_title }}</h1>
</div>

{{-- FORM FILTER --}}
<div class="card shadow-sm border-0 mb-4 bg-light d-print-none">
    <div class="card-body">
        {{-- Tambahkan ID "filterForm" pada form ini --}}
        <form id="filterForm" action="{{ route('fincom.payment.recaprec') }}" method="GET" autocomplete="off">
            <input type="hidden" name="filter" value="1">
            <div class="row g-2 align-items-end">
                
                {{-- Jenis Pembayaran --}}
                <div class="col-md-5">
                    <label class="fw-bold mb-1 small">Jenis Pembayaran :</label>
                    <select name="fpayitem" class="form-select form-select-sm">
                        <option value="all">-- Semua Jenis Pembayaran --</option>
                        @foreach($payitems as $pitem)
                            <option value="{{ $pitem->id }}" {{ $fpayitem == $pitem->id ? 'selected' : '' }}>
                                {{ $pitem->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun --}}
                <div class="col-auto" style="width: 150px;">
                    <label class="fw-bold mb-1 small">Tahun :</label>
                    <input type="text" name="year" class="form-control form-control-sm text-center" value="{{ $year }}" maxlength="4">
                </div>

                {{-- Tombol --}}
                <div class="col">
                    <div class="d-flex gap-2 justify-content-start">
                        <button type="submit" id="btnFilter" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        
                        @if(isset($receivables) && count($receivables) > 0)
                            <a href="{{ route('fincom.payment.recaprec.print', request()->query()) }}" target="_blank" class="btn btn-secondary btn-sm px-4" title="Cetak Laporan">
                                <i class="bi bi-printer"></i> Print
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- AREA HASIL PENCARIAN --}}
@if($isFilter)
    
    @if(count($receivables) == 0)
        <div class="alert alert-warning text-center mt-4">
            <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
            Tidak ada data piutang (tunggakan) pada komponen dan tahun tersebut.
        </div>
    @else
        <div class="d-flex justify-content-between align-items-end mb-2 mt-4 border-bottom pb-2">
            <h5 class="mb-0 text-danger">Daftar Tunggakan Siswa</h5>
            <h5 class="mb-0 text-danger fw-bold">Grand Total Piutang: Rp {{ number_format($grandTotal, 0, ',', '.') }}</h5>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="15%">Komponen</th>
                                <th width="10%">Tanggal</th>
                                <th width="15%">Referensi</th>
                                <th width="20%">Nama Siswa</th>
                                <th width="10%">Kelas</th>
                                <th>Keterangan</th>
                                <th width="12%">Nilai Piutang (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $row)
                                <tr>
                                    <td>{{ $row->komponen }}</td>
                                    <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                                    <td class="text-center">{{ $row->ref_no }}</td>
                                    <td class="fw-bold">{{ $row->fullname }}</td>
                                    <td class="text-center">{{ $row->class ?? '-' }}</td>
                                    <td>{{ $row->note }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($row->payment, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($receivables, 'hasPages') && $receivables->hasPages())
                <div class="card-footer bg-white pb-0 pt-3 pagination-container">
                    {{ $receivables->links() }}
                </div>
            @endif
        </div>
    @endif

@else
    {{-- Tampilan saat pertama kali halaman dibuka --}}
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search text-secondary" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Pilih Filter Pencarian</h5>
        <p>Silakan pilih jenis pembayaran dan tahun, lalu klik "Filter".</p>
    </div>
@endif

{{-- ========================================== --}}
{{-- 2. SCRIPT PEMICU ANIMASI LOADING           --}}
{{-- ========================================== --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        const filterForm = document.getElementById('filterForm');
        const btnFilter = document.getElementById('btnFilter');

        // Memunculkan loading saat tombol filter di klik
        if(filterForm) {
            filterForm.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
                // Opsional: nonaktifkan tombol agar tidak di-klik 2x (double submit)
                btnFilter.disabled = true;
                btnFilter.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            });
        }

        // Memunculkan loading saat tombol navigasi halaman (paginasi) di klik
        const paginationLinks = document.querySelectorAll('.pagination-container a');
        paginationLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    });
</script>

@endsection