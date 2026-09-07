@extends('layouts.app') 

@section('title', $page_title)

@section('content')
<div class="container-fluid py-4">
    
    {{-- HEADER LAPORAN --}}
    <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
        <h3 class="mb-0 text-primary"><i class="bi bi-calendar3"></i> {{ $page_title }}</h3>
        <div class="text-end text-muted small">
            Created: <strong>{{ date('d-M-Y') }}</strong>
        </div>
    </div>

    {{-- INFORMASI SISWA --}}
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <table cellpadding="4" style="font-size: 14px;">
                <tr>
                    <td width="100"><strong>Nama</strong></td>
                    <td>: <span class="fw-bold">{{ $student->fullname }}</span></td>
                </tr>
                <tr>
                    <td><strong>Kelas</strong></td>
                    <td>: {{ $student->kelas }} {{ $student->tgroup }} {{ $student->tsubject }}</td>
                </tr>
                <tr>
                    <td><strong>Tahun</strong></td>
                    <td>: <span class="badge bg-primary fs-6">{{ $year }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- TABEL REKAP 12 BULAN --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle" style="font-size: 13px;">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="3%">No</th>
                            <th width="15%" class="text-start">Pembayaran</th>
                            <th>Jan</th><th>Feb</th><th>Mar</th>
                            <th>Apr</th><th>Mei</th><th>Jun</th>
                            <th>Jul</th><th>Ags</th><th>Sep</th>
                            <th>Okt</th><th>Nov</th><th>Des</th>
                            <th class="bg-primary">Total</th>
                        </tr>
                    </thead>  
                    <tbody>    
                        @php
                            $n = 1;
                            $totalall = 0;
                            // Siapkan array untuk menampung total per bulan (Kolom Bawah)
                            $totalBulan = array_fill(1, 12, 0);
                        @endphp  

                        @forelse($lrcv as $rcv)
                            <tr>
                                <td class="text-center">{{ $n++ }}</td>
                                <td class="fw-bold text-nowrap">{{ $rcv['title'] }}</td>
                                
                                {{-- LOOPING 12 BULAN PER BARIS --}}
                                @php $subtot = 0; @endphp
                                @for($m = 1; $m <= 12; $m++)
                                    @php 
                                        $val = $rcv['mons'][$m]; 
                                        $subtot += $val;
                                        $totalBulan[$m] += $val;
                                    @endphp
                                    <td class="text-end {{ $val > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                        {{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}
                                    </td>
                                @endfor
                                
                                {{-- TOTAL PER BARIS (KANAN) --}}
                                @php $totalall += $subtot; @endphp
                                <td class="text-end fw-bold bg-light text-primary">
                                    {{ number_format($subtot, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-4 text-muted">Belum ada data pembayaran di tahun ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- TOTAL KESELURUHAN (BAWAH) --}}
                    @if(count($lrcv) > 0)
                    <tfoot class="table-secondary fw-bold text-end">
                        <tr>
                            <td colspan="2" class="text-center text-uppercase">Total Keseluruhan</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td>{{ $totalBulan[$m] > 0 ? number_format($totalBulan[$m], 0, ',', '.') : '-' }}</td>
                            @endfor
                            <td class="text-primary fs-6">{{ number_format($totalall, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4 text-end">
        <button onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Cetak Laporan</button>
        <a href="{{ route('fincom.payment.index') }}" class="btn btn-outline-dark">Kembali</a>
    </div>

</div>

<style>
    /* Sembunyikan tombol cetak saat diprint */
    @media print {
        .btn, .navbar, .sidebar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table-dark { color: #000 !important; background-color: #eee !important; }
    }
</style>
@endsection