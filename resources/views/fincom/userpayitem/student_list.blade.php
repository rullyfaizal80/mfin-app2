@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Tombol Kembali --}}
    <div class="mb-3">
        <a href="{{ route('fincom.student_list.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Siswa
        </a>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Kolom Kiri: Biodata Siswa --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Informasi Siswa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td width="30%" class="text-muted">NIS</td>
                            <td class="fw-bold">: {{ $student->nis }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama</td>
                            <td class="fw-bold">: {{ $student->fullname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kelas</td>
                            <td class="fw-bold">: {{ $student->class_name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            {{-- Form Tambah Komponen Baru --}}
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <label class="form-label fw-bold">Tambah Komponen Baru</label>
                        <div class="input-group">
                            <select name="payitem_id" class="form-select">
                                <option value="">- Pilih Komponen -</option>
                                @foreach($payitems as $pi)
                                    <option value="{{ $pi->id }}">{{ $pi->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" name="add_button" value="1" class="btn btn-success">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Rincian Tabel Komponen --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">Daftar Komponen (Tagihan) Aktif</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>JENIS</th>
                                    <th>PERIODE</th>
                                    <th>AKUN</th>
                                    <th>TYPE</th>
                                    <th class="text-end" width="150">NILAI</th>
                                    <th class="text-center" width="100">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upitems as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        
                                        {{-- Kolom 1: JENIS (Title & Code) --}}
                                        <td>
                                            <strong>{{ $item->title }}</strong><br>
                                            <span class="text-muted small">{{ $item->payitem_code }}</span>
                                        </td>
                                        
                                        {{-- Kolom 2: PERIODE (Start & End) --}}
                                        <td>
                                            {{ $item->pay_start }}<br>
                                            {{ $item->pay_end }}
                                        </td>
                                        
                                        {{-- Kolom 3: AKUN --}}
                                        <td class="small">
                                            @if($item->coa_cash && isset($coa_list[$item->coa_cash])) 
                                                {{ $coa_list[$item->coa_cash] }}<br> 
                                            @endif
                                            @if($item->coa_receivable && isset($coa_list[$item->coa_receivable])) 
                                                {{ $coa_list[$item->coa_receivable] }}<br> 
                                            @endif
                                            @if($item->coa_cost && isset($coa_list[$item->coa_cost])) 
                                                {{ $coa_list[$item->coa_cost] }}<br> 
                                            @endif
                                            @if($item->coa_payable && isset($coa_list[$item->coa_payable])) 
                                                {{ $coa_list[$item->coa_payable] }}<br> 
                                            @endif
                                            @if($item->coa_revenue && isset($coa_list[$item->coa_revenue])) 
                                                {{ $coa_list[$item->coa_revenue] }} 
                                            @endif
                                        </td>

                                        {{-- Kolom 4: TYPE --}}
                                        <td>
                                            {{ $item->payitem_type }}<br>
                                            {{ $item->pay_repeat }}
                                        </td>
                                        
                                        {{-- Kolom 5: NILAI (Diubah menjadi Teks Biasa, bukan Input Form) --}}
                                        <td class="text-end fw-bold text-primary">
                                            Rp {{ number_format($item->payvalue, 0, ',', '.') }}
                                        </td>
                                        
                                        {{-- Kolom 6: AKSI --}}
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                <a href="{{ route('fincom.userpayitem.del_item', ['student_id' => $student->user_id, 'id' => $item->upid]) }}" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Yakin ingin menghapus komponen ini?')"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Siswa ini belum memiliki komponen tagihan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection