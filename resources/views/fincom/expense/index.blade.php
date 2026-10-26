@extends('layouts.app')

@section('title', 'Komponen Pengeluaran & Pemasukan | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Komponen Pengeluaran & Pemasukan</h1>
    </div>

    {{-- Pesan Sukses / Error --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- BAGIAN KIRI: TABEL DAFTAR KOMPONEN --}}
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Daftar Komponen</h6>
                    <form action="{{ route('fincom.expense.index') }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Cari kode / nama..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Komponen</th>
                                    <th class="text-center">Tipe</th>
                                    <th class="text-center">Urutan</th>
                                    <th class="text-center" width="90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payitems as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item->payitem_code }}</td>
                                        <td>
                                            <strong>{{ $item->title }}</strong>
                                            <div class="small text-muted" style="font-size: 10px;">
                                                @if($item->coa_cash != '0') Kas: {{ $item->coa_cash }} @endif
                                                @if($item->coa_cost != '0') | Biaya: {{ $item->coa_cost }} @endif
                                                @if($item->coa_revenue != '0') | Pendapatan: {{ $item->coa_revenue }} @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->payitem_type == 'expense' ? 'bg-danger' : ($item->payitem_type == 'income' ? 'bg-success' : 'bg-secondary') }}">
                                                {{ strtoupper($item->payitem_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item->ordering }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('fincom.expense.index', ['edit' => $item->id] + request()->except('edit')) }}" class="btn btn-xs btn-outline-primary py-0" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('fincom.expense.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus komponen ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger py-0" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Tidak ada data komponen.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $payitems->links() }}
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN: FORM TAMBAH / EDIT --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header {{ $details ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi {{ $details ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i> 
                        {{ $details ? 'Edit Data Komponen' : 'Tambah Komponen Baru' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('fincom.expense.store') }}" method="POST">
                        @csrf
                        @if($details)
                            <input type="hidden" name="payitem_id" value="{{ $details->id }}">
                        @endif

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Kode Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="payitem_code" class="form-control form-control-sm" value="{{ old('payitem_code', $details->payitem_code ?? '') }}" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $details->title ?? '') }}" required>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pemakai</label>
                                <select name="payitem_user" class="form-select form-select-sm">
                                    <option value="finance" {{ old('payitem_user', $details->payitem_user ?? '') == 'finance' ? 'selected' : '' }}>Keuangan</option>
                                    <option value="cashier" {{ old('payitem_user', $details->payitem_user ?? '') == 'cashier' ? 'selected' : '' }}>Kasir</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tipe Komponen</label>
                                <select name="payitem_type" class="form-select form-select-sm">
                                    <option value="expense" {{ old('payitem_type', $details->payitem_type ?? '') == 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                                    <option value="income" {{ old('payitem_type', $details->payitem_type ?? '') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                                    <option value="journal" {{ old('payitem_type', $details->payitem_type ?? '') == 'journal' ? 'selected' : '' }}>Jurnal</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Urutan Tampil</label>
                            <input type="number" name="ordering" class="form-control form-control-sm" value="{{ old('ordering', $details->ordering ?? 1) }}" required>
                        </div>

                        <hr>
                        <!-- TABEL PANDUAN AKUN (LAMA) -->
                        <div class="table-responsive my-3">
                            <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size: 11px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-bold">TIPE</th>
                                        <th class="fw-bold">AKUN-DEB</th>
                                        <th class="fw-bold">AKUN-CRE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>PENGELUARAN</td>
                                        <td>BIAYA</td>
                                        <td>KAS</td>
                                    </tr>
                                    <tr>
                                        <td>PENGELUARAN</td>
                                        <td>HUTANG</td>
                                        <td>KAS</td>
                                    </tr>
                                    <tr>
                                        <td>PEMASUKAN</td>
                                        <td>KAS</td>
                                        <td>PENDAPATAN</td>
                                    </tr>
                                    <tr>
                                        <td>PEMASUKAN</td>
                                        <td>KAS</td>
                                        <td>HUTANG</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-3">
                        <h6 class="small fw-bold text-muted mb-2">Pengaturan Akun Buku Besar (COA)</h6>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small">Akun Kas <span class="text-danger">*</span></label>
                                <select name="coa_cash" class="form-select form-select-sm">
                                    <option value="0">- Pilih -</option>
                                    @foreach($coa_cash as $coa)
                                        <option value="{{ $coa->coa_code }}" {{ old('coa_cash', $details->coa_cash ?? '') == $coa->coa_code ? 'selected' : '' }}>{{ $coa->coa_code }} - {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Akun Hutang</label>
                                <select name="coa_payable" class="form-select form-select-sm">
                                    <option value="0">- Pilih -</option>
                                    @foreach($coa_payable as $coa)
                                        <option value="{{ $coa->coa_code }}" {{ old('coa_payable', $details->coa_payable ?? '') == $coa->coa_code ? 'selected' : '' }}>{{ $coa->coa_code }} - {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small">Akun Biaya</label>
                                <select name="coa_cost" class="form-select form-select-sm">
                                    <option value="0">- Pilih -</option>
                                    @foreach($coa_cost as $coa)
                                        <option value="{{ $coa->coa_code }}" {{ old('coa_cost', $details->coa_cost ?? '') == $coa->coa_code ? 'selected' : '' }}>{{ $coa->coa_code }} - {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Akun Piutang</label>
                                <select name="coa_receivable" class="form-select form-select-sm">
                                    <option value="0">- Pilih -</option>
                                    @foreach($coa_receivable as $coa)
                                        <option value="{{ $coa->coa_code }}" {{ old('coa_receivable', $details->coa_receivable ?? '') == $coa->coa_code ? 'selected' : '' }}>{{ $coa->coa_code }} - {{ $coa->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Akun Pendapatan</label>
                            <select name="coa_revenue" class="form-select form-select-sm">
                                <option value="0">- Pilih -</option>
                                @foreach($coa_revenue as $coa)
                                    <option value="{{ $coa->coa_code }}" {{ old('coa_revenue', $details->coa_revenue ?? '') == $coa->coa_code ? 'selected' : '' }}>{{ $coa->coa_code }} - {{ $coa->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Nilai Default (Rp)</label>
                            <input type="text" name="payvalue" id="input_payvalue" class="form-control form-control-sm text-end" value="{{ number_format(old('payvalue', $details->payvalue ?? 0), 0, ',', '.') }}">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @if($details)
                                <a href="{{ route('fincom.expense.index') }}" class="btn btn-sm btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-sm btn-warning">Update Komponen</button>
                            @else
                                <button type="submit" class="btn btn-sm btn-primary">Simpan Komponen</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Format otomatis angka rupiah saat mengetik
    document.addEventListener("DOMContentLoaded", function() {
        const payvalueInput = document.getElementById('input_payvalue');
        if (payvalueInput) {
            payvalueInput.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^0-9]/g, '');
                if(val !== '') {
                    val = parseInt(val, 10).toLocaleString('id-ID');
                }
                this.value = val;
            });
        }
    });
</script>
@endpush