@extends('layouts.app')

@section('title', 'Laporan Data Kelas | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Laporan Data Kelas Siswa</h1>
    </div>

    {{-- ========================================================== --}}
    {{-- FORM FILTER (UKURAN KECIL / COMPACT)                       --}}
    {{-- ========================================================== --}}
    <form action="{{ route('school.report.index') }}" method="GET">
        <div class="card mb-4 border-start border-4 border-primary">
            <div class="card-body p-3"> 
                <h6 class="card-title fw-bold text-primary mb-3">Filter Tampilan & Cetak</h6>
                
                {{-- AREA CHECKBOX (Font Kecil) --}}
                <div class="row g-2 mb-3 small"> 
                    {{-- Kolom 1 --}}
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nis" id="nis" {{ $req->nis ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="nis">NIS</label>
                        </div>
                        <div class="form-check mt-1">
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
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="adress" id="adress" {{ $req->adress ? 'checked' : '' }}>
                            <label class="form-check-label" for="adress">Alamat</label>
                        </div>
                    </div>

                    {{-- Kolom 3 --}}
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="no_hp" id="no_hp" {{ $req->no_hp ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="no_hp">No HP</label>
                        </div>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="fullname_par" id="fullname_par" {{ $req->fullname_par ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="fullname_par">Nama Orang Tua</label>
                        </div>
                    </div>
                </div>

                {{-- DROPDOWN KELAS & TOMBOL (Ukuran Small) --}}
                <div class="row align-items-end border-top pt-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small mb-1">Pilih Kelas</label>
                        <select name="fclass_list" class="form-select form-select-sm border-primary" onchange="this.form.submit()">
                            <option value="">- Semua Siswa Aktif -</option>
                            @foreach($classList as $cl)
                                <option value="{{ $cl->id }}" {{ $req->fclass_list == $cl->id ? 'selected' : '' }}>
                                    {{ $cl->title }} - {{ $cl->subject }} ({{ $cl->year_title }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 d-flex gap-2">
                        <button type="submit" name="filter_btn" value="1" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>

                        @if($students->count() > 0)
                            <button type="submit" 
                                    formaction="{{ route('school.report.print') }}" 
                                    formtarget="_blank" 
                                    class="btn btn-outline-danger btn-sm px-3">
                                <i class="bi bi-printer"></i> Cetak PDF
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>


    {{-- ========================================================== --}}
    {{-- TABEL DATA (UKURAN NORMAL)                                 --}}
    {{-- ========================================================== --}}
    <div class="card shadow-sm">
        
        {{-- TOOLBAR PENCARIAN --}}
        <div class="card-body py-3 border-bottom bg-light">
            <div class="row justify-content-center"> 
                <div class="col-md-10">
                    <form action="{{ url()->current() }}" method="GET" class="d-flex justify-content-between align-items-center w-100">
                        
                        {{-- Hidden Inputs (Menjaga Filter) --}}
                        @if(request('fclass_list'))
                            <input type="hidden" name="fclass_list" value="{{ request('fclass_list') }}">
                        @endif
                        @foreach(['nis', 'fullname_sis', 'old', 'adress', 'no_hp', 'fullname_par'] as $chk)
                            @if(request($chk) == 'on') <input type="hidden" name="{{ $chk }}" value="on"> @endif
                        @endforeach          

                        {{-- KIRI: Search Bar (Ukuran Normal) --}}
                        <div class="input-group me-3" style="max-width: 60%;"> 
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama Siswa atau NIS..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Cari</button>
                            @if(request('search'))
                                <a href="{{ url()->current() }}?fclass_list={{ request('fclass_list') }}&per_page={{ request('per_page', 10) }}&nis=on&fullname_sis=on&old=on&adress=on" class="btn btn-danger"><i class="fa fa-times"></i></a>
                            @endif
                        </div>

                        {{-- KANAN: Pagination Dropdown (Ukuran Normal) --}}
                        <div class="d-flex align-items-center">
                            <span class="me-2 text-nowrap text-muted">Tampilkan:</span>
                            <select name="per_page" class="form-select" style="width: 80px;" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
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
                {{-- TABEL NORMAL (Tanpa font size inline, tanpa table-sm) --}}
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
                            <th class="text-center" width="10%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $row)
                            <tr>
                                <td class="text-center">{{ $students->firstItem() + $index }}</td>
                                @if($req->nis) <td>{{ $row->nis ?? '-' }}</td> @endif
                                @if($req->fullname_sis) <td class="fw-bold">{{ $row->fullname }}</td> @endif
                                @if($req->old) <td>{{ $row->dateofbirth ? date('d-m-Y', strtotime($row->dateofbirth)) : '-' }}</td> @endif
                                @if($req->adress) <td>{{ \Illuminate\Support\Str::limit($row->full_address, 40) }}</td> @endif
                                @if($req->no_hp) <td>{{ $row->mobile_phone ?? '-' }}</td> @endif
                                @if($req->fullname_par) <td>{{ $row->father_name ?? '-' }}</td> @endif
                                <td class="text-center">
                                    <a href="{{ route('school.report.detail', $row->user_id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat Report Siswa">
                                        <i class="bi bi-file-earmark-person"></i> Report
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION NORMAL (Tanpa CSS Custom) --}}
            <div class="p-3 d-flex justify-content-end">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection