@extends('layouts.app')

@section('title', 'Pencarian Data Siswa | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Pencarian Data Siswa</h1>
    </div>

    {{-- FORM PENCARIAN --}}
    <form action="{{ route('school.reportstudent.index') }}" method="GET">
        <div class="card mb-4 border-start border-4 border-primary">
            <div class="card-body p-3">
                <h6 class="card-title fw-bold text-primary mb-3">Filter Pencarian</h6>
                
                <div class="row g-2 mb-3 small">
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">Sekolah</label>
                        <select name="school_id" class="form-select form-select-sm border-primary">
                            <option value="0">- Semua Sekolah -</option>
                            @foreach($schools as $sc)
                                <option value="{{ $sc->id }}" {{ $req->school_id == $sc->id ? 'selected' : '' }}>
                                    {{ $sc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1">NIS / NISN</label>
                        <input type="text" name="snis" class="form-control form-control-sm" value="{{ $req->snis }}" placeholder="Cari NIS...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1">Nama Siswa</label>
                        <input type="text" name="snama" class="form-control form-control-sm" value="{{ $req->snama }}" placeholder="Nama Siswa...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1">Nama Ortu</label>
                        <input type="text" name="sortu" class="form-control form-control-sm" value="{{ $req->sortu }}" placeholder="Ayah / Ibu...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">Alamat</label>
                        <input type="text" name="salamat" class="form-control form-control-sm" value="{{ $req->salamat }}" placeholder="Jalan / Kota...">
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end border-top pt-3">
                    @if($req->anyFilled(['school_id', 'snis', 'snama', 'sortu', 'salamat']))
                        <a href="{{ route('school.reportstudent.index') }}" class="btn btn-secondary btn-sm px-3">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-search"></i> Cari Data
                    </button>
                    @if($students->count() > 0)
                        <button type="submit" 
                                formaction="{{ route('school.reportstudent.print') }}" 
                                formtarget="_blank" 
                                class="btn btn-outline-danger btn-sm px-4">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
            <span><i class="bi bi-people-fill me-2"></i> Hasil Pencarian</span>
            <span class="badge bg-secondary">{{ $students->total() }} Data Ditemukan</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Tgl Lahir</th> {{-- KOLOM BARU --}}
                            <th>Kelas</th>
                            <th>Orang Tua</th>
                            <th>Alamat</th>
                            <th class="text-center" width="10%">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $row)
                            <tr>
                                <td class="text-center">{{ $students->firstItem() + $index }}</td>
                                <td>{{ $row->nis ?? '-' }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                
                                {{-- KOLOM BARU: FORMAT 3 HURUF BULAN --}}
                                <td>
                                    @if($row->dateofbirth)
                                        {{ date('d M Y', strtotime($row->dateofbirth)) }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($row->class_name)
                                        <span class="badge bg-info text-dark">{{ $row->class_name }}</span><br>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $row->school_name }}</small>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small style="font-size: 0.8rem">
                                        A: {{ $row->father_name ?? '-' }}<br>
                                        I: {{ $row->mother_name ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted" style="font-size: 0.8rem">
                                        {{ \Illuminate\Support\Str::limit($row->full_address, 30) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('school.report.detail', $row->user_id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                        <i class="bi bi-file-earmark-person"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-2 d-block mb-2"></i>
                                    Tidak ada data siswa yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-end">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection