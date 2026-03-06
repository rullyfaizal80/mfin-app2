@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <form action="{{ route('fincom.transexpense.store') }}" method="POST">
        @csrf
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0"><i class="bi bi-pencil-square"></i> Entry Pengeluaran Kas</h6>
            </div>
            <div class="card-body bg-light">
                
                {{-- BAGIAN ATAS (HEADER BARU) --}}
<div class="p-3 mb-4 bg-white border rounded shadow-sm">
    
    {{-- Baris 1: Petugas, Referensi, Tanggal --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
    <label class="small fw-bold text-muted mb-1">Petugas</label>
    
    {{-- Memanggil variabel dari Controller --}}
    <input type="text" class="form-control form-control-sm bg-light fw-bold text-primary" 
           value="{{ $petugasName }}" readonly>
           
    {{-- Hidden input untuk menyimpan user_id ke database --}}
    <input type="hidden" name="user_id" value="{{ $petugasId }}">
</div>
        <div class="col-md-4">
            <label class="small fw-bold text-muted mb-1">Referensi</label>
            <input type="text" name="ref_no" class="form-control form-control-sm bg-light fw-bold text-primary" 
                   value="{{ $autoRef }}" readonly>
        </div>

        <div class="col-md-4">
            <label class="small fw-bold text-muted mb-1">Tanggal</label>
            <div class="input-group input-group-sm">
                <input type="date" name="tdate" id="tdate" class="form-control bg-light" value="{{ date('Y-m-d') }}" readonly>
                <button type="button" class="btn btn-warning text-dark px-3" data-bs-toggle="modal" data-bs-target="#modalUnlockDate" title="Unlock Tanggal">
                    <i class="bi bi-unlock-fill"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Baris 2: Akun Kas, Dengan (Pay To), Keterangan --}}
    <div class="row g-3">
        <div class="col-md-4">
            <label class="small fw-bold text-muted mb-1">Akun Kas</label>
            <select name="coa_id" class="form-select form-select-sm border-primary" required>
                <option value="">-- Pilih Akun Kas --</option>
                @foreach($coas as $coa)
                    {{-- Sesuaikan nama kolom jika bukan 'title' (bisa 'name' atau 'coa_name') --}}
                    <option value="{{ $coa->id }}">{{ $coa->title ?? $coa->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="small fw-bold text-muted mb-1">Dengan</label>
            <div class="input-group input-group-sm">
                <input type="text" name="payto" id="payto" class="form-control border-primary" placeholder="Nama penerima..." required>
                <button type="button" class="btn btn-secondary px-3" id="btnSearchUser" title="Cari Data">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <div class="col-md-4">
            <label class="small fw-bold text-muted mb-1">Keterangan</label>
            <input type="text" name="header_note" class="form-control form-control-sm" placeholder="Catatan transaksi (opsional)...">
        </div>
    </div>
</div>

{{-- TABEL RINCIAN ITEM TETAP DI BAWAH SINI ... --}}

                {{-- TABEL RINCIAN --}}
                <div class="table-responsive bg-white rounded shadow-sm">
                    <table class="table table-sm table-hover table-bordered mb-0">
                        <thead class="table-dark small">
                            <tr class="text-center">
                                <th width="20%">Jenis Komponen</th>
                                <th>Keterangan / Note</th>
                                <th width="15%">Unit Sekolah</th>
                                <th width="12%">Grade</th>
                                <th width="15%">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][payitem_id]" class="form-select form-select-sm" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        @foreach($payitems as $pi)
                                            <option value="{{ $pi->id }}">{{ $pi->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[0][note]" class="form-control form-control-sm" placeholder="...">
                                </td>
                                <td>
                                    <select name="items[0][school_id]" class="form-select form-select-sm">
                                        <option value="0">Semua Unit</option>
                                        @foreach($schools as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][grade_id]" class="form-select form-select-sm">
                                        <option value="0">Semua Grade</option>
                                        @foreach($grades as $g)
                                            <option value="{{ $g->id }}">{{ $g->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][amount]" class="form-control form-control-sm text-end fw-bold text-primary" placeholder="0" required>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle"></i> Pastikan semua data sudah benar sebelum menekan tombol simpan.
                    </div>
                    <div>
                        <a href="{{ route('fincom.transexpense.index') }}" class="btn btn-outline-secondary btn-sm px-4 me-2">Batal</a>
                        <button type="submit" class="btn btn-primary btn-sm px-5 shadow-sm fw-bold">Simpan Transaksi</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="modalUnlockDate" tabindex="-1" aria-labelledby="modalUnlockDateLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning py-2">
                <h6 class="modal-title text-dark fw-bold" id="modalUnlockDateLabel"><i class="bi bi-shield-lock"></i> Otorisasi Admin</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Masukkan kredensial Admin untuk mengubah tanggal transaksi.</p>
                <div class="mb-2">
                    <input type="text" id="admin_username" class="form-control form-control-sm" placeholder="Username Admin">
                </div>
                <div class="mb-3">
                    <input type="password" id="admin_password" class="form-control form-control-sm" placeholder="Password Admin">
                </div>
                <button type="button" class="btn btn-primary btn-sm w-100" id="btnConfirmUnlock">Buka Kunci Tanggal</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnConfirm = document.getElementById('btnConfirmUnlock');
        const inputDate = document.getElementById('tdate');
        
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

            // Kirim request ke server Laravel
            fetch("{{ route('fincom.transexpense.verifyAdmin') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}" // Wajib di Laravel
                },
                body: JSON.stringify({
                    username: user,
                    password: pass
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Jika Server menjawab sukses (Admin Valid)
                    inputDate.removeAttribute('readonly');
                    inputDate.classList.remove('bg-light');
                    
                    // Tutup modal
                    var myModalEl = document.getElementById('modalUnlockDate');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    modal.hide();
                    
                    alert("Tanggal berhasil dibuka. Silakan ubah tanggal transaksi.");
                } else {
                    // Jika Server menjawab gagal (Salah password atau bukan admin)
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Terjadi kesalahan jaringan.");
            })
            .finally(() => {
                // Kembalikan tombol seperti semula dan kosongkan form
                btnConfirm.innerHTML = originalText;
                btnConfirm.disabled = false;
                document.getElementById('admin_username').value = "";
                document.getElementById('admin_password').value = "";
            });
        });
    });
</script>
@endsection