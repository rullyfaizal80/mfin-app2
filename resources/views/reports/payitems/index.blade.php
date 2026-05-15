@extends('layouts.app')

@section('title', 'Pengecekan Komponen Keuangan | MIMHa Finance')

@section('content')
    {{-- BARIS JUDUL UTAMA --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 text-body">{{ $page_title }}</h1>       
    </div>
    
    {{-- BARIS FILTER & AKSI (Seragam dengan Format MIMHa Finance) --}}
    <form action="{{ route('reports.payitems') }}" method="POST" class="mb-4">
        @csrf
    {{-- KELAS TANPA KOMPONEN BIAYA (NOTIFIKASI ATAS) --}}
    @if(!empty($wopi))
    <div class="alert alert-warning d-flex align-items-center shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong>Kelas Tanpa Komponen Biaya:</strong> 
            {{-- Mengubah text-dark menjadi text-warning-emphasis agar adaptif di mode dark --}}
            <span class="font-monospace text-warning-emphasis fw-bold">{{ $wopi }}</span>
        </div>
    </div>
    @endif

    {{-- BLOCK TABEL 1: KOMPONEN SATUAN SISWA --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-dark text-white fw-bold py-2">
            <i class="bi bi-collection-play-fill me-1"></i> 1. Komponen Satuan Siswa
        </div>
        <div class="card-body p-0 bg-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th width="8%" class="text-center">No</th>
                            <th width="12%">Kode</th>    
                            <th width="20%">Nama Komponen (Title)</th>
                            <th width="15%">Pengguna (User)</th>
                            <th>Struktur Akun Keterangan (Tipe / RC / CS / CS2 / PY / RV / VL)</th>
                            <th width="20%" class="text-center">Status Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($e_payitem as $index => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $row['payitem_code'] }}</td>    
                                <td class="fw-bold">{{ $row['title'] }}</td>
                                <td><span class="badge bg-secondary">{{ $row['payitem_user'] ?? 'ALL' }}</span></td>
                                <td class="font-monospace text-muted">
                                    {{ $row['payitem_type'] }} / {{ $row['coa_receivable'] }}-{{ $row['coa_cash'] }}-{{ $row['coa_cost'] }}-{{ $row['coa_payable'] }}-{{ $row['coa_revenue'] }} / {{ number_format($row['payvalue'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill"></i> {{ $row['error'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-success fw-bold">
                                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>
                                    Alhamdulillah, tidak ada error pada struktur komponen induk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BLOCK TABEL 2: KOMPONEN PER KELAS --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-dark text-white fw-bold py-2">
            <i class="bi bi-building me-1"></i> 2. Komponen Per Kelas
        </div>
        <div class="card-body p-0 bg-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th width="8%" class="text-center">No</th>
                            <th width="15%">Kelas</th>
                            <th width="12%">Kode</th>    
                            <th width="20%">Nama Komponen (Title)</th>
                            <th>Struktur Akun Keterangan (RC-CS-CS2-PY-RV / Nilai)</th>
                            <th width="20%" class="text-center">Status Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($e_cpayitem as $index => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td class="fw-bold text-success">{{ $row['class'] }}</td>
                                <td class="fw-bold text-primary">{{ $row['payitem_code'] }}</td>    
                                <td class="fw-bold">{{ $row['title'] }}</td>
                                <td class="font-monospace text-muted">
                                    {{ $row['coa_receivable'] }}-{{ $row['coa_cash'] }}-{{ $row['coa_cost'] }}-{{ $row['coa_payable'] }}-{{ $row['coa_revenue'] }} / {{ number_format($row['payvalue'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill"></i> {{ $row['error'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-success fw-bold">
                                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>
                                    Alhamdulillah, seluruh relasi komponen kelas sudah seimbang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BLOCK TABEL 3: KOMPONEN PEMBAYARAN PER MURID --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold py-2">
            <i class="bi bi-people-fill me-1"></i> 3. Komponen Pembayaran Per Murid
        </div>
        <div class="card-body p-0 bg-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th width="8%" class="text-center">No</th>
                            <th width="12%">Kelas</th>
                            <th width="20%">Nama Lengkap Murid</th>
                            <th width="12%">Kode</th>    
                            <th width="15%">Nama Komponen</th>
                            <th>Struktur Akun Keterangan (RC-CS-CS2-PY-RV / Nilai)</th>
                            <th width="20%" class="text-center">Status Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($e_upayitem as $index => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td class="text-success fw-bold">{{ $row['class'] }}</td>
                                <td class="fw-bold text-primary">{{ $row['fullname'] }}</td>
                                <td class="font-monospace">{{ $row['payitem_code'] }}</td>    
                                <td>{{ $row['title'] }}</td>
                                <td class="font-monospace text-muted">
                                    {{ $row['coa_receivable'] }}-{{ $row['coa_cash'] }}-{{ $row['coa_cost'] }}-{{ $row['coa_payable'] }}-{{ $row['coa_revenue'] }} / {{ number_format($row['payvalue'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill"></i> {{ $row['error'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-success fw-bold bg-body">
                                    <i class="bi bi-stars text-warning fs-1 d-block mb-2"></i>
                                    <span>Alhamdulillah, Seluruh Struktur Akun Komponen Murid Sudah Lengkap & Valid!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
