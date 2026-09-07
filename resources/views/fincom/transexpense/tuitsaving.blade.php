@extends('layouts.app')

@section('title', $page_title)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">{{ $page_title }}</h1>
</div>

{{-- FORM FILTER --}}
<div class="card shadow-sm border-0 mb-4 bg-light d-print-none">
    <div class="card-body">
        <form action="{{ route('fincom.transexpense.tuitsaving') }}" method="GET" autocomplete="off">
            <input type="hidden" name="filter" value="1">
            {{-- Ubah g-3 menjadi g-2 agar jarak antar kotak sedikit lebih rapat --}}
            <div class="row g-2 align-items-end">
                
                {{-- Jenis Pembayaran (Dipertahankan) --}}
                <div class="col-md-3">
                    <label class="fw-bold mb-1 small">Jenis Pembayaran :</label>
                    <select name="fpayitem" class="form-select form-select-sm">
                        <option value="0">-- Semua Jenis --</option>
                        @foreach($payitems as $pitem)
                            <option value="{{ $pitem->id }}" {{ $fpayitem == $pitem->id ? 'selected' : '' }}>{{ $pitem->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Awal (Ukurannya dikunci di 140px) --}}
                <div class="col-auto" style="width: 140px;">
                    <label class="fw-bold mb-1 small">Dari :</label>
                    <input type="date" name="awal" class="form-control form-control-sm" value="{{ $awal }}">
                </div>

                {{-- Tanggal Akhir (Ukurannya dikunci di 140px) --}}
                <div class="col-auto" style="width: 140px;">
                    <label class="fw-bold mb-1 small">Sampai :</label>
                    <input type="date" name="akhir" class="form-control form-control-sm" value="{{ $akhir }}">
                </div>

                {{-- Kasir --}}
                <div class="col-md-2">
                    <label class="fw-bold mb-1 small">Kasir :</label>
                    <select name="cas_id" class="form-select form-select-sm">
                        <option value="0">-- Semua Kasir --</option>
                        @foreach($cass as $cas)
                            <option value="{{ $cas->id }}" {{ $cas_id == $cas->id ? 'selected' : '' }}>{{ $cas->fullname }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipe Transaksi (Sudah diperlebar dan aman) --}}
                <div class="col-md-2">
                    <label class="fw-bold mb-1 small">Tipe :</label>
                    <select name="iscash" class="form-select form-select-sm">
                        <option value="all" {{ $iscash == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="yes" {{ $iscash == 'yes' ? 'selected' : '' }}>Tunai</option>
                        <option value="no" {{ $iscash == 'no' ? 'selected' : '' }}>Non-Tunai</option>
                    </select>
                </div>

                {{-- Tombol (Menggunakan class "col" agar otomatis mengisi sisa lebar yang ada) --}}
                <div class="col">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
                        
                        @if($isFilter && (count($terima) > 0 || count($keluar) > 0))
                            <a href="{{ route('fincom.transexpense.tuitsaving.print', request()->query()) }}" target="_blank" class="btn btn-secondary btn-sm w-100" title="Cetak Laporan">
                                <i class="bi bi-printer"></i> Cetak
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

    {{-- BAGIAN 1: PENERIMAAN --}}
    <div class="d-flex justify-content-between align-items-end mb-2 mt-4 border-bottom pb-2">
        <h5 class="mb-0 text-primary">A. Penerimaan</h5>
        <h5 class="mb-0 text-primary fw-bold">Grand Total: Rp {{ number_format($totalTerima, 0, ',', '.') }}</h5>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th class="text-center" width="100">Tanggal</th>
                            <th width="150">Referensi</th>
                            <th width="200">Nama Siswa</th>
                            <th width="150">Komponen</th>
                            <th>Note</th>
                            <th class="text-end" width="130">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terima as $index => $row)
                            <tr>
                                <td class="text-center">{{ $terima->firstItem() + $index }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                                <td>{{ $row->ref_no }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                <td>{{ $row->payitem }}</td>
                                <td>{{ $row->note }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data penerimaan pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($terima->hasPages())
            <div class="card-footer bg-white pb-0 pt-3">{{ $terima->links() }}</div>
        @endif
    </div>


    {{-- BAGIAN 2: PENGELUARAN / PENARIKAN TABUNGAN --}}
    <div class="d-flex justify-content-between align-items-end mb-2 mt-4 border-bottom pb-2">
        <h5 class="mb-0 text-danger">B. Pengeluaran</h5>
        <h5 class="mb-0 text-danger fw-bold">Grand Total: Rp {{ number_format($totalKeluar, 0, ',', '.') }}</h5>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th class="text-center" width="100">Tanggal</th>
                            <th width="150">Referensi</th>
                            <th width="200">Nama Siswa</th>
                            <th width="150">Komponen</th>
                            <th>Note</th>
                            <th class="text-end" width="130">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keluar as $index => $row)
                            <tr>
                                <td class="text-center">{{ $keluar->firstItem() + $index }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                                <td>{{ $row->ref_no }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                <td>{{ $row->payitem }}</td>
                                <td>{{ $row->note }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data penarikan tabungan pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($keluar->hasPages())
            <div class="card-footer bg-white pb-0 pt-3">{{ $keluar->links() }}</div>
        @endif
    </div>


    {{-- BAGIAN 3: RETUR TRANSAKSI --}}
    <div class="d-flex justify-content-between align-items-end mb-2 mt-4 border-bottom pb-2">
        <h5 class="mb-0 text-warning">C. Retur</h5>
        <h5 class="mb-0 text-warning fw-bold">Grand Total: Rp {{ number_format($totalRetur, 0, ',', '.') }}</h5>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th class="text-center" width="100">Tanggal</th>
                            <th width="150">Referensi</th>
                            <th width="200">Nama Siswa</th>
                            <th width="150">Komponen</th>
                            <th>Note</th>
                            <th class="text-end" width="130">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($retur as $index => $row)
                            <tr>
                                <td class="text-center">{{ $retur->firstItem() + $index }}</td>
                                <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                                <td>{{ $row->ref_no }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                <td>{{ $row->payitem }}</td>
                                <td>{{ $row->note }}</td>
                                <td class="text-end fw-bold text-warning">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada data retur transaksi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($retur->hasPages())
            <div class="card-footer bg-white pb-0 pt-3">{{ $retur->links() }}</div>
        @endif
    </div>

@else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search text-secondary" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Pilih Filter Pencarian</h5>
        <p>Silakan sesuaikan jenis, tanggal, dan kasir, lalu klik "Tampilkan".</p>
    </div>
@endif

@endsection