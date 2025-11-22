@extends('layouts.app')

@section('title', 'Rincian Tabungan | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Rincian Tabungan</h1>
            <span class="text-muted">Nasabah: <strong>{{ $user->fullname }}</strong></span>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('savings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Info Saldo Header --}}
    <div class="alert alert-info d-flex justify-content-between align-items-center py-2">
        <span>Total Saldo Saat Ini:</span>
        <span class="fs-5 fw-bold">Rp {{ number_format($saldo_total, 0, ',', '.') }}</span>
    </div>

    {{-- Filter Tanggal --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('savings.show', $user->id) }}" method="GET" class="row g-2 align-items-end">
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
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $total_in = 0; 
                            $total_out = 0; 
                        @endphp
                        @forelse ($transactions as $row)
                            @php
                                $total_in += $row->credit;
                                $total_out += $row->debit;
                            @endphp
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="fw-bold border-top">
                        <tr>
                            <td colspan="3" class="text-end">TOTAL (Periode Ini):</td>
                            <td class="text-end text-success">{{ number_format($total_in, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($total_out, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection