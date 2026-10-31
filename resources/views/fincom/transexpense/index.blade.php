@extends('layouts.app')

@section('title', 'Daftar Pengeluaran | MIMHa Finance')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3">Daftar Pengeluaran</h1>       
    </div>
    
    {{-- BARIS FILTER & AKSI --}}
    <form action="{{ route('fincom.transexpense.index') }}" method="GET" class="mb-3">
        <div class="card border-start border-4 border-primary shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-end g-2 small">
                    
                    {{-- 1. Tombol Tambah --}}
                    <div class="col-md-2">
                        <a href="{{ route('fincom.transexpense.create') }}" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div>

                    {{-- 2. Filter Tanggal (Dari & Sampai) --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="awal" id="awal" class="form-control" value="{{ request('awal') }}" placeholder="Dari">
                            <span class="input-group-text bg-light">s.d</span>
                            <input type="date" name="akhir" id="akhir" class="form-control" value="{{ request('akhir') }}" placeholder="Sampai">
                        </div>
                    </div>

                    {{-- 3. Filter Kasir --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1">Kasir</label>
                        <select name="cas_id" id="cas_id" class="form-select form-select-sm">
                            <option value="0" {{ request('cas_id', '0') == '0' ? 'selected' : '' }}>-- Semua Kasir --</option>
                            @foreach($cashiers as $cas)
                                <option value="{{ $cas->id }}" {{ request('cas_id') == $cas->id ? 'selected' : '' }}>
                                    {{ $cas->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Tombol Filter & Print Laporan --}}
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" name="filter_btn" value="1" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="cetakLaporan()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- TABEL DATA PENGELUARAN (Sesuai Urutan Kolom CI2) --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%" class="text-center">Tanggal</th>
                            <th width="12%">Referensi</th>
                            <th width="15%">Kasir</th>
                            <th width="15%">Kepada</th>
                            <th>Keterangan</th>
                            <th width="12%" class="text-end">Nilai</th>
                            <th width="8%" class="text-center">Edit</th>
                            <th width="5%" class="text-center">Print</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $row)
                            <tr>
                                <td class="text-center">{{ date('d M Y', strtotime($row->tdate)) }}</td>
                                <td class="fw-bold text-primary">{{ $row->ref_no }}</td>
                                <td>{{ $row->cashier_name ?? '-' }}</td>
                                <td>{{ $row->payto ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">{{ $row->note }}</small>
                                </td>
                                <td class="text-end fw-bold">
                                    {{-- Mengambil nilai credit (atau debit jika credit 0) --}}
                                    {{ number_format($row->credit > 0 ? $row->credit : $row->debit, 0, ',', '.') }}
                                </td>
                                
                                {{-- Kolom 7: Edit Transaksi (Konfirmasi hapus data lama persis CI2) --}}
                                {{-- Kolom 7: Edit Transaksi --}}
<td class="text-center">
    <a href="javascript:void(0);" 
       data-url="{{ route('fincom.transexpense.edit', $row->id) }}"
       onclick="confirmEdit(this)" 
       class="btn btn-warning btn-xs py-0 px-2" title="Edit">
       <i class="bi bi-pencil-square"></i> Edit
    </a>
</td>

                                {{-- Kolom 8: Print Kwitansi Per Baris --}}
                                <td class="text-center">
                                    <a href="{{ route('fincom.transexpense.receipt', $row->id) }}" target="_blank" class="btn btn-xs btn-info text-white py-0 px-2" title="Print Kwitansi">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data transaksi pengeluaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION LARAVEL --}}
            <div class="p-3 d-flex justify-content-end border-top">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
    {{-- Modal Otorisasi Admin untuk Edit --}}
    <div class="modal fade" id="modalAuthEdit" tabindex="-1" aria-labelledby="modalAuthEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6" id="modalAuthEditLabel">
                        <i class="bi bi-shield-lock text-danger"></i> Otorisasi Edit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Masukkan kredensial Admin untuk mengedit transaksi yang sudah di-posting.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" id="auth_username" class="form-control form-control-sm" autocomplete="off" placeholder="Username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" id="auth_password" class="form-control form-control-sm" placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnConfirmEditAuth">Verifikasi</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let targetEditUrl = '';

function cetakLaporan() {
    let casId = document.getElementById('cas_id').value;
    let awal = document.getElementById('awal').value || 'all';
    let akhir = document.getElementById('akhir').value || 'all';

    if (awal === 'all' || akhir === 'all') {
        alert('Silakan pilih rentang Tanggal Awal dan Akhir terlebih dahulu sebelum mencetak laporan.');
        return;
    }

    let startDate = new Date(awal);
    let endDate = new Date(akhir);
    let diffDays = (endDate.getTime() - startDate.getTime()) / (1000 * 3600 * 24);

    if (diffDays < 0) {
        alert('Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal!');
        return;
    }

    if (diffDays > 366) {
        alert('Rentang waktu cetak laporan maksimal adalah 1 Tahun (365 Hari). Silakan persempit filter tanggal Anda.');
        return;
    }

    let baseUrl = "{{ url('fincom/transexpense/p_expense') }}";
    let printUrl = `${baseUrl}/${casId}/${awal}/${akhir}`;
    window.open(printUrl, '_blank');
}

function confirmEdit(element) {
    // 1. Simpan URL tujuan
    targetEditUrl = element.getAttribute('data-url');
    
    // 2. Munculkan konfirmasi bawaan (opsional, persis CI2)
    if (confirm('Mengedit data akan menghapus rincian lama dan menggantinya dengan yang baru, lanjutkan?')) {
        // 3. Jangan langsung redirect, tapi buka modal otorisasi
        var myModalEl = document.getElementById('modalAuthEdit');
        var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
        modal.show();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const btnConfirm = document.getElementById('btnConfirmEditAuth');
    
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            let user = document.getElementById('auth_username').value;
            let pass = document.getElementById('auth_password').value;

            if (user === "" || pass === "") {
                alert("Username dan Password tidak boleh kosong!");
                return;
            }

            let originalText = btnConfirm.innerHTML;
            btnConfirm.innerHTML = "Memverifikasi...";
            btnConfirm.disabled = true;

            // Panggil API verifyAdmin yang sudah Anda buat
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
                    // Jika sukses, langsung alihkan ke halaman Edit!
                    window.location.href = targetEditUrl;
                } else {
                    alert(data.message); // Alert jika password salah
                }
            })
            .catch(error => {
                alert(error.message);
            })
            .finally(() => {
                btnConfirm.innerHTML = originalText;
                btnConfirm.disabled = false;
                document.getElementById('auth_username').value = "";
                document.getElementById('auth_password').value = "";
            });
        });
    }
});
</script>
@endpush