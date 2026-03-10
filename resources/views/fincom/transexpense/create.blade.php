@extends('layouts.app')

@section('title', 'Tambah Pengeluaran | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Tambah Pengeluaran Baru</h1>
    <a href="{{ route('fincom.transexpense.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

{{-- Menampilkan pesan error jika validasi gagal --}}
@if ($errors->any())
    {{-- Menampilkan pesan error dari session (kegagalan database/sistem) --}}
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
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('fincom.transexpense.store') }}" method="POST">
    @csrf

    {{-- BAGIAN HEADER TRANSAKSI --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">Data Utama Transaksi</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">No Referensi</label>
                    {{-- Nilai autoRef dari controller --}}
                    <input type="text" name="ref_no" class="form-control bg-light" value="{{ $autoRef }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                    <input type="date" name="tdate" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Petugas / Kasir</label>
                    <input type="text" class="form-control bg-light" value="{{ $petugasName }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dibayar Kepada (Pay To) <span class="text-danger">*</span></label>
                    <input type="text" name="payto" id="payto" class="form-control" placeholder="Nama Penerima" required>
                    {{-- Note: Nanti kita bisa tambahkan fitur AJAX Autocomplete searchPayto di input ini --}}
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keterangan Umum (Note)</label>
                    <textarea name="header_note" class="form-control" rows="2" placeholder="Catatan transaksi..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN DETAIL ITEM (DINAMIS) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">Rincian Pengeluaran</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="detailTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="25%">Jenis Pengeluaran</th>
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
                                <input type="text" name="items[0][note]" class="form-control" placeholder="Keterangan rincian">
                            </td>
                            <td>
                                <input type="text" name="items[0][amount]" class="form-control amount-input text-end" placeholder="0" required onkeyup="formatRupiah(this); calculateTotal()">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-remove" disabled><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light fw-bold">
                            <td colspan="2" class="text-end align-middle">TOTAL PENGELUARAN</td>
                            <td>
                                <input type="text" id="totalAmount" class="form-control text-end bg-white fw-bold" value="0" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" id="addRow" class="btn btn-sm btn-success"><i class="bi bi-plus-circle"></i> Tambah Baris</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-save"></i> Simpan Transaksi</button>
    </div>
</form>

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
                <input type="text" name="items[${rowIdx}][note]" class="form-control" placeholder="Keterangan rincian">
            </td>
            <td>
                <input type="text" name="items[${rowIdx}][amount]" class="form-control amount-input text-end" placeholder="0" required onkeyup="formatRupiah(this); calculateTotal()">
            </td>
            <td class="text-center">
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
            // Hilangkan titik untuk perhitungan matematik
            let val = input.value.replace(/\./g, ''); 
            if (val) {
                total += parseFloat(val);
            }
        });

        // Format kembali hasil total ke string Rupiah
        document.getElementById('totalAmount').value = new Intl.NumberFormat('id-ID').format(total);
    }
</script>
@endsection