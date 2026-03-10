@extends('layouts.app')

@section('title', 'Daftar Pengeluaran | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Daftar Pengeluaran</h1>       
    </div>
    
    {{-- BARIS FILTER & AKSI (Meniru Layout Tabel Header CI2 Lama) --}}
    <form action="{{ route('fincom.transexpense.index') }}" method="GET" class="mb-3">
        <div class="card border-start border-4 border-primary shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-end g-2 small">
                    
                    {{-- 1. Tombol Tambah (Sisi Kiri Seperti CI2) --}}
                    <div class="col-md-2">
                        {{-- Catatan: Sesuaikan nama route-nya nanti jika sudah dibuat --}}
                        <a href="{{ url('fincom/transexpense/create') }}" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="bi bi-plus-circle"></i> Tambah
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

                    {{-- 4. Tombol Filter & Print (Sisi Kanan Seperti CI2) --}}
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

    {{-- TABEL DATA PENGELUARAN --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%" class="text-center">Tanggal</th>
                            <th width="12%">Referensi</th>
                            <th width="15%">Kasir</th>
                            <th width="15%">Dibayar Kepada</th>
                            <th>Keterangan</th>
                            <th width="12%" class="text-end">Total</th>
                            <th width="5%" class="text-center">Print</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $row)
                            <tr>
                                <td class="text-center">{{ date('d M Y', strtotime($row->tdate)) }}</td>
                                <td class="fw-bold text-primary">{{ $row->ref_no }}</td>
                                <td>{{ $row->cashier_name ?? '-' }}</td>
                                <td>{{ $row->payto ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">{{ $row->note }}</small>
                                </td>
                               <td class="text-end fw-bold">
            {{-- Logika Pintar: Ambil nilai yang tidak nol agar data lama CI2 tetap terbaca --}}
            {{ number_format($row->debit > 0 ? $row->debit : $row->credit, 0, ',', '.') }}
        </td>
                                
                                {{-- Tombol Print Kwitansi Per Baris (Meniru CI2) --}}
                                <td class="text-center">
                                    <a href="{{ url('fincom/transexpense/receipt/' . $row->id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Print Kwitansi">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                                
                                {{-- Tombol Aksi (Edit) --}}
                                <td class="text-center">
                                    <a href="{{ url('fincom/transexpense/edit/' . $row->id) }}" class="btn btn-sm btn-warning" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data transaksi pengeluaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION LARAVEL --}}
            <div class="p-3 d-flex justify-content-end border-top">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>

    <script>
function cetakLaporan() {
    // 1. Ambil nilai dari input filter
    // Sesuaikan ID ini dengan ID input form filter Anda
    let casId = document.getElementById('cas_id').value;
    let awal = document.getElementById('awal').value;
    let akhir = document.getElementById('akhir').value;

    // 2. Validasi: Apakah tanggal sudah diisi?
    if (!awal || !akhir) {
        alert('Silakan pilih rentang Tanggal Awal dan Akhir terlebih dahulu sebelum mencetak laporan.');
        return; // Hentikan proses, tab baru tidak akan dibuka
    }

    // 3. Validasi: Apakah rentang waktu lebih dari 1 tahun (366 hari)?
    let startDate = new Date(awal);
    let endDate = new Date(akhir);
    
    // Hitung selisih waktu dalam milidetik, lalu ubah ke hari
    let diffTime = endDate.getTime() - startDate.getTime();
    let diffDays = diffTime / (1000 * 3600 * 24);

    // Cek jika tanggal akhir lebih kecil dari tanggal awal (mundur)
    if (diffDays < 0) {
        alert('Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal!');
        return;
    }

    // Cek batasan 1 tahun
    if (diffDays > 366) {
        alert('Rentang waktu cetak laporan maksimal adalah 1 Tahun (365 Hari). Silakan persempit filter tanggal Anda.');
        return; // Hentikan proses
    }

    // 4. Jika semua validasi lolos, buka tab baru untuk Print!
    let baseUrl = "{{ url('fincom/transexpense/p_expense') }}";
    let printUrl = `${baseUrl}/${casId}/${awal}/${akhir}`;
    
    window.open(printUrl, '_blank');
}
</script>
@endsection