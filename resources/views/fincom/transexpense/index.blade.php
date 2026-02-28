@extends('layouts.app')

@section('title', 'Daftar Pengeluaran | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Daftar Pengeluaran (Expense)</h1>
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
                            <input type="date" name="awal" class="form-control" value="{{ $req->awal }}" placeholder="Awal">
                            <span class="input-group-text bg-light">s.d</span>
                            <input type="date" name="akhir" class="form-control" value="{{ $req->akhir }}" placeholder="Akhir">
                        </div>
                    </div>

                    {{-- 3. Filter Kasir --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">Kasir</label>
                        <select name="cas_id" class="form-select form-select-sm">
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
                        
                        {{-- Tombol Print Laporan --}}
                        {{-- Catatan: Route print disesuaikan nanti, ini mengambil nilai dari filter saat ini --}}
                        <a href="{{ url('fincom/transexpense/p_expense/' . ($req->cas_id ?? 0) . '/' . ($req->awal ?? 'all') . '/' . ($req->akhir ?? 'all')) }}" 
                           target="_blank" class="btn btn-outline-danger btn-sm flex-fill">
                            <i class="bi bi-printer"></i> Print
                        </a>
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
                                    {{ number_format($row->credit, 0, ',', '.') }}
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
@endsection