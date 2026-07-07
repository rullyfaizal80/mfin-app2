@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Notifikasi Sukses/Gagal --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Info Header Kelas --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded border d-flex justify-content-between align-items-center py-3">
            <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('fincom.class_list.index') }}">Daftar Kelas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Komponen Tagihan</li>
                  </ol>
                </nav>
                <h4 class="mb-0 fw-bold text-dark">
                    Kelas: <span class="text-primary">{{ $class_info->class_title }}</span> 
                    <span class="badge bg-secondary fs-6 ms-2">{{ $class_info->school_name }} ({{ $class_info->year_title }})</span>
                </h4>
            </div>
            
            {{-- Tombol-Tombol Aksi Utama --}}
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCopy">
                    <i class="bi bi-files me-1"></i> Copy Dari Kelas Lain
                </button>
                <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm" onclick="confirmSync()">
                    <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi ke Siswa
                </button>
                <button type="button" class="btn btn-sm btn-primary fw-bold shadow-sm" onclick="openAddModal()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Komponen
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel Utama Data Komponen Kelas --}}
    <div class="card shadow-sm border-0 bg-body">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle border" id="table-payitem" style="width: 100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Nama Komponen</th>
                            <th width="15%" class="text-end">Nominal</th>
                            <th width="15%" class="text-center">Periode Mulai</th>
                            <th width="15%" class="text-center">Periode Akhir</th>
                            <th width="15%" class="text-center">Status Akun COA</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payitems as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><strong class="text-primary">{{ $item->item_title }}</strong></td>
                                <td class="text-end fw-bold text-success">Rp {{ number_format($item->payvalue, 0, ',', '.') }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ date('d M Y', strtotime($item->pay_start)) }}</span></td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ date('d M Y', strtotime($item->pay_end)) }}</span></td>
                                <td class="text-center">
                                    <small class="text-muted block">Kas: {{ $item->coa_cash ? '✅' : '❌' }} | Piutang: {{ $item->coa_receivable ? '✅' : '❌' }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                       <div class="btn-group">
    <button class="btn btn-sm btn-warning py-1 px-2 btn-edit" data-id="{{ $item->id }}" title="Edit">
        <i class="bi bi-pencil-square"></i>
    </button>
    
    <button class="btn btn-sm btn-danger py-1 px-2 btn-delete" data-id="{{ $item->id }}" title="Hapus">
        <i class="bi bi-trash"></i>
    </button>
</div>
                                    </div>
                                    <form id="form-delete-{{ $item->id }}" action="{{ route('fincom.classpayitem.destroy', $item->id) }}" method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada komponen pembayaran yang diset untuk kelas ini. Klik "Tambah Komponen" untuk memulai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- FORM HIDDEN UNTUK PROSES SINKRONISASI --}}
<form id="form-sync" action="{{ route('fincom.classpayitem.sync', $class_info->id) }}" method="POST" style="display:none;">@csrf</form>


