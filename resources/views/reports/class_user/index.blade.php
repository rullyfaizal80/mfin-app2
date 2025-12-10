@extends('layouts.app')

@section('title', 'Data Murid per Kelas | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Data Murid: {{ $classList->title }}</h1>
            <span class="text-muted">{{ $classList->school_name }} | {{ $classList->year_title }}</span>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('reports.class_list.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- FORM FILTER & CETAK --}}
    {{-- Kita gunakan satu form untuk menghandle Filter Layar & Cetak --}}
    <form method="GET" action="{{ route('reports.class_user.index', $class_list_id) }}">
        
        {{-- Penanda bahwa filter sedang aktif --}}
        <input type="hidden" name="filter_applied" value="1">

        <div class="row">
            {{-- PANEL KIRI: INFO --}}
            <div class="col-md-4 mb-3">
                <div class="card h-100 bg-light">
                    <div class="card-body py-2">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted" width="35%">Tingkat</td><td class="fw-bold">{{ $classList->grade_title }} {{ $classList->group_title }}</td></tr>
                            <tr><td class="text-muted">Tipe</td><td>{{ $classList->type_title }}</td></tr>
                            <tr><td class="text-muted">Wali Kelas</td><td>{{ $classList->wali1_name ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: OPSI KOLOM --}}
            <div class="col-md-8 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white py-2 fw-bold small">
                        <i class="bi bi-gear-fill"></i> Opsi Kolom & Cetak
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            {{-- Checkbox Filter --}}
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_nis" id="f_nis" {{ $req->has('f_nis') ? 'checked' : '' }}><label class="form-check-label small" for="f_nis">NIS</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_name" checked disabled><input type="hidden" name="f_name" value="on"><label class="form-check-label small">Nama (Wajib)</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_gender" id="f_gender" {{ $req->has('f_gender') ? 'checked' : '' }}><label class="form-check-label small" for="f_gender">Jenis Kelamin</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_nin" id="f_nin" {{ $req->has('f_nin') ? 'checked' : '' }}><label class="form-check-label small" for="f_nin">NISN</label></div></div>
                            
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_born" id="f_born" {{ $req->has('f_born') ? 'checked' : '' }}><label class="form-check-label small" for="f_born">Tempat Tgl Lahir</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_phone" id="f_phone" {{ $req->has('f_phone') ? 'checked' : '' }}><label class="form-check-label small" for="f_phone">No. Telepon</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_address" id="f_address" {{ $req->has('f_address') ? 'checked' : '' }}><label class="form-check-label small" for="f_address">Alamat</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_email" id="f_email" {{ $req->has('f_email') ? 'checked' : '' }}><label class="form-check-label small" for="f_email">Email</label></div></div>

                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_father" id="f_father" {{ $req->has('f_father') ? 'checked' : '' }}><label class="form-check-label small" for="f_father">Nama Ayah</label></div></div>
                            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="f_mother" id="f_mother" {{ $req->has('f_mother') ? 'checked' : '' }}><label class="form-check-label small" for="f_mother">Nama Ibu</label></div></div>
                        </div>

                        <div class="d-flex gap-2">
                            {{-- TOMBOL FILTER (Submit ke halaman ini sendiri) --}}
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-funnel"></i> Filter Tampilan
                            </button>

                            {{-- TOMBOL CETAK (Override Action ke route print, Target _blank) --}}
                            <button type="submit" 
                                    formaction="{{ route('reports.class_user.print', $class_list_id) }}" 
                                    formtarget="_blank" 
                                    class="btn btn-sm btn-primary px-4">
                                <i class="bi bi-printer"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA (Kolom Muncul/Hilang Sesuai Filter) --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            
                            @if($req->has('f_nis')) <th>NIS</th> @endif
                            @if($req->has('f_nin')) <th>NISN</th> @endif
                            
                            {{-- Nama Wajib Ada --}}
                            <th>Nama Siswa</th>
                            
                            @if($req->has('f_gender')) <th class="text-center">L/P</th> @endif
                            @if($req->has('f_born')) <th>Tempat Tgl Lahir</th> @endif
                            @if($req->has('f_phone')) <th>Kontak</th> @endif
                            @if($req->has('f_address')) <th>Alamat</th> @endif
                            @if($req->has('f_email')) <th>Email</th> @endif
                            @if($req->has('f_father')) <th>Ayah</th> @endif
                            @if($req->has('f_mother')) <th>Ibu</th> @endif

                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                
                                @if($req->has('f_nis')) <td>{{ $row->nis ?? '-' }}</td> @endif
                                @if($req->has('f_nin')) <td>{{ $row->nin ?? '-' }}</td> @endif
                                
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                
                                @if($req->has('f_gender')) 
                                    <td class="text-center">
                                        {{ ($row->gender == 'M' || $row->gender == 'L') ? 'L' : 'P' }}
                                    </td> 
                                @endif
                                @if($req->has('f_born')) 
                                    <td>{{ $row->placeofbirth }}, {{ $row->dateofbirth ? date('d-m-Y', strtotime($row->dateofbirth)) : '' }}</td> 
                                @endif
                                @if($req->has('f_phone')) <td>{{ $row->mobile_phone ?? $row->home_phone }}</td> @endif
                                @if($req->has('f_address')) <td>{{ Str::limit($row->address, 30) }}</td> @endif
                                @if($req->has('f_email')) <td>{{ $row->email }}</td> @endif
                                @if($req->has('f_father')) <td>{{ $row->father_name }}</td> @endif
                                @if($req->has('f_mother')) <td>{{ $row->mother_name }}</td> @endif

                                <td>
                                    @if($row->is_active == 'yes')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('reports.class_user.student_print', $row->user_id) }}" target="_blank" class="btn btn-xs btn-outline-info" title="Cetak Profil">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-4 text-muted">Belum ada siswa di kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection