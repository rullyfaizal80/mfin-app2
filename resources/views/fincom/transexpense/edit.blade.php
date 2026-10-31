@extends('layouts.app')

@section('title', 'Edit Pengeluaran | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Transaksi Pengeluaran</h1>
    <a href="{{ route('fincom.transexpense.cancel', $header->id) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi Kesalahan!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('fincom.transexpense.update', $header->id) }}" method="POST" id="form-edit-expense">
    @csrf
    @method('PUT')

    {{-- BAGIAN HEADER TRANSAKSI --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-warning text-dark">Data Utama Transaksi</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Petugas / Kasir</label>
                    <input type="text" class="form-control" value="{{ $petugasName }}" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="tdate" class="form-label">Tanggal Transaksi</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="tdate" id="tdate" value="{{ \Carbon\Carbon::parse($header->tdate)->format('Y-m-d') }}" readonly>
                        <button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalUnlockDate" id="btn-unlock-date" title="Buka Kunci Tanggal">
                            <i class="fas fa-lock"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">No Referensi</label>
                    <input type="text" name="ref_no" class="form-control" value="{{ $header->ref_no }}" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3 position-relative">
                    <label for="payto" class="form-label">Dibayarkan Kepada (Pay To)</label>
                    {{-- Input Hidden untuk menyimpan ID Penerima --}}
                    <input type="hidden" name="user_id" id="user_id" value="{{ $header->user_id }}">
                    <input type="text" class="form-control" name="payto" id="payto" value="{{ $header->payto }}" autocomplete="off" placeholder="Ketik nama...">
                    
                    <ul class="list-group position-absolute w-100 shadow-sm" id="payto-suggestions" style="display: none; z-index: 1050; max-height: 250px; overflow-y: auto; top: 100%;"></ul>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Keterangan Umum (Note)</label>
                    <textarea name="header_note" class="form-control" rows="2" placeholder="Catatan transaksi...">{{ $header->note }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN DETAIL ITEM --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">Rincian Pengeluaran & Alokasi Beban</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 small" id="detailTable">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="20%">Nama Komponen</th>
                            <th width="20%">Keterangan Item</th>
                            <th width="15%">Nominal (Rp)</th>
                            <th width="20%">Alokasi Kelas & Level</th>
                            <th width="15%">Unit</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $index => $item)
                        <tr class="item-row">
                            <td>
                                <select name="items[{{ $index }}][payitem_id]" class="form-select form-select-sm payitem-select" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach($payitem as $pi)
                                        <option value="{{ $pi->id }}" {{ $item->payitem_id == $pi->id ? 'selected' : '' }}>
                                            {{ $pi->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][note]" class="form-control form-control-sm item-note" value="{{ $item->note }}">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][amount]" class="form-control form-control-sm amount-input text-end" value="{{ number_format($item->debit, 0, ',', '.') }}" required onkeyup="formatRupiah(this); calculateTotal()">
                            </td>
                            <td>
                                <div class="mb-1">
    <select name="items[{{ $index }}][school_id]" class="form-select form-select-sm">
        <option value="0">--Semua Sekolah--</option>
        @foreach($schools as $sch)
            <option value="{{ $sch->id }}">{{ $sch->name }}</option>
        @endforeach
    </select>
</div>
                                <div class="mb-1">
                                    <select name="items[{{ $index }}][level_id]" class="form-select form-select-sm">
                                        <option value="0">--Semua Level--</option>
                                        @foreach($grades as $gd)
                                            <option value="{{ $gd->id }}" {{ $item->level_id == $gd->id ? 'selected' : '' }}>{{ $gd->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <select name="items[{{ $index }}][class_id]" class="form-select form-select-sm">
                                        <option value="0">--Semua Kelas--</option>
                                        @foreach($classes as $cs)
                                            <option value="{{ $cs->id }}" {{ $item->class_id == $cs->id ? 'selected' : '' }}>{{ $cs->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <select name="items[{{ $index }}][sunit]" class="form-select form-select-sm">
    @foreach($sunits as $su)
        <option value="{{ $su }}" {{ $item->sunit == $su ? 'selected' : '' }}>{{ $su }}</option>
    @endforeach
</select>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light fw-bold">
                            <td colspan="2" class="text-end align-middle">TOTAL PENGELUARAN</td>
                            <td>
                                <input type="text" id="totalAmount" class="form-control form-control-sm text-end fw-bold text-danger" value="0" readonly>
                            </td>
                            <td colspan="3" class="text-end">
                                <button type="button" id="addRow" class="btn btn-sm btn-success"><i class="bi bi-plus-circle"></i> Tambah Baris</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- TOMBOL AKSI BAWAH --}}
    <div class="d-flex justify-content-between mb-5">
        <div>
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('fincom.transexpense.cancel', $header->id) }}" class="btn btn-secondary">Batal</a>
        </div>

        <button type="button" class="btn btn-outline-danger fw-bold" onclick="deleteTransaction()">
            <i class="fas fa-trash"></i> Hapus Transaksi Ini
        </button>
    </div>
</form>

<form id="form-delete" action="{{ route('fincom.transexpense.destroy', $header->id) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- Modal Otorisasi Admin Buka Kunci Tanggal --}}
<div class="modal fade" id="modalUnlockDate" tabindex="-1" aria-labelledby="modalUnlockDateLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6" id="modalUnlockDateLabel">
                    <i class="fas fa-shield-alt text-primary"></i> Otorisasi Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Masukkan kredensial untuk mengubah tanggal transaksi.</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" id="admin_username" class="form-control form-control-sm" autocomplete="off" placeholder="Username Admin">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" id="admin_password" class="form-control form-control-sm" placeholder="••••••••">
                </div>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmUnlock">Verifikasi</button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    let rowIdx = parseInt("{{ count($details) }}", 10) || 0;

    // Tambah Baris Baru
    document.getElementById('addRow').addEventListener('click', function () {
        let tbody = document.querySelector('#detailTable tbody');
        let tr = document.createElement('tr');
        tr.className = 'item-row';
        
        tr.innerHTML = `
            <td>
                <select name="items[${rowIdx}][payitem_id]" class="form-select form-select-sm payitem-select" required onchange="fetchExpenseData(this)">
                    <option value="">-- Pilih --</option>
                    @foreach($payitem as $pi)
                        <option value="{{ $pi->id }}">{{ $pi->title }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="items[${rowIdx}][note]" class="form-control form-control-sm item-note"></td>
            <td><input type="text" name="items[${rowIdx}][amount]" class="form-control form-control-sm amount-input text-end" placeholder="0" required onkeyup="formatRupiah(this); calculateTotal()"></td>
            <td>
                <div class="mb-1">
                    <select name="items[${rowIdx}][school_id]" class="form-select form-select-sm">
                        <option value="0">--Semua Sekolah--</option>
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1">
                    <select name="items[${rowIdx}][level_id]" class="form-select form-select-sm">
                        <option value="0">--Semua Level--</option>
                        @foreach($grades as $gd)
                            <option value="{{ $gd->id }}">{{ $gd->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="items[${rowIdx}][class_id]" class="form-select form-select-sm">
                        <option value="0">--Semua Kelas--</option>
                        @foreach($classes as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->title }}</option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <select name="items[${rowIdx}][sunit]" class="form-select form-select-sm">
                    @foreach($sunits as $su)
                        <option value="{{ $su }}">{{ $su }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIdx++;
    });

    // Menarik Nilai Default dari Komponen via AJAX (Identik CI2)
    function fetchExpenseData(selectElem) {
        let payitem_id = selectElem.value;
        if(payitem_id > 0) {
            let row = selectElem.closest('.item-row');
            let amountInput = row.querySelector('.amount-input');
            
            fetch(`{{ url('fincom/transexpense/ax_getexpensedata') }}/${payitem_id}`)
                .then(res => res.json())
                .then(data => {
                    amountInput.value = data.payvalue; 
                    calculateTotal();
                })
                .catch(err => console.log('Error fetching default value:', err));
        }
    }

   function removeRow(btn) {
        let rowCount = document.querySelectorAll('#detailTable tbody .item-row').length;
        if (rowCount > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        } else {
            alert('Transaksi tidak boleh kosong! Minimal harus ada 1 baris rincian pengeluaran.');
        }
    }

    function formatRupiah(input) {
        let value = input.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = rupiah;
    }

    function calculateTotal() {
        let inputs = document.querySelectorAll('.amount-input');
        let total = 0;
        inputs.forEach(function(input) {
            let val = input.value.replace(/\./g, ''); 
            if (val) total += parseFloat(val);
        });
        document.getElementById('totalAmount').value = new Intl.NumberFormat('id-ID').format(total);
    } 

    function deleteTransaction() {
        if (confirm('PERINGATAN: Transaksi akan dihapus permanen. Yakin?')) {
            document.getElementById('form-delete').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        calculateTotal();

        let currentRows = document.querySelectorAll('#detailTable tbody .item-row').length;
        if (currentRows === 0) {
            document.getElementById('addRow').click();
        }

        // --- 1. SCRIPT OTORISASI ADMIN BUKA KUNCI TANGGAL ---
        const btnConfirm = document.getElementById('btnConfirmUnlock');
        const inputDate = document.getElementById('tdate');
        const btnUnlockTrigger = document.getElementById('btn-unlock-date');
        
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function() {
                let user = document.getElementById('admin_username').value;
                let pass = document.getElementById('admin_password').value;

                if (user === "" || pass === "") {
                    alert("Username dan Password tidak boleh kosong!");
                    return;
                }

                let originalText = btnConfirm.innerHTML;
                btnConfirm.innerHTML = "Memverifikasi...";
                btnConfirm.disabled = true;

                fetch("{{ route('fincom.transexpense.verifyAdmin') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ username: user, password: pass })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || "Terjadi kesalahan server");
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        // Buka gembok input tanggal
                        inputDate.removeAttribute('readonly');
                        
                        // Menutup modal dengan aman (Menggunakan getOrCreateInstance)
                        var myModalEl = document.getElementById('modalUnlockDate');
                        var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
                        modal.hide();
                        
                        // Ubah ikon tombol
                        btnUnlockTrigger.classList.remove('btn-secondary');
                        btnUnlockTrigger.classList.add('btn-success');
                        btnUnlockTrigger.innerHTML = '<i class="fas fa-lock-open"></i>';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert(error.message);
                })
                .finally(() => {
                    btnConfirm.innerHTML = originalText;
                    btnConfirm.disabled = false;
                    document.getElementById('admin_username').value = "";
                    document.getElementById('admin_password').value = "";
                });
            });
        }

        // --- 2. Autocomplete Pay To (Menyimpan ID ke hidden input) ---
        const paytoInput = document.getElementById('payto');
        const userIdInput = document.getElementById('user_id');
        const suggestionBox = document.getElementById('payto-suggestions');

        if (paytoInput && suggestionBox) {
            const fetchSuggestions = function(query) {
                fetch(`{{ route('fincom.transexpense.searchPayto') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        suggestionBox.innerHTML = ''; 
                        if (data.length > 0) {
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action py-1 small';
                                li.style.cursor = 'pointer';
                                li.textContent = item.fullname; 
                                li.addEventListener('click', function() {
                                    paytoInput.value = item.fullname;
                                    userIdInput.value = item.id; // Simpan ID penerima
                                    suggestionBox.style.display = 'none';
                                });
                                suggestionBox.appendChild(li);
                            });
                            suggestionBox.style.display = 'block';
                        }
                    });
            };

            paytoInput.addEventListener('focus', function() { fetchSuggestions(this.value); });
            paytoInput.addEventListener('input', function() { fetchSuggestions(this.value); userIdInput.value = 0; });
            document.addEventListener('click', function(e) {
                if (e.target !== paytoInput && !suggestionBox.contains(e.target)) suggestionBox.style.display = 'none';
            });
        }
    });
</script>
@endsection