{{-- ========================================== --}}
{{-- MODAL DIALOG FORM TAMBAH / EDIT KOMPONEN --}}
{{-- ========================================== --}}
<div class="modal fade" id="modalForm" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Tambah Komponen Tagihan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formComponent" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Komponen <span class="text-danger">*</span></label>
                            <select name="payitem_id" id="payitem_id" class="form-select form-select-sm" required>
                                <option value="">- Pilih Master Komponen -</option>
                                @foreach($master_payitems as $mpi)
                                    <option value="{{ $mpi->id }}">{{ $mpi->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nominal Tagihan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="payvalue" id="payvalue" class="form-select form-select-sm form-control" placeholder="Contoh: 250000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Periode Berlaku Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="pay_start" id="pay_start" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sampai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="pay_end" id="pay_end" class="form-control form-control-sm" required>
                        </div>

                        {{-- AKUN COA (Buku Besar Akuntansi) --}}
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 fw-bold text-secondary"><i class="bi bi-journal-bookmark-fill me-1"></i> Pemetaan Akun COA Akuntansi</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Akun Kas / Bank (Debit)</label>
                            <select name="coa_cash" id="coa_cash" class="form-select form-select-sm">
                                <option value="0">- AKUN KAS -</option>
                                @foreach($coa_cash as $c) <option value="{{ $c->coa_code }}">{{ $c->coa_code }} - {{ $c->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Akun Pendapatan (Kredit)</label>
                            <select name="coa_revenue" id="coa_revenue" class="form-select form-select-sm">
                                <option value="0">- AKUN PENDAPATAN -</option>
                                @foreach($coa_revenue as $c) <option value="{{ $c->coa_code }}">{{ $c->coa_code }} - {{ $c->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Akun Piutang</label>
                            <select name="coa_receivable" id="coa_receivable" class="form-select form-select-sm">
                                <option value="0">- AKUN PIUTANG -</option>
                                @foreach($coa_receivable as $c) <option value="{{ $c->coa_code }}">{{ $c->coa_code }} - {{ $c->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Akun Utang</label>
                            <select name="coa_payable" id="coa_payable" class="form-select form-select-sm">
                                <option value="0">- AKUN UTANG -</option>
                                @foreach($coa_payable as $c) <option value="{{ $c->coa_code }}">{{ $c->coa_code }} - {{ $c->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Akun Biaya</label>
                            <select name="coa_cost" id="coa_cost" class="form-select form-select-sm">
                                <option value="0">- AKUN BIAYA -</option>
                                @foreach($coa_cost as $c) <option value="{{ $c->coa_code }}">{{ $c->coa_code }} - {{ $c->title }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ========================================== --}}
{{-- MODAL COPY DATA DARI KELAS LAIN            --}}
{{-- ========================================== --}}
<div class="modal fade" id="modalCopy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-files me-1"></i> Salin Komponen Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('fincom.classpayitem.copy', $class_info->id) }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="small text-muted">Fitur ini akan menyalin seluruh jenis komponen tagihan beserta nominal & COA dari kelas lain ke kelas <strong>{{ $class_info->class_title }}</strong> saat ini.</p>
                    <label class="form-label small fw-bold">Pilih Kelas Sumber Asal:</label>
                    <select name="from_class_list_id" class="form-select form-select-sm" required>
                        <option value="-">- Pilih Kelas Asal -</option>
                        @foreach($all_classes as $cls)
                            @if($cls->id != $class_info->id)
                                <option value="{{ $cls->id }}">{{ $cls->title }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">Eksekusi Salin Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
$(document).ready(function() {
    
    // 1. Kumpulan Event Listener Klik (Menangkap data-id dari HTML)
    
    // Menangkap klik pada tombol edit (class: .btn-edit)
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id'); 
        openEditModal(id);
    });

    // Menangkap klik pada tombol hapus (class: .btn-delete)
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id'); 
        confirmDelete(id);
    });

});

// 2. Kumpulan Fungsi Pendukung Logika Utama

// Trigger Modal Tambah Baru
function openAddModal() {
    $('#modalTitle').text('Tambah Komponen Tagihan Baru');
    $('#formComponent').attr('action', "{{ route('fincom.classpayitem.store', $class_info->id) }}");
    $('#formComponent')[0].reset(); // Reset isi form
    $('#modalForm').modal('show');
}

// Trigger Modal Edit via AJAX (Menarik data satu baris komponen)
function openEditModal(id) {
    $('#modalTitle').text('Ubah Komponen Tagihan');
    
    let updateUrl = "{{ route('fincom.classpayitem.update', ':id') }}";
    $('#formComponent').attr('action', updateUrl.replace(':id', id));

    // Tarik data komponen dari server via Route Edit JSON
    let editUrl = "{{ route('fincom.classpayitem.edit', ':id') }}";
    $.get(editUrl.replace(':id', id), function(data) {
        $('#payitem_id').val(data.payitem_id);
        $('#payvalue').val(data.payvalue);
        $('#pay_start').val(data.pay_start);
        $('#pay_end').val(data.pay_end);
        $('#coa_cash').val(data.coa_cash);
        $('#coa_revenue').val(data.coa_revenue);
        $('#coa_receivable').val(data.coa_receivable);
        $('#coa_payable').val(data.coa_payable);
        $('#coa_cost').val(data.coa_cost);

        $('#modalForm').modal('show');
    }).fail(function() {
        alert('Gagal mengambil data komponen dari database.');
    });
}

// Konfirmasi Hapus Data
function confirmDelete(id) {
    if(confirm('Apakah Anda yakin ingin menghapus komponen tagihan ini dari kelas?')) {
        $('#form-delete-' + id).submit();
    }
}

// Konfirmasi Sinkronisasi Massal ke Dompet Siswa
function confirmSync() {
    if(confirm('PENTING! Fitur ini akan mendistribusikan / membuat tagihan riil ke dalam akun masing-masing siswa yang aktif di kelas ini.\n\nApakah Anda yakin ingin melanjutkan sinkronisasi massal?')) {
        $('#form-sync').submit();
    }
}
</script>
@endpush