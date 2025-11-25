@extends('layouts.app')

@section('title', 'Rincian Tabungan | MFIN')

@section('content')
    {{-- LOADING OVERLAY --}}
    <div id="loading-overlay" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; flex-direction:column; justify-content:center; align-items:center;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <div class="mt-2 fw-bold text-dark fs-5">Memuat Data...</div>
    </div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Rincian Tabungan</h1>
            <span class="text-muted">Nasabah: <strong>{{ $user->fullname }}</strong></span>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('savings.index') }}" class="btn btn-sm btn-outline-secondary btn-loading">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Info Saldo Header (Penting: Ini Saldo Total Semua Waktu) --}}
    <div class="alert alert-info d-flex justify-content-between align-items-center py-2">
        <span>Total Saldo Saat Ini:</span>
        <span class="fs-5 fw-bold">Rp {{ number_format($saldo_total, 0, ',', '.') }}</span>
    </div>

    {{-- Filter Tanggal --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('savings.show', $user->id) }}" method="GET" class="row g-2 align-items-end" id="filter-form">
                <div class="col-auto">
                    <label class="form-label small mb-0">Dari</label>
                    <input type="date" name="ffrom" class="form-control form-control-sm" value="{{ $ffrom }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-0">Sampai</label>
                    <input type="date" name="fto" class="form-control form-control-sm" value="{{ $fto }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Ref</th>
                            <th>Uraian / Catatan</th>
                            <th class="text-end text-success">Masuk (Kredit)</th>
                            <th class="text-end text-danger">Keluar (Debit)</th>
                            <th class="text-center" width="50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $row)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($row->tdate)) }}</td>
                                <td>{{ $row->ref_no }}</td>
                                <td>{{ $row->note }}</td>
                                <td class="text-end">
                                    @if($row->credit > 0) {{ number_format($row->credit, 0, ',', '.') }} @else - @endif
                                </td>
                                <td class="text-end">
                                    @if($row->debit > 0) {{ number_format($row->debit, 0, ',', '.') }} @else - @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('savings.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Saldo akan dikembalikan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- Footer Total Halaman Ini DIHAPUS --}}
                </table>
            </div>

            {{-- Tombol Pagination --}}
            <div class="mt-3 pagination-links">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loader = document.getElementById('loading-overlay');

            // 1. Loading saat klik Filter
            const form = document.getElementById('filter-form');
            if(form) {
                form.addEventListener('submit', () => loader.style.display = 'flex');
            }

            // 2. Loading saat klik tombol Kembali
            const backBtns = document.querySelectorAll('.btn-loading');
            backBtns.forEach(btn => {
                btn.addEventListener('click', () => loader.style.display = 'flex');
            });

            // 3. Loading saat klik Pagination (Next/Prev Page)
            const paginations = document.querySelectorAll('.pagination-links a');
            paginations.forEach(link => {
                link.addEventListener('click', () => loader.style.display = 'flex');
            });
        });

        // Fix Back Button Browser
        window.addEventListener('pageshow', function(event) {
            const loader = document.getElementById('loading-overlay');
            if (event.persisted) {
                if(loader) loader.style.display = 'none';
            }
        });
    </script>
@endpush