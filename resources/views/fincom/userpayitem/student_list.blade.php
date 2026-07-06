@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Baris Navigasi Atas & Tombol Tambah --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('fincom.student_list.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Siswa
        </a>
        
        {{-- Tombol Pemicu Modal Tambah Komponen --}}
        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomponen">
            <i class="bi bi-plus-lg me-1"></i> Tambah Komponen Baru
        </button>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 1. Informasi Biodata Siswa (Full Width Compact Card) --}}
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <div class="row text-center text-md-start align-items-center">
                
                {{-- NIS --}}
                <div class="col-md-2 mb-2 mb-md-0 border-md-end">
                    <small class="text-muted d-block uppercase text-xs fw-bold">NIS</small>
                    <span class="fw-bold fs-6 text-dark">{{ $student->nis }}</span>
                </div>
                
                {{-- Nama Lengkap --}}
                <div class="col-md-3 mb-2 mb-md-0 border-md-end ps-md-3">
                    <small class="text-muted d-block uppercase text-xs fw-bold">Nama Lengkap</small>
                    <span class="fw-bold fs-6 text-dark">{{ $student->fullname }}</span>
                </div>
                
                {{-- Tempat, Tanggal Lahir (TTL) --}}
                <div class="col-md-3 mb-2 mb-md-0 border-md-end ps-md-3">
                    <small class="text-muted d-block uppercase text-xs fw-bold">TTL</small>
                    <span class="fw-bold fs-6 text-primary">{{ $student->placeofbirth }} / {{ $student->dateofbirth }}</span>
                </div>
                
                {{-- Ditanggung Oleh (Payer) --}}
                <div class="col-md-4 ps-md-3 position-relative">
                    <small class="text-muted d-block uppercase text-xs fw-bold">Ditanggung oleh</small>
                    
                    @if($student->paid_by && $student->paid_by != 0 && $student->payer_name)
                        {{-- Jika sudah ada penanggung jawab, tampilkan nama dan tombol X (Hapus) --}}
                        <div class="d-flex align-items-center mt-1">
                            <span class="fw-bold text-success me-2 fs-6"><i class="bi bi-person-check-fill me-1"></i> {{ $student->payer_name }}</span>
                            <a href="{{ route('fincom.userpayitem.del_payer', $student->user_id) }}" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Penanggung Jawab" onclick="return confirm('Hapus penanggung jawab ini?')"><i class="bi bi-x-lg"></i></a>
                        </div>
                    @else
                        {{-- Jika belum ada, tampilkan form pencarian --}}
                        <div class="input-group input-group-sm mt-1">
                            <input type="text" id="fpaid" class="form-control" placeholder="Ketik nama penanggung...">
                            <button class="btn btn-primary px-3" type="button" id="fpaid_btn">Cari</button>
                        </div>
                        
                        {{-- Kontainer Dropdown Hasil Pencarian AJAX --}}
                        <div id="paid-list" class="list-group position-absolute shadow mt-1" style="z-index: 1050; display: none; width: 90%;"></div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- 2. Daftar Tabel Komponen (Full Width 1 Kolom) --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i> Daftar Komponen (Tagihan) Aktif Siswa</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>JENIS</th>
                                    <th>PERIODE</th>
                                    <th>AKUN</th>
                                    <th>TYPE</th>
                                    <th class="text-end" width="180">NILAI</th>
                                    <th class="text-center" width="100">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upitems as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        
                                        {{-- Kolom JENIS --}}
                                        <td>
                                            <strong class="text-dark">{{ $item->title }}</strong><br>
                                            <span class="text-muted small fw-mono">{{ $item->payitem_code }}</span>
                                        </td>
                                        
                                        {{-- Kolom PERIODE --}}
                                        <td>
                                            <i class="bi bi-calendar-event text-muted me-1"></i> {{ $item->pay_start }}<br>
                                            <i class="bi bi-calendar-check text-muted me-1"></i> {{ $item->pay_end }}
                                        </td>
                                        
                                        {{-- Kolom AKUN --}}
                                        <td class="small text-secondary">
                                            @if($item->coa_cash && isset($coa_list[$item->coa_cash])) 
                                                <span class="badge bg-light text-dark border">K</span> {{ $coa_list[$item->coa_cash] }}<br> 
                                            @endif
                                            @if($item->coa_receivable && isset($coa_list[$item->coa_receivable])) 
                                                <span class="badge bg-light text-dark border">P</span> {{ $coa_list[$item->coa_receivable] }}<br> 
                                            @endif
                                            @if($item->coa_cost && isset($coa_list[$item->coa_cost])) 
                                                <span class="badge bg-light text-dark border">B</span> {{ $coa_list[$item->coa_cost] }}<br> 
                                            @endif
                                            @if($item->coa_payable && isset($coa_list[$item->coa_payable])) 
                                                <span class="badge bg-light text-dark border">U</span> {{ $coa_list[$item->coa_payable] }}<br> 
                                            @endif
                                            @if($item->coa_revenue && isset($coa_list[$item->coa_revenue])) 
                                                <span class="badge bg-light text-dark border">D</span> {{ $coa_list[$item->coa_revenue] }} 
                                            @endif
                                        </td>

                                        {{-- Kolom TYPE --}}
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">{{ $item->payitem_type }}</span><br>
                                            <small class="text-muted">{{ $item->pay_repeat }}</small>
                                        </td>
                                        
                                        {{-- Kolom NILAI --}}
                                        <td class="text-end fw-bold text-primary fs-6">
                                            Rp {{ number_format($item->payvalue, 0, ',', '.') }}
                                        </td>
                                        
                                        {{-- Kolom AKSI --}}
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm">
                                                <button type="button" 
        class="btn btn-warning btn-sm text-white btn-edit-item"
        data-id="{{ $item->upid }}"
        data-title="{{ $item->title }}"
        data-value="{{ $item->payvalue }}"
        data-cash="{{ $item->coa_cash }}"
        data-payable="{{ $item->coa_payable }}"
        data-cost="{{ $item->coa_cost }}"
        data-receivable="{{ $item->coa_receivable }}"
        data-revenue="{{ $item->coa_revenue }}"
        data-repeat="{{ $item->pay_repeat }}"
        data-start="{{ $item->pay_start }}"
        data-end="{{ $item->pay_end }}"
        data-bs-toggle="modal" 
        data-bs-target="#modalEditKomponen">
    <i class="bi bi-pencil-square"></i> Edit
