@extends('layouts.app')

@section('title', 'Laporan Data Kelas | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Data Kelas</h1>
    </div>

    {{-- CARD FILTER --}}
    <div class="card mb-4">
        <div class="card-header bg-body-tertiary fw-bold">
            <i class="bi bi-funnel"></i> Filter Data
        </div>
        <div class="card-body">
            <form action="{{ route('reports.class_list.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    
                    {{-- Filter Sekolah --}}
                    <div class="col-md-3">
                        <label class="form-label small">Sekolah</label>
                        <select name="f_cschool" class="form-select form-select-sm">
                            <option value="">- Semua Sekolah -</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->name }}" {{ $f_cschool == $sch->name ? 'selected' : '' }}>
                                    {{ $sch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tahun Ajaran (Range) --}}
                    <div class="col-md-3">
                        <label class="form-label small">Tahun (Mulai - Akhir)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="f_cyear_start" class="form-control" placeholder="YYYY" value="{{ $f_cyear_start }}">
                            <span class="input-group-text">-</span>
                            <input type="number" name="f_cyear_end" class="form-control" placeholder="YYYY" value="{{ $f_cyear_end }}">
                        </div>
                    </div>

                    {{-- Filter Tingkat --}}
                    <div class="col-md-2">
                        <label class="form-label small">Tingkat</label>
                        <select name="f_cgrade" class="form-select form-select-sm">
                            <option value="">- Semua -</option>
                            @foreach($grades as $grd)
                                <option value="{{ $grd->title }}" {{ $f_cgrade == $grd->title ? 'selected' : '' }}>
                                    {{ $grd->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tipe --}}
                    <div class="col-md-2">
                        <label class="form-label small">Tipe</label>
                        <select name="f_ctype" class="form-select form-select-sm">
                            <option value="">- Semua -</option>
                            @foreach($types as $tp)
                                <option value="{{ $tp->title }}" {{ $f_ctype == $tp->title ? 'selected' : '' }}>
                                    {{ $tp->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            {{-- Tombol Cetak (Membuka Tab Baru dengan Parameter Filter) --}}
                            <a href="{{ route('reports.class_list.print', request()->all()) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-printer"></i> Cetak
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Kelas</th>
                            <th width="20%">Tingkat / Grup</th>
                            <th width="15%">Tipe</th>
                            <th width="15%">Sekolah</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $index => $row)
                            <tr>
                                <td>{{ $classes->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $row->class_name }}</strong><br>
                                    <small class="text-muted">{{ $row->year_title }} - {{ $row->subject_title }}</small>
                                </td>
                                <td>{{ $row->grade_title }} {{ $row->group_title }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $row->type_title }}</span>
                                </td>
                                <td>{{ $row->school_name }}</td>
                                <td class="text-center">
                                    {{-- Link ke Daftar Siswa (Placeholder link) --}}
                                    <a href="{{ route('reports.class_user.index', $row->id) }}" class="btn btn-xs btn-info text-white" title="Lihat Siswa">
                                        <i class="bi bi-people"></i> Siswa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Data kelas tidak ditemukan dengan filter tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $classes->links() }}
            </div>
        </div>
    </div>
@endsection