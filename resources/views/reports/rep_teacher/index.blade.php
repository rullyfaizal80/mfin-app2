@extends('layouts.app')

@section('title', 'Pencarian Data Guru | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Pencarian Data Guru</h1>
    </div>

    {{-- FORM FILTER --}}
    <form action="{{ route('reports.rep_teacher.index') }}" method="GET">
        <div class="card mb-4 border-start border-4 border-primary">
            <div class="card-body p-3">
                <h6 class="card-title fw-bold text-primary mb-3">Filter Pencarian</h6>
                
                <div class="row g-2 mb-3 small">
                    {{-- NIP --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">NIP / NIK</label>
                        <input type="text" name="snik" class="form-control form-control-sm" value="{{ $req->snik }}" placeholder="Cari NIP...">
                    </div>
                    
                    {{-- Nama --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Nama Guru</label>
                        <input type="text" name="snama" class="form-control form-control-sm" value="{{ $req->snama }}" placeholder="Nama Lengkap...">
                    </div>
                    
                    {{-- Masa Kerja --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1">Masa Kerja (Tahun)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="smasa" class="form-control form-control-sm" value="{{ $req->smasa }}" placeholder="0">
                            <span class="input-group-text">Tahun</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end border-top pt-3">
                    @if($req->anyFilled(['snik', 'snama', 'smasa']))
                        <a href="{{ route('reports.rep_teacher.index') }}" class="btn btn-secondary btn-sm px-3">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-search"></i> Cari Data
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
            <span><i class="bi bi-person-badge-fill me-2"></i> Hasil Pencarian</span>
            <span class="badge bg-secondary">{{ $teachers->total() }} Data Ditemukan</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>NIP / NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Tempat, Tanggal Lahir</th>
                            <th>Kontak (HP/Email)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="10%">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $index => $row)
                            <tr>
                                <td class="text-center">{{ $teachers->firstItem() + $index }}</td>
                                <td>{{ $row->nik ?? '-' }}</td>
                                <td class="fw-bold">{{ $row->fullname }}</td>
                                <td>
                                    {{-- TEMPAT LAHIR --}}
                                    {{ $row->placeofbirth ?? '' }},
                                    <br>
                                    
                                    {{-- TANGGAL LAHIR (Logic Fix) --}}
                                    <small class="text-muted">
                                        @php
                                            // Cek jika tanggal valid (bukan 0000-00-00 dan bukan tahun minus)
                                            $isValidDate = $row->dateofbirth && $row->dateofbirth != '0000-00-00' && strtotime($row->dateofbirth) > 0;
                                        @endphp

                                        @if($isValidDate)
                                            {{ date('d M Y', strtotime($row->dateofbirth)) }}
                                        @else
                                            01 01 1970
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        <i class="bi bi-phone"></i> {{ $row->mobile_phone ?? '-' }}<br>
                                        <i class="bi bi-envelope"></i> {{ $row->email ?? '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($row->is_active == 'yes')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('reports.rep_teacher.detail', $row->id) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                        <i class="bi bi-file-earmark-person"></i> Report
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-2 d-block mb-2"></i>
                                    Tidak ada data guru yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-end">
                {{ $teachers->links() }}
            </div>
        </div>
    </div>
@endsection