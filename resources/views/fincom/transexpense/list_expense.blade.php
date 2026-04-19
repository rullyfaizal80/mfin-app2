@extends('layouts.app')

@section('title', 'Laporan Per Komponen | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Laporan Per Komponen</h1>
    <div class="btn-toolbar mb-2 mb-md-0">        
        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="printPreview()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

{{-- FORM FILTER --}}
<div class="card shadow-sm mb-4 no-print">
    <div class="card-body">
        <form action="{{ route('fincom.transexpense.list_expense') }}" method="GET" class="row g-3" id="filterForm">
            <div class="col-md-4">
                <label class="form-label fw-bold">Jenis Komponen</label>
                <select name="payitem_id" class="form-select select2">
                    <option value="0">-- Semua Komponen --</option>
                    @foreach($payitems as $pi)
                        {{-- Label diganti ke Pemasukan/Pengeluaran --}}
                        <option value="{{ $pi->id }}" {{ $payitemId == $pi->id ? 'selected' : '' }}>
                            {{ $pi->payitem_type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}: {{ $pi->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Dari Tanggal</label>
                <input type="date" name="awal" class="form-control" value="{{ $awal }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" name="akhir" class="form-control" value="{{ $akhir }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        {{-- Hapus class table-responsive jika ingin benar-benar full fit layar tanpa scroll --}}
        <div style="overflow-x: hidden;"> 
            <table class="table table-bordered table-hover mb-0" style="table-layout: fixed; width: 100%;">
                <thead class="table-dark text-center align-middle">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="width: 12%;">Referensi</th>
                        <th style="width: 14%;">Komponen</th>
                        <th style="width: 12%;">Petugas</th>
                        <th style="width: 15%;">Kepada</th>
                        <th style="width: auto;">Keterangan</th>
                        <th style="width: 13%;">Nominal (Rp)</th>
                    </tr>
                </thead>
                
                {{-- SEKSI PEMASUKAN --}}
                <tbody class="table-light">
                    <tr>
                        <td colspan="8" class="fw-bold bg-success text-white">I. PEMASUKAN</td>
                    </tr>
                </tbody>
                <tbody>
                    @php $totalIncome = 0; $noIn = 1; @endphp
                    @foreach($results as $row)
                        @if($row->payitem_type == 'income')
                        @php 
                            $nominal = $row->credit > 0 ? $row->credit : $row->debit;
                            $totalIncome += $nominal; 
                        @endphp
                        <tr>
                            <td class="text-center">{{ $noIn++ }}</td>
                            <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                            <td class="text-center fw-bold">{{ $row->ref_no }}</td>
                            <td>{{ $row->component_name }}</td>
                            <td>{{ $row->fullname }}</td>
                            <td>{{ $row->parent_payto ? $row->parent_payto : ($row->payto ? $row->payto : '-') }}</td>
                            <td style="word-wrap: break-word; white-space: normal;">
                                <small>{{ $row->note ?: '-' }}</small>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($nominal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold table-success">
                        <td colspan="7" class="text-end">Sub Total Pemasukan :</td>
                        <td class="text-end">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                    </tr>
                </tbody>

                {{-- SEKSI PENGELUARAN --}}
                <tbody class="table-light">
                    <tr>
                        <td colspan="8" class="fw-bold bg-danger text-white">II. PENGELUARAN</td>
                    </tr>
                </tbody>
                <tbody>
                    @php $totalExpense = 0; $noEx = 1; @endphp
                    @foreach($results as $row)
                        @if($row->payitem_type == 'expense')
                        @php 
                            $nominal = $row->debit > 0 ? $row->debit : $row->credit;
                            $totalExpense += $nominal; 
                        @endphp
                        <tr>
                            <td class="text-center">{{ $noEx++ }}</td>
                            <td class="text-center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                            <td class="text-center fw-bold">{{ $row->ref_no }}</td>
                            <td>{{ $row->component_name }}</td>
                            <td>{{ $row->fullname }}</td>
                            <td>{{ $row->parent_payto ? $row->parent_payto : ($row->payto ? $row->payto : '-') }}</td>
                            <td style="word-wrap: break-word; white-space: normal;">
                                <small>{{ $row->note ?: '-' }}</small>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($nominal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="fw-bold table-danger">
                        <td colspan="7" class="text-end">Sub Total Pengeluaran :</td>
                        <td class="text-end">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                    </tr>
                </tbody>                
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .navbar, .sidebar, .btn-toolbar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        body { background: white; padding: 0; margin: 0; }
        .table-dark { color: black !important; background: #eee !important; }
        .bg-success, .bg-danger { -webkit-print-color-adjust: exact; color: white !important; }
    }
</style>

<script>
// Validasi Filter Maksimal 1 Tahun
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const awalInput = document.querySelector('input[name="awal"]').value;
    const akhirInput = document.querySelector('input[name="akhir"]').value;

    if (awalInput && akhirInput) {
        const dAwal = new Date(awalInput);
        const dAkhir = new Date(akhirInput);

        // 1. Cek apakah tanggal terbalik
        if (dAkhir < dAwal) {
            e.preventDefault(); // Cegah loading
            alert("⚠️ Peringatan: Tanggal 'Sampai' tidak boleh lebih lampau dari tanggal 'Dari'.");
            return;
        }

        // 2. Hitung selisih hari
        const diffTime = Math.abs(dAkhir - dAwal);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

        // 3. Batasi maksimal 366 hari (1 tahun + toleransi tahun kabisat)
        if (diffDays > 366) {
            e.preventDefault(); // Cegah loading (mencegah crash)
            alert("🚨 Peringatan: Rentang waktu filter maksimal adalah 1 Tahun (365 hari).\n\nSilakan perkecil rentang tanggal untuk mencegah aplikasi menjadi berat atau error.");
        }
    }
});

function printPreview() {
    // Gunakan JavaScript Murni (Vanilla JS) agar tidak bergantung pada urutan load jQuery
    const payitemSelect = document.querySelector('select[name="payitem_id"]');
    const awalInput = document.querySelector('input[name="awal"]');
    const akhirInput = document.querySelector('input[name="akhir"]');

    const payitem_id = payitemSelect ? payitemSelect.value : '0';
    const awal = (awalInput && awalInput.value) ? awalInput.value : 'all';
    const akhir = (akhirInput && akhirInput.value) ? akhirInput.value : 'all';
    
    // Bangun URL Preview
    const url = "{{ route('fincom.transexpense.p_list_expense', [':id', ':awal', ':akhir']) }}"
                .replace(':id', payitem_id)
                .replace(':awal', awal)
                .replace(':akhir', akhir);
                
    // Buka di Tab Baru
    window.open(url, '_blank');
}
</script>

@endsection