@extends('layouts.app')

@section('title', $page_title . ' | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h3">{{ $page_title }}</h1>
        <p class="text-muted mb-0">
            Periode: <strong>{{ date('d M Y', strtotime($period->period_start)) }}</strong> s/d <strong>{{ date('d M Y', strtotime($period->period_end)) }}</strong> 
            | Tipe: <strong>{{ $ttype == 'student' ? 'Siswa' : 'Guru / Karyawan' }}</strong>
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('fincom.period.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Periode
        </a>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Pilih Proses yang Akan Dijalankan</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="35%">Nama Proses</th>
                    <th>Keterangan</th>
                    <th width="20%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- JIKA TIPE PERIODE ADALAH SISWA --}}
                @if($ttype == 'student')
                <tr>
                    <td class="text-center align-middle">1</td>
                    <td class="align-middle fw-bold">Proses SPP Murid</td>
                    <td class="align-middle text-muted">Generate tagihan bulanan (SPP, dll) untuk seluruh siswa aktif pada periode ini.</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="bi bi-play-circle"></i> Jalankan Proses
                        </a>
                    </td>
                </tr>
                @endif

                {{-- JIKA TIPE PERIODE ADALAH GURU/KARYAWAN --}}
                @if($ttype == 'teacher')
                <tr>
                    <td class="text-center align-middle">1</td>
                    <td class="align-middle fw-bold">Proses Draft Gaji</td>
                    <td class="align-middle text-muted">Menghitung rincian gaji kotor, potongan, dan tunjangan guru. (Masih bisa direvisi/reset)</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-warning btn-sm text-dark">
                            <i class="bi bi-play-circle"></i> Proses Draft
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="text-center align-middle">2</td>
                    <td class="align-middle fw-bold">Proses Finalisasi Gaji</td>
                    <td class="align-middle text-muted">Mengesahkan draft gaji menjadi final dan mempostingnya ke buku besar/jurnal. (Tidak bisa direvisi lagi)</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-danger btn-sm">
                            <i class="bi bi-check-circle"></i> Finalisasi
                        </a>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection