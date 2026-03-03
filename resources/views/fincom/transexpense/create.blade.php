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
                
                {{-- HEADER MOCKUP LAYOUT --}}
                <div class="row g-3 align-items-center mb-4 p-3 border rounded bg-white shadow-sm">
                    <div class="col-md-3 border-end">
                        <label class="small fw-bold text-muted d-block mb-1 text-uppercase">Dari (Kasir)</label>
                        <select name="user_id" class="form-select form-select-sm border-primary" required>
                            <option value="">-- Pilih Kasir --</option>
                            @foreach($cass as $cas)
                                <option value="{{ $cas->id }}" {{ Auth::id() == $cas->id ? 'selected' : '' }}>
                                    {{ $cas->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 border-end">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small fw-bold text-muted d-block mb-1 text-uppercase">No. Ref</label>
                                <input type="text" name="ref_no" class="form-control form-control-sm bg-light fw-bold" value="{{ $autoRef }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold text-muted d-block mb-1 text-uppercase">Tanggal</label>
                                <input type="date" name="tdate" class="form-control form-control-sm border-primary" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="small fw-bold text-muted d-block mb-1 text-uppercase">Diberikan Kepada (Pay To)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                            <input type="text" name="payto" class="form-control form-control-sm border-primary" placeholder="Masukkan nama penerima dana..." required>
                        </div>
                    </div>
                </div>

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
@endsection