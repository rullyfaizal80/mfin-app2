@extends('layouts.app')

@section('title', 'Tabungan | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tabungan Siswa & Guru</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-sm btn-success me-2"><i class="bi bi-plus-circle"></i> Setoran Baru</a>
        </div>
    </div>

    {{-- Card Daftar Nasabah --}}
    <div class="card">
        <div class="card-header bg-body-tertiary">
            {{-- Form Filter & Search dalam satu baris --}}
            <form action="{{ route('savings.index') }}" method="GET" class="row g-2 align-items-center">
                
                {{-- Input Pencarian --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Cari Nama Nasabah..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter Tanggal (Opsional) --}}
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Dari</span>
                        <input type="date" name="ffrom" class="form-control" value="{{ $ffrom }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Sampai</span>
                        <input type="date" name="fto" class="form-control" value="{{ $fto }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100" type="submit">Terapkan</button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Nasabah</th>
                            <th>ID (NIS/NIK)</th>
                            <th class="text-center">Trans. Terakhir</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($savings as $index => $row)
                            <tr>
                                <td>{{ $savings->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $row->fullname }}</strong>
                                </td>
                                <td>{{ $row->nis ?? ($row->nik ?? '-') }}</td>
                                <td class="text-center text-muted small">
                                    {{ $row->last_transaction ? date('d-m-Y', strtotime($row->last_transaction)) : '-' }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    Rp {{ number_format($row->total_saldo, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('savings.show', $row->user_id) }}" class="btn btn-xs btn-info text-white" title="Lihat Rincian">
                                        <i class="bi bi-list-ul"></i> Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data tabungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $savings->links() }}
            </div>
        </div>
    </div>
@endsection