</button>
                                                <a href="{{ route('fincom.userpayitem.del_item', ['student_id' => $student->user_id, 'id' => $item->upid]) }}" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Yakin ingin menghapus komponen ini?')"><i class="bi bi-trash"></i>Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x display-6 d-block mb-2"></i> Siswa ini belum memiliki komponen tagihan aktif.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Bootstrap 5 Modal (Tambah Komponen Baru dengan Item Lengkap & Auto-Fill CI2) --}}
    <div class="modal fade" id="modalTambahKomponen" tabindex="-1" aria-labelledby="modalTambahKomponenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('fincom.userpayitem.add_item', $student->user_id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalTambahKomponenLabel">
                            <i class="bi bi-plus-circle me-2"></i> Komponen Pembayaran Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 px-4">
                        <div class="row g-3">
                            
                            {{-- Item 1: Jenis Pembayaran (Dropdown Utama) --}}
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold text-dark">Jenis / Nama Komponen <span class="text-danger">*</span></label>
                                <select name="payitem_id" id="select_payitem" class="form-select form-select-lg border-success" required>
                                    <option value="">- Pilih Jenis Pembayaran -</option>
                                    @foreach($payitems as $pi)
                                        {{-- Menyimpan seluruh konfigurasi bawaan master pada attribute option --}}
                                        <option value="{{ $pi->id }}" 
                                                data-value="{{ $pi->payvalue ?? 0 }}"
                                                data-cash="{{ $pi->coa_cash ?? 0 }}"
                                                data-payable="{{ $pi->coa_payable ?? 0 }}"
                                                data-cost="{{ $pi->coa_cost ?? 0 }}"
                                                data-receivable="{{ $pi->coa_receivable ?? 0 }}"
                                                data-revenue="{{ $pi->coa_revenue ?? 0 }}"
                                                data-repeat="{{ $pi->pay_repeat ?? 'monthly' }}">
                                            {{ $pi->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="text-muted my-2">
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-journal-check me-1"></i> Konfigurasi Akun Jurnal (COA)</h6>

                            {{-- Item 2: Akun Kas --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Kas</label>
                                <select name="coa_cash" id="coa_cash" class="form-select form-select-sm">
                                    <option value="0">- AKUN KAS -</option>
                                    @foreach($coa_cash_list as $cc)
                                        <option value="{{ $cc->coa_code }}">{{ $cc->coa_code }} - {{ $cc->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 3: Akun Hutang --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Hutang</label>
                                <select name="coa_payable" id="coa_payable" class="form-select form-select-sm">
                                    <option value="0">- AKUN HUTANG -</option>
                                    @foreach($coa_payable_list as $cp)
                                        <option value="{{ $cp->coa_code }}">{{ $cp->coa_code }} - {{ $cp->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 4: Akun Biaya --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Biaya</label>
                                <select name="coa_cost" id="coa_cost" class="form-select form-select-sm">
                                    <option value="0">- AKUN BIAYA -</option>
                                    @foreach($coa_cost_list as $co)
                                        <option value="{{ $co->coa_code }}">{{ $co->coa_code }} - {{ $co->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 5: Akun Piutang (*) --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Akun Piutang <span class="text-danger">*</span></label>
                                <select name="coa_receivable" id="coa_receivable" class="form-select form-select-sm" required>
                                    <option value="0">- AKUN PIUTANG -</option>
                                    @foreach($coa_receivable_list as $cr)
                                        <option value="{{ $cr->coa_code }}">{{ $cr->coa_code }} - {{ $cr->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 6: Akun Pendapatan --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Pendapatan</label>
                                <select name="coa_revenue" id="coa_revenue" class="form-select form-select-sm">
                                    <option value="0">- AKUN PENDAPATAN -</option>
                                    @foreach($coa_revenue_list as $cn)
                                        <option value="{{ $cn->coa_code }}">{{ $cn->coa_code }} - {{ $cn->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 7: Nilai Nominal --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Nilai / Nominal (Rp)</label>
                                <input type="text" name="payvalue" id="payvalue" class="form-control form-control-sm fw-bold text-primary" placeholder="0" required>
                            </div>

                            <hr class="text-muted my-2">
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-arrow-repeat me-1"></i> Pengaturan Perulangan & Periode</h6>

                            {{-- Item 8: Jenis Perulangan --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Jenis Perulangan</label>
                                <select name="pay_repeat" id="pay_repeat" class="form-select form-select-sm">
                                    @foreach($repeat_options as $ro)
                                        <option value="{{ $ro }}">{{ $ro }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Item 9: Mulai Pembayaran (*) --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="pay_start" id="pay_start" class="form-control form-control-sm" required>
                            </div>

                            {{-- Item 10: Sampai Pembayaran (*) --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampai <span class="text-danger">*</span></label>
                                <input type="date" name="pay_end" id="pay_end" class="form-control form-control-sm" required>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success px-4">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Komponen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. Bootstrap 5 Modal Edit (Komponen Pembayaran Siswa) --}}
    <div class="modal fade" id="modalEditKomponen" tabindex="-1" aria-labelledby="modalEditKomponenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                {{-- ID Action Form akan diubah dinamis via JavaScript --}}
                <form id="form_edit_komponen" method="POST">
                    @csrf
                    @method('PUT') {{-- Directive Laravel untuk metode PUT --}}
                    
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditKomponenLabel">
                            <i class="bi bi-pencil-square me-2"></i> Edit Komponen: <span id="edit_display_title" class="fw-bold"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 px-4">
                        <div class="row g-3">
                            
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-journal-check me-1"></i> Konfigurasi Akun Jurnal (COA)</h6>

                            {{-- Akun Kas --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Kas</label>
                                <select name="coa_cash" id="edit_coa_cash" class="form-select form-select-sm">
                                    <option value="0">- AKUN KAS -</option>
                                    @foreach($coa_cash_list as $cc)
                                        <option value="{{ $cc->coa_code }}">{{ $cc->coa_code }} - {{ $cc->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Akun Hutang --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Hutang</label>
                                <select name="coa_payable" id="edit_coa_payable" class="form-select form-select-sm">
                                    <option value="0">- AKUN HUTANG -</option>
                                    @foreach($coa_payable_list as $cp)
                                        <option value="{{ $cp->coa_code }}">{{ $cp->coa_code }} - {{ $cp->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Akun Biaya --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Biaya</label>
                                <select name="coa_cost" id="edit_coa_cost" class="form-select form-select-sm">
                                    <option value="0">- AKUN BIAYA -</option>
                                    @foreach($coa_cost_list as $co)
                                        <option value="{{ $co->coa_code }}">{{ $co->coa_code }} - {{ $co->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Akun Piutang (*) --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Akun Piutang <span class="text-danger">*</span></label>
                                <select name="coa_receivable" id="edit_coa_receivable" class="form-select form-select-sm" required>
                                    <option value="0">- AKUN PIUTANG -</option>
                                    @foreach($coa_receivable_list as $cr)
                                        <option value="{{ $cr->coa_code }}">{{ $cr->coa_code }} - {{ $cr->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Akun Pendapatan --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Akun Pendapatan</label>
                                <select name="coa_revenue" id="edit_coa_revenue" class="form-select form-select-sm">
                                    <option value="0">- AKUN PENDAPATAN -</option>
                                    @foreach($coa_revenue_list as $cn)
                                        <option value="{{ $cn->coa_code }}">{{ $cn->coa_code }} - {{ $cn->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nilai Nominal --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Nilai / Nominal (Rp)</label>
                                <input type="text" name="payvalue" id="edit_payvalue" class="form-control form-control-sm fw-bold text-primary" placeholder="0" required>
                            </div>

                            <hr class="text-muted my-2">
                            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-arrow-repeat me-1"></i> Pengaturan Perulangan & Periode</h6>

                            {{-- Jenis Perulangan --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Jenis Perulangan</label>
                                <select name="pay_repeat" id="edit_pay_repeat" class="form-select form-select-sm">
                                    @foreach($repeat_options as $ro)
                                        <option value="{{ $ro }}">{{ $ro }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Mulai Pembayaran (*) --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="pay_start" id="edit_pay_start" class="form-control form-control-sm" required>
                            </div>

                            {{-- Sampai Pembayaran (*) --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Sampai <span class="text-danger">*</span></label>
                                <input type="date" name="pay_end" id="edit_pay_end" class="form-control form-control-sm" required>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning text-white px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>



{{-- JavaScript Engine Form Dinamis (Auto-Fill & Auto-Select Semisal AJAX di CI2) --}}
<script>
// --- SEKTOR SCRIPT UNTUK PENCARIAN PENANGGUNG JAWAB (DITANGGUNG OLEH) ---
    const fpaidBtn = document.getElementById('fpaid_btn');
    if (fpaidBtn) {
        fpaidBtn.addEventListener('click', function() {
            let keyword = document.getElementById('fpaid').value;
            let studentId = "{{ $student->user_id }}";
            let listContainer = document.getElementById('paid-list');
            
            if (keyword.length < 2) {
                alert('Silakan ketik minimal 2 huruf untuk mencari.');
                return;
            }

            // Memanggil AJAX via Fetch API
            fetch(`{{ route('fincom.userpayitem.search_payer') }}?keyword=${keyword}`)
                .then(response => response.json())
                .then(data => {
                    listContainer.innerHTML = '';
                    
                    if(data.length > 0) {
                        data.forEach(user => {
                            let a = document.createElement('a');
                            // URL aksi yang otomatis menyimpan (menimpa) data ketika diklik
                            a.href = `{{ url('fincom/userpayitem/set_payer') }}/${studentId}/${user.id}`;
                            a.className = 'list-group-item list-group-item-action py-2 text-primary fw-bold';
                            a.innerHTML = `<i class="bi bi-person-plus-fill me-2"></i> ${user.fullname} <small class="text-muted">(${user.nickname})</small>`;
                            listContainer.appendChild(a);
                        });
                        listContainer.style.display = 'block';
                    } else {
                        listContainer.innerHTML = '<div class="list-group-item text-danger small"><i class="bi bi-x-circle me-1"></i> Data tidak ditemukan</div>';
                        listContainer.style.display = 'block';
                    }
                });
        });

        // Menyembunyikan dropdown hasil pencarian jika user mengklik di luar area
        document.addEventListener('click', function(e) {
            if (!document.getElementById('fpaid').contains(e.target) && !fpaidBtn.contains(e.target)) {
                document.getElementById('paid-list').style.display = 'none';
            }
        });
    }

document.addEventListener('DOMContentLoaded', function () {
    const selectPayitem = document.getElementById('select_payitem');
    const inputPayvalue = document.getElementById('payvalue');
    const inputPayStart = document.getElementById('pay_start');
    const inputPayEnd   = document.getElementById('pay_end');
    
    // Elements Dropdown COA & Perulangan
    const coaCash       = document.getElementById('coa_cash');
    const coaPayable    = document.getElementById('coa_payable');
    const coaCost       = document.getElementById('coa_cost');
    const coaReceivable = document.getElementById('coa_receivable');
    const coaRevenue    = document.getElementById('coa_revenue');
    const payRepeat     = document.getElementById('pay_repeat');

    selectPayitem.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        
        if (this.value !== "") {
            // 1. Ambil seluruh data-attributes dari opsi komponen master yang dipilih
            let val      = opt.getAttribute('data-value') || 0;
            let cash     = opt.getAttribute('data-cash') || 0;
            let payable  = opt.getAttribute('data-payable') || 0;
            let cost     = opt.getAttribute('data-cost') || 0;
            let receiv   = opt.getAttribute('data-receivable') || 0;
            let revenue  = opt.getAttribute('data-revenue') || 0;
            let repeat   = opt.getAttribute('data-repeat') || 'monthly';

            // 2. Terapkan pemetaan Auto-Select pada seluruh item Akun COA & Repeat
            inputPayvalue.value = new Intl.NumberFormat('id-ID').format(val);
            coaCash.value       = cash;
            coaPayable.value    = payable;
            coaCost.value       = cost;
            coaReceivable.value = receiv;
            coaRevenue.value    = revenue;
            payRepeat.value     = repeat;

            // 3. Set Otomatis Periode Tanggal Mulai (Hari Ini) & Selesai (+1 Tahun)
            let today = new Date();
            let yyyy  = today.getFullYear();
            let mm    = String(today.getMonth() + 1).padStart(2, '0');
            let dd    = String(today.getDate()).padStart(2, '0');
            inputPayStart.value = `${yyyy}-${mm}-${dd}`;

            let nextYear = yyyy + 1;
            inputPayEnd.value = `${nextYear}-${mm}-${dd}`;
        } else {
            // Kosongkan ulang seluruh item jika dropdown di-reset
            inputPayvalue.value = "";
            coaCash.value       = "0";
            coaPayable.value    = "0";
            coaCost.value       = "0";
            coaReceivable.value = "0";
            coaRevenue.value    = "0";
            payRepeat.value     = "monthly";
            inputPayStart.value = "";
            inputPayEnd.value   = "";
        }
    });

    // Format Masking Titik Rupiah Real-time saat nominal diketik ulang secara manual
    inputPayvalue.addEventListener('input', function (e) {
        let clean = this.value.replace(/\D/g, "");
        this.value = clean ? new Intl.NumberFormat('id-ID').format(clean) : "";
    });

    // --- SEKTOR SCRIPT UNTUK MODAL EDIT ---
    const btnEdits = document.querySelectorAll('.btn-edit-item');
    const formEdit = document.getElementById('form_edit_komponen');
    const editDisplayTitle = document.getElementById('edit_display_title');
    
    // Elements input Modal Edit
    const editPayvalue   = document.getElementById('edit_payvalue');
    const editCoaCash    = document.getElementById('edit_coa_cash');
    const editCoaPayable = document.getElementById('edit_coa_payable');
    const editCoaCost    = document.getElementById('edit_coa_cost');
    const editCoaReceiv  = document.getElementById('edit_coa_receivable');
    const editCoaRevenue = document.getElementById('edit_coa_revenue');
    const editPayRepeat  = document.getElementById('edit_pay_repeat');
    const editPayStart   = document.getElementById('edit_pay_start');
    const editPayEnd     = document.getElementById('edit_pay_end');

    btnEdits.forEach(btn => {
        btn.addEventListener('click', function () {
            // 1. Ambil data baris tabel dari attribute tombol yang diklik
            const id        = this.getAttribute('data-id');
            const title     = this.getAttribute('data-title');
            const val       = this.getAttribute('data-value') || 0;
            const cash      = this.getAttribute('data-cash') || 0;
            const payable   = this.getAttribute('data-payable') || 0;
            const cost      = this.getAttribute('data-cost') || 0;
            const receiv    = this.getAttribute('data-receivable') || 0;
            const revenue   = this.getAttribute('data-revenue') || 0;
            const repeat    = this.getAttribute('data-repeat') || 'monthly';
            const start     = this.getAttribute('data-start');
            const end       = this.getAttribute('data-end');

            // 2. Set action URL Form secara dinamis mengarah ke route update_item Laravel
            let studentId = "{{ $student->user_id }}";
            let updateUrl = "{{ route('fincom.userpayitem.update_item', ['student_id' => ':student', 'id' => ':id']) }}";
            updateUrl = updateUrl.replace(':student', studentId).replace(':id', id);
            formEdit.setAttribute('action', updateUrl);

            // 3. Suntikkan nilai penampung ke dalam field Modal Edit
            editDisplayTitle.innerText = title;
            editPayvalue.value   = new Intl.NumberFormat('id-ID').format(val);
            editCoaCash.value    = cash;
            editCoaPayable.value = payable;
            editCoaCost.value    = cost;
            editCoaReceiv.value  = receiv;
            editCoaRevenue.value = revenue;
            editPayRepeat.value  = repeat;
            editPayStart.value   = start;
            editPayEnd.value     = end;
        });
    });

    // Format Masking Titik Rupiah Real-time saat nominal diedit manual
    editPayvalue.addEventListener('input', function (e) {
        let clean = this.value.replace(/\D/g, "");
        this.value = clean ? new Intl.NumberFormat('id-ID').format(clean) : "";
    });
});


</script>
@endsection