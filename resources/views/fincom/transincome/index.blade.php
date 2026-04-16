@extends('layouts.app')

@section('title', 'Daftar Pemasukan | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Daftar Pemasukan</h1>       
    </div>
    
    {{-- Menampilkan pesan error/success --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- BARIS FILTER & AKSI --}}
    <form action="{{ route('fincom.transincome.index') }}" method="GET" class="mb-3">
        <div class="card border-start border-4 border-success shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-end g-2 small">
                    
                    {{-- 1. Tombol Tambah --}}
                    <div class="col-md-2">
                        <a href="{{ route('fincom.transincome.create') }}" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="bi bi-plus-circle"></i> Tambah Pemasukan
                        </a>
                    </div>

                    {{-- 2. Filter Tanggal --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="awal" id="awal" class="form-control" value="{{ $req->awal }}" placeholder="Awal">
                            <span class="input-group-text bg-light">s.d</span>
                            <input type="date" name="akhir" id="akhir" class="form-control" value="{{ $req->akhir }}" placeholder="Akhir">
                        </div>
                    </div>

                    {{-- 3. Filter Kasir --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">Kasir</label>
                        <select name="cas_id" id="cas_id" class="form-select form-select-sm">
                            <option value="0" {{ $req->cas_id == '0' ? 'selected' : '' }}>-- Semua Kasir --</option>
                            @foreach($cashiers as $cas)
                                <option value="{{ $cas->id }}" {{ $req->cas_id == $cas->id ? 'selected' : '' }}>
                                    {{ $cas->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Tombol Filter & Print --}}
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" name="filter_btn" value="1" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cetakLaporan()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA PEMASUKAN --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%" class="text-center">Tanggal</th>
                            <th width="12%">Referensi</th>
                            <th width="15%">Kasir</th>
                            <th width="15%">Diterima Dari</th> {{-- Di CI2 tertulis 'Kepada', kita buat lebih logis --}}
                            <th>Keterangan</th>
                            <th width="12%" class="text-end">Nominal</th>
                            <th width="5%" class="text-center">Print</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $row)
                            <tr>
                                <td class="text-center">{{ date('d M Y', strtotime($row->tdate)) }}</td>
                                <td class="fw-bold text-success">{{ $row->ref_no }}</td>
                                <td>{{ $row->cashier_name ?? '-' }}</td>
                                <td>{{ $row->payto ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">{{ $row->note }}</small>
                                </td>
                                <td class="text-end fw-bold">
                                    {{-- Mengambil nilai tertinggi antara debit/credit untuk menghindari error data migrasi --}}
                                    {{ number_format($row->debit > 0 ? $row->debit : $row->credit, 0, ',', '.') }}
                                </td>
                                
                                {{-- Tombol Print Kwitansi (Akan kita buat fiturnya nanti jika dibutuhkan) --}}
                                <td class="text-center">
                                    <a href="javascript:alert('Fitur print kwitansi pemasukan akan segera dibuat!');" class="btn btn-sm btn-info text-white" title="Print Kwitansi">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                                
                                {{-- Tombol Aksi (Edit) --}}
                                <td class="text-center">                                    
                                    <a href="javascript:void(0);" 
                                       onclick="confirmEdit('{{-- route('fincom.transincome.edit', $row->id) --}}')" 
                                       class="btn btn-warning btn-sm" title="Edit Pemasukan">
                                       <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data transaksi pemasukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION LARAVEL --}}
            <div class="p-3 d-flex justify-content-end border-top">                
                {{ $incomes->withQueryString()->links() }}
            </div>
        </div>
    </div>

<script>
// Fungsi Confirm Edit
function confirmEdit(url) {
    if (confirm('Mengedit data akan menghapus data transaksi lama dan menggantinya dengan yang baru, lanjutkan?')) {
        // window.location.href = url; // Diaktifkan nanti jika route edit sudah ada
        alert('Fitur Form Edit akan kita buat di langkah selanjutnya!');
    }
}

// Fungsi Cetak Laporan (Mirip dengan Expense)
function cetakLaporan() {
    let casId = document.getElementById('cas_id').value;
    let awal = document.getElementById('awal').value;
    let akhir = document.getElementById('akhir').value;

    if (!awal || !akhir) {
        alert('Silakan pilih rentang Tanggal Awal dan Akhir terlebih dahulu sebelum mencetak laporan.');
        return; 
    }

    let startDate = new Date(awal);
    let endDate = new Date(akhir);
    let diffTime = endDate.getTime() - startDate.getTime();
    let diffDays = diffTime / (1000 * 3600 * 24);

    if (diffDays < 0) {
        alert('Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal!');
        return;
    }

    if (diffDays > 366) {
        alert('Rentang waktu cetak laporan maksimal adalah 1 Tahun (365 Hari). Silakan persempit filter tanggal Anda.');
        return; 
    }

    // Arahkan ke URL cetak (Nanti akan kita buat controller p_income)
    let baseUrl = "{{ url('fincom/transincome/p_income') }}";
    let printUrl = `${baseUrl}/${casId}/${awal}/${akhir}`;
    
    // Buka tab baru untuk sementara kita beri alert dulu sebelum fiturnya ada
    alert('Akan membuka tab baru ke: \n' + printUrl + '\n(Fitur akan dibuat di tahap selanjutnya)');
    // window.open(printUrl, '_blank'); 
}
</script>
@endsection