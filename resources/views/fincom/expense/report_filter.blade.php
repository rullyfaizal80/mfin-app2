@extends('layouts.app')

@section('title', 'Laporan Transaksi per Akun | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Transaksi per Akun</h1>
    </div>
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <div class="card">
        <div class="card-header bg-primary text-white fw-bold">
            Filter Laporan Transaksi
        </div>
        <div class="card-body">
            {{-- Form mengirim request secara GET ke rute Print, dan membuka di Tab Baru --}}
            <form action="{{ route('fincom.expense.report.print') }}" method="GET" target="_blank">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jenis Komponen</label>
                        <select name="coa_code" class="form-select">
                            <option value="0">-- Pilih Akun --</option>
                            @foreach($coas as $coa)
                                <option value="{{ $coa->coa_code }}">{{ $coa->coa_code }} - {{ $coa->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" name="awal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" name="akhir" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-printer"></i> Tampilkan & Cetak</button>
                    </div>
                </div>

                <hr>
                <h6 class="text-muted fw-bold mb-3">Filter Opsional (Alokasi Kelas & Siswa)</h6>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Sekolah</label>
                        <select name="school_id" class="form-select form-select-sm">
                            <option value="0">-- Semua Sekolah --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Level / Grade</label>
                        <select name="level_id" class="form-select form-select-sm">
                            <option value="0">-- Semua Level --</option>
                            @foreach($grades as $gd)
                                <option value="{{ $gd->id }}">{{ $gd->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="0">-- Semua Kelas --</option>
                            @foreach($classes as $cs)
                                <option value="{{ $cs->id }}">{{ $cs->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Unit</label>
                        <select name="sunit" class="form-select form-select-sm border-primary">
                            <option value="all">-- Semua Unit --</option>
                            @foreach($sunits as $su)
                                <option value="{{ $su }}">{{ $su }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection