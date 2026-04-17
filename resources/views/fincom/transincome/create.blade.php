@extends('layouts.app')

@section('title', 'Tambah Pemasukan | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Tambah Pemasukan Baru</h1>
    <a href="{{ route('fincom.transincome.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

{{-- Menampilkan pesan error atau success --}}
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

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('fincom.transincome.store') }}" method="POST">
    @csrf

    {{-- BAGIAN HEADER TRANSAKSI --}}
    <div class="card shadow-sm mb-4 border-start border-4 border-success">
        <div class="card-header bg-white fw-bold">Data Utama Transaksi</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Petugas / Kasir</label>
                    <input type="text" class="form-control bg-light" value="{{ $petugasName }}" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="tdate" class="form-label">Tanggal Transaksi</label>
                    <div class="input-group">
                        <input type="date" class="form-control bg-light" name="tdate" id="tdate" value="{{ date('Y-m-d') }}" readonly>
                        <button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalUnlockDate" id="btn-unlock-date" title="Buka Kunci Tanggal">
                            <i class="bi bi-lock-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">No Referensi</label>
                    <input type="text" name="ref_no" class="form-control bg-light text-success fw-bold" value="{{ $autoRef }}" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3 position-relative">
                    <label for="payto" class="form-label">Diterima Dari <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="payto" id="payto" autocomplete="off" placeholder="Ketik nama atau klik di sini..." required>
                    
                    <ul class="list-group position-absolute w-100 shadow-sm" id="payto-suggestions" style="display: none; z-index: 1050; max-height: 250px; overflow-y: auto; top: 100%;"></ul>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Keterangan Umum (Note)</label>
                    <textarea name="header_note" class="form-control" rows="2" placeholder="Catatan transaksi pemasukan..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN DETAIL ITEM (DINAMIS) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Rincian Pemasukan</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="detailTable">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="25%">Jenis Pemasukan</th>
                            <th width="35%">Keterangan Item</th>
                            <th width="25%">Nominal (Rp)</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Baris Pertama Default --}}
                        <tr class="item-row">
                            <td>
                                <select name="items[0][payitem_id]" class="form-select" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach($payitems as $pi)
                                        <option value="{{ $pi->id }}">{{ $pi->title }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[0][note]" class="form-control" placeholder="Keterangan rincian" required>
                            </td>
                            <td>
                                <input type="text" name="items[0][amount]" class="form-control amount-input text-end" placeholder="0" required onkeyup="formatRupiah(this); calculateTotal()">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-danger btn-remove" disabled><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light fw-bold">
                            <td colspan="2" class="text-end align-middle fs-5">TOTAL PEMASUKAN :</td>
                            <td>
                                <input type="text" id="totalAmount" class="form-control text-end fw-bold text-success fs-5 bg-white" value="0" readonly>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" id="addRow" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Tambah Baris</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-success btn-lg px-5 fw-bold"><i class="bi bi-save"></i> Simpan Transaksi</button>
    </div>
</form>

{{-- MODAL UNLOCK TANGGAL --}}
<div class="modal fade" id="modalUnlockDate" tabindex="-1" aria-labelledby="modalUnlockDateLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUnlockDateLabel"><i class="bi bi-shield-lock-fill text-success"></i> Otorisasi Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Masukkan kredensial Administrator untuk mengubah tanggal transaksi.</p>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" id="admin_username" class="form-control" autocomplete="off" placeholder="Username Admin">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="admin_password" class="form-control" placeholder="••••••••">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmUnlock">Verifikasi</button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT UNTUK TABEL DINAMIS DAN FORMAT RUPIAH --}}
<script>
    let rowIdx = 1; // Index untuk input name array

    // Fungsi Tambah Baris
    document.getElementById('addRow').addEventListener('click', function () {
        let tbody = document.querySelector('#detailTable tbody');
        let tr = document.createElement('tr');
        tr.className = 'item-row';
        
        tr.innerHTML = `
            <td>
                <select name="items[${rowIdx}][payitem_id]" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($payitems as $pi)
                        <option value="{{ $pi->id }}">{{ $pi->title }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIdx}][note]" class="form-control" placeholder="Keterangan rincian" required>
            </td>
            <td>
                <input type="text" name="items[${rowIdx}][amount]" class="form-control amount-input text-end" placeholder="0" required onkeyup="formatRupiah(this); calculateTotal()">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIdx++;
    });

    // Fungsi Hapus Baris
    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotal();
    }

    // Fungsi Format Ribuan (Rupiah) saat mengetik
    function formatRupiah(input) {
        let value = input.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = rupiah;
    }

    // Fungsi Hitung Total
    function calculateTotal() {
        let inputs = document.querySelectorAll('.amount-input');
        let total = 0;

        inputs.forEach(function(input) {
            let val = input.value.replace(/\./g, ''); 
            if (val) {
                total += parseFloat(val);
            }
        });

        document.getElementById('totalAmount').value = new Intl.NumberFormat('id-ID').format(total);
    } 

    // SCRIPT UNTUK MODAL UNLOCK TANGGAL
    document.addEventListener('DOMContentLoaded', function () {
        const btnConfirm = document.getElementById('btnConfirmUnlock');
        const inputDate = document.getElementById('tdate');
        const btnUnlockTrigger = document.getElementById('btn-unlock-date');
        
        btnConfirm.addEventListener('click', function() {
            let user = document.getElementById('admin_username').value;
            let pass = document.getElementById('admin_password').value;

            if (user === "" || pass === "") {
                alert("Username dan Password tidak boleh kosong!");
                return;
            }

            // Ubah tombol menjadi loading
            let originalText = btnConfirm.innerHTML;
            btnConfirm.innerHTML = "Memverifikasi...";
            btnConfirm.disabled = true;

            // Kirim request ke server Laravel (Route Transincome)
            fetch("{{ route('fincom.transincome.verifyAdmin') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    username: user,
                    password: pass
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || "Terjadi kesalahan server");
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Jika Sukses
                    inputDate.removeAttribute('readonly');
                    inputDate.classList.remove('bg-light');
                    
                    var myModalEl = document.getElementById('modalUnlockDate');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    modal.hide();
                    
                    btnUnlockTrigger.classList.remove('btn-secondary');
                    btnUnlockTrigger.classList.add('btn-success');
                    btnUnlockTrigger.innerHTML = '<i class="bi bi-unlock-fill"></i>';
                } else {
                    // Jika password salah / bukan admin
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error Detail:", error);
                alert(error.message);
            })
            .finally(() => {
                btnConfirm.innerHTML = originalText;
                btnConfirm.disabled = false;
                document.getElementById('admin_username').value = "";
                document.getElementById('admin_password').value = "";
            });
        });
    });
    
    // SCRIPT UNTUK AUTOCOMPLETE DITERIMA DARI (PAY TO)
    document.addEventListener('DOMContentLoaded', function () {
        const paytoInput = document.getElementById('payto');
        const suggestionBox = document.getElementById('payto-suggestions');

        if (paytoInput && suggestionBox) {
            const fetchSuggestions = function(query) {
                // Mengarah ke route searchPayto milik transincome
                fetch(`{{ route('fincom.transincome.searchPayto') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionBox.innerHTML = ''; 
                        
                        if (data.length > 0) {
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.style.cursor = 'pointer';
                                li.textContent = item.fullname; 
                                
                                li.addEventListener('click', function() {
                                    paytoInput.value = item.fullname;
                                    suggestionBox.style.display = 'none';
                                });
                                
                                suggestionBox.appendChild(li);
                            });
                            suggestionBox.style.display = 'block';
                        } else {
                            const li = document.createElement('li');
                            li.className = 'list-group-item text-muted';
                            li.textContent = 'Nama tidak ditemukan...';
                            suggestionBox.appendChild(li);
                            suggestionBox.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching payto:', error);
                    });
            };

            paytoInput.addEventListener('focus', function() { fetchSuggestions(this.value); });
            paytoInput.addEventListener('input', function() { fetchSuggestions(this.value); });
            document.addEventListener('click', function(e) {
                if (e.target !== paytoInput && !suggestionBox.contains(e.target)) {
                    suggestionBox.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection