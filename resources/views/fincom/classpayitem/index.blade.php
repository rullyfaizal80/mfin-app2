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

    {{-- BARIS TOMBOL NAVIGASI & AKSI --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        {{-- Sisi Kiri: Tombol Kembali --}}
        <div>
            <a href="{{ route('fincom.class_list.index') }}" class="btn btn-sm btn-outline-secondary fw-bold shadow-sm px-3 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kelas
            </a>
        </div>
        
        {{-- Sisi Kanan: Kumpulan Tombol Manajerial --}}
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary fw-bold shadow-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCopy">
                <i class="bi bi-files me-1"></i> Copy Dari Kelas Lain
            </button>
            <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm px-3 rounded-pill" id="btn-trigger-sync">
                <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi ke Siswa
            </button>
            <button type="button" class="btn btn-sm btn-primary fw-bold shadow-sm px-3 rounded-pill" id="btn-trigger-add">
                <i class="bi bi-plus-circle me-1"></i> Tambah Komponen
            </button>
        </div>
    </div>

   {{-- KOTAK IDENTITAS KELAS (Terpisah Sendiri ala Userpayment Student List) --}}
    <div class="card shadow-sm border-0 mb-4 bg-light border">
        <div class="card-body py-3 px-4">
            <div class="row g-3 align-items-center text-center text-md-start">
                <div class="col-md-5 border-end-md">
                    <span class="text-muted d-block small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.5px;">Kelas</span>
                    <span class="fw-bold text-primary fs-5">
                        {{-- Output target: E.FUTSAL / NON-JURUSAN / EKSTRA --}}
                        {{ $class_info->class_title }} / {{ $class_info->subject_title ?? 'NON-JURUSAN' }} / {{ $class_info->type_title ?? 'EKSTRA' }}
                    </span>
                </div>
                <div class="col-md-4 border-end-md ps-md-4">
                    <span class="text-muted d-block small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.5px;">Sekolah</span>
                    <span class="fw-bold text-dark fs-6">{{ $class_info->school_name ?? '-' }}</span>
                </div>
                <div class="col-md-3 ps-md-4">
                    <span class="text-muted d-block small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.5px;">Tahun Ajaran</span>
                    <span class="fw-bold text-dark fs-6">{{ $class_info->year_title ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL CRD UTAMA KOMPONEN TAGIHAN --}}
    <div class="card shadow-sm border-0 bg-body">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-borderless border-top border-light mb-0" id="table-payitem" style="width: 100%">
                <thead class="table-light text-secondary text-uppercase fs-7 bg-light">
                    <tr>
                        <th width="5%" class="text-center ps-3">No</th>
                        <th width="25%">Nama Komponen / Jenis</th>
                        <th width="15%" class="text-center">Periode Tagihan</th>
                        <th width="25%">Alokasi Akun COA</th>
                        <th width="12%" class="text-center">Pengulangan</th>
                        <th width="13%" class="text-end pe-3">Nominal</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Dikosongkan karena di-load lewat AJAX --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

{{-- FORM HIDDEN UNTUK PROSES SINKRONISASI --}}
<form id="form-sync" action="{{ route('fincom.classpayitem.sync', $class_info->id) }}" method="POST" style="display:none;">@csrf</form>

{{-- TAMBAHKAN INI: Form hapus massal/global untuk menangani baris dinamis Datatables --}}
<form id="form-delete-global" action="" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- ========================================== --}}
{{-- MODAL DIALOG FORM TAMBAH / EDIT KOMPONEN   --}}
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
                            <input type="number" name="payvalue" id="payvalue" class="form-control form-control-sm" placeholder="Contoh: 250000" required>
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
                    <p class="small text-muted">Fitur ini akan menyalin seluruh jenis komponen tagihan beserta nominal & COA dari kelas lain ke kelas rumpun ini.</p>
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
    $('#table-payitem').DataTable({
    processing: true,
    serverSide: true,
    ordering: false,
    ajax: {
        url: "{{ url('fincom/classpayitem/ax_get_classpayitem/' . $class_info->id) }}",
        type: "POST",
        data: function ( d ) {
            d.iDisplayLength = d.length;
            d.iDisplayStart = d.start;
            d.sSearch = d.search.value;
            d.sEcho = d.draw;
            d._token = "{{ csrf_token() }}";
        }
    },
    // Penyelarasan class CSS per kolom agar rapi seperti view student_list
    columnDefs: [
        {
            targets: 0, // Kolom NO
            className: 'text-center ps-3 text-muted fw-bold',
            render: function (data, type, row, meta) {
                // Membuat nomor urut otomatis yang dinamis walaupun ganti halaman pagination
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { targets: 1, className: 'fw-semibold text-dark' }, // Kolom Nama Komponen
        { targets: 2, className: 'text-center text-muted fs-7' }, // Kolom Periode
        { targets: 3, className: 'text-start text-secondary fs-7' }, // Kolom COA
        { targets: 4, className: 'text-center text-capitalize' }, // Kolom Pengulangan
        { targets: 5, className: 'text-end pe-3 fw-bold text-dark' }, // Kolom Nominal
        { targets: 6, className: 'text-center' } // Kolom Aksi
    ]
});
    
    // --- PENANGKAP EVENT KLIK (ANTI PROBLEM VS CODE) ---
    
    // Klik Tambah Komponen
    $('#btn-trigger-add').on('click', function() {
        openAddModal();
    });

    // Klik Sinkronisasi ke Siswa
    $('#btn-trigger-sync').on('click', function() {
        confirmSync();
    });

    // Klik Tombol Edit di Baris Tabel
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id'); 
        openEditModal(id);
    });

    // Klik Tombol Hapus di Baris Tabel
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id'); 
        confirmDelete(id);
    });

});

// --- FUNGSI LOGIKA AKSI MODAL & AJAX ---

function openAddModal() {
    $('#modalTitle').text('Tambah Komponen Tagihan Baru');
    $('#formComponent').attr('action', "{{ route('fincom.classpayitem.store', $class_info->id) }}");
    $('#formComponent')[0].reset(); 
    $('#modalForm').modal('show');
}

function openEditModal(id) {
    $('#modalTitle').text('Ubah Komponen Tagihan');
    
    let updateUrl = "{{ route('fincom.classpayitem.update', ':id') }}";
    $('#formComponent').attr('action', updateUrl.replace(':id', id));

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

function confirmDelete(id) {
    if(confirm('Apakah Anda yakin ingin menghapus komponen tagihan ini dari kelas?')) {
        // Ambil struktur route delete bawaan Laravel
        let deleteUrl = "{{ route('fincom.classpayitem.destroy', ':id') }}";
        
        // Ganti token :id dengan id data yang sesungguhnya
        $('#form-delete-global').attr('action', deleteUrl.replace(':id', id));
        
        // Jalankan submit form
        $('#form-delete-global').submit();
    }
}

function confirmSync() {
    if(confirm('PENTING! Fitur ini akan mendistribusikan / membuat tagihan riil ke dalam akun masing-masing siswa yang aktif di kelas ini.\n\nApakah Anda yakin ingin melanjutkan sinkronisasi massal?')) {
        $('#form-sync').submit();
    }
}
</script>
@endpush