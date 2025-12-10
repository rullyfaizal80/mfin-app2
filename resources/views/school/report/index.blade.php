@extends('layouts.app')

@section('title', 'Laporan Data Kelas | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Data Kelas Siswa</h1>
    </div>

    {{-- FORM FILTER --}}
    <form action="{{ route('school.report.index') }}" method="GET">
        <div class="card mb-4 border-start border-4 border-primary">
            <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-3">Filter Tampilan & Cetak</h5>
                
                {{-- AREA CHECKBOX --}}
                <div class="row g-3 mb-4">
                    {{-- Kolom 1 --}}
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nis" id="nis" {{ $req->nis ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="nis">NIS</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="fullname_sis" id="fullname_sis" {{ $req->fullname_sis ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="fullname_sis">Nama Siswa</label>
                        </div>
                    </div>
                    
                    {{-- Kolom 2 --}}
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="old" id="old" {{ $req->old ? 'checked' : '' }}>
                            <label class="form-check-label" for="old">Tanggal Lahir</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="adress" id="adress" {{ $req->adress ? 'checked' : '' }}>
                            <label class="form-check-label" for="adress">Alamat</label>
                        </div>
                    </div>

                    {{-- Kolom 3 (Default Off) --}}
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="no_hp" id="no_hp" {{ $req->no_hp ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="no_hp">No HP</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="fullname_par" id="fullname_par" {{ $req->fullname_par ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="fullname_par">Nama Orang Tua</label>
                        </div>
                    </div>
                </div>

                {{-- DROPDOWN KELAS & TOMBOL --}}
                <div class="row align-items-end border-top pt-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Pilih Kelas</label>
                        <select name="fclass_list" class="form-select border-primary" onchange="this.form.submit()">
    <option value="">- Semua Siswa Aktif -</option>
    @foreach($classList as $cl)
        {{-- TAMPILAN BARU: Nama Kelas - Subjek (Tahun Ajaran) --}}
        <option value="{{ $cl->id }}" {{ $req->fclass_list == $cl->id ? 'selected' : '' }}>
            {{ $cl->title }} - {{ $cl->subject }} ({{ $cl->year_title }})
        </option>
    @endforeach
</select>
                    </div>
                    <div class="col-md-7 d-flex gap-2">
                        <button type="submit" name="filter_btn" value="1" class="btn btn-primary px-4">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>

                        @if($students->count() > 0)
                            <button type="submit" 
                                    formaction="{{ route('school.report.print') }}" 
                                    formtarget="_blank" 
                                    class="btn btn-outline-danger px-4">
                                <i class="bi bi-printer"></i> Cetak PDF
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-people-fill me-2"></i>
                @if($selectedClass)
                    Siswa Kelas: <span class="text-primary">{{ $selectedClass->title }}</span>
                @else
                    Seluruh Data Siswa
                @endif
            </span>
            <span class="badge bg-secondary">{{ $students->total() }} Data</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            
                            @if($req->nis) <th>NIS</th> @endif
                            @if($req->fullname_sis) <th>Nama Siswa</th> @endif
                            @if($req->old) <th>Tanggal Lahir</th> @endif
                            @if($req->adress) <th>Alamat</th> @endif
                            @if($req->no_hp) <th>No HP</th> @endif
                            @if($req->fullname_par) <th>Orang Tua</th> @endif
                            
                            <th class="text-center">Report Siswa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $row)
                            <tr>
                                <td class="text-center">{{ $students->firstItem() + $index }}</td>
                                
                                @if($req->nis) <td>{{ $row->nis ?? '-' }}</td> @endif
                                @if($req->fullname_sis) <td class="fw-bold">{{ $row->fullname }}</td> @endif
                                
                                @if($req->old) 
                                    <td>{{ $row->dateofbirth ? date('d-m-Y', strtotime($row->dateofbirth)) : '-' }}</td> 
                                @endif
                                
                                @if($req->adress) <td class="small">{{ \Illuminate\Support\Str::limit($row->full_address, 40) }}</td> @endif
                                @if($req->no_hp) <td>{{ $row->mobile_phone ?? '-' }}</td> @endif
                                @if($req->fullname_par) <td>{{ $row->father_name ?? '-' }}</td> @endif
                                
                                <td class="text-center">
                                    <a href="{{ route('school.report.detail', $row->user_id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat Report Siswa">
                                        <i class="bi bi-file-earmark-person"></i> Report Siswa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                    Data tidak ditemukan.
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