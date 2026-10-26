@extends('layouts.app')

@section('title', 'Entri Nilai Katering')

@section('content')
<style>
    .filter-box {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Entri Nilai Katering</h1>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('fincom.payment.catering') }}" method="POST">
    @csrf

    <div class="filter-box bg-body-tertiary border">
        <div class="row align-items-center">
            
            <div class="col-md-6 border-end">
                <label class="fw-bold mb-1">Hitung Nilai Aktual adalah :</label>
                <div class="input-group input-group-sm mb-1" style="max-width: 320px;">
                    <input type="text" name="days" class="form-control text-center" value="20" maxlength="3">
                    <span class="input-group-text">kali dari Harian</span>
                    <button type="submit" name="calc" value="1" class="btn btn-warning">Perbarui Aktual</button>
                </div>
                <small class="text-muted" style="font-size: 0.8rem;">
                    Nilai aktual adalah nilai yang dibayar oleh peserta catering.<br>
                    Setting ID kelas CATERING di tabel OPTION.
                </small>
            </div>

            <div class="col-md-6 ps-4">
                <button type="submit" name="sync" value="1" class="btn btn-info btn-sm mb-1 text-white">
                    <i class="bi bi-arrow-repeat"></i> Sinkron ke Komp. Pembayaran
                </button><br>
                <small class="text-muted" style="font-size: 0.8rem;">
                    Sinkron akan memperbarui nilai tagihan katering siswa (piutang katering).
                </small>
            </div>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="70">No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th class="text-center" width="200">Harian</th>
                            <th class="text-center" width="200">Aktual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caterings as $index => $c)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $c->fullname }}</td>
                                <td>{{ $c->class_title }}</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="def[{{ $c->classuser_id }}]" 
                                               class="form-control text-end format-ribuan" 
                                               value="{{ number_format($c->cat_def, 0, ',', '.') }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="act[{{ $c->classuser_id }}]" 
                                            class="form-control text-end format-ribuan" 
                                            value="{{ number_format($c->cat_act, 0, ',', '.') }}">
                                        
                                        <input type="hidden" name="user[{{ $c->classuser_id }}]" value="{{ $c->user_id }}">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data siswa untuk kelas katering ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(count($caterings) > 0)
            <div class="card-footer text-center py-3">
                <button type="submit" name="save" value="1" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Default
                </button>
            </div>
        @endif
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    $(document).ready(function() {
        $('.format-ribuan').on('input', function() {
            let val = $(this).val().replace(/\./g, '');
            if($.isNumeric(val)) {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            } else {
                $(this).val(val.replace(/[^0-9]/g, ''));
            }
        });
    });
</script>
@endsection