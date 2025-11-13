@extends('layouts.app')

@section('title', 'Orang Tua Baru | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Orang Tua Baru</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('parent.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Orang Tua
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-4" role="alert">
                <h5 class="alert-heading">Terjadi Kesalahan!</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('parent.store') }}" method="POST" id="parent-form">
            @csrf
            
            <div class="row">
                {{-- Kolom Kiri: Data Pribadi --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Pribadi</h5>
                    {{-- ... (Field Nama s/d Agama tetap sama) ... --}}
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Nama Lengkap *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname') }}" required>
                        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nama Panggilan</label>
                        <input type="text" id="nickname" name="nickname" class="form-control" value="{{ old('nickname') }}">
                    </div>
                    <div class="mb-3">
                        <label for="placeofbirth" class="form-label">Tempat Lahir</label>
                        <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" value="{{ old('placeofbirth') }}">
                    </div>
                    <div class="mb-3">
                        <label for="dateofbirth" class="form-label">Tanggal Lahir</label>
                        <input type="date" id="dateofbirth" name="dateofbirth" class="form-control" value="{{ old('dateofbirth') }}">
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="street" class="form-label">Alamat (Jalan)</label>
                        <textarea id="street" name="street" class="form-control">{{ old('street') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">Kota</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}">
                    </div>
                    <div class="mb-3">
                        <label for="province" class="form-label">Provinsi</label>
                        <input type="text" id="province" name="province" class="form-control" value="{{ old('province') }}">
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Negara</label>
                        <input type="text" id="country" name="country" class="form-control" value="{{ old('country') }}">
                    </div>
                    <div class="mb-3">
                        <label for="postalcode" class="form-label">Kode Pos</label>
                        <input type="text" id="postalcode" name="postalcode" class="form-control" value="{{ old('postalcode') }}">
                    </div>
                    <div class="mb-3">
                        <label for="home_phone" class="form-label">Telepon Rumah</label>
                        <input type="text" id="home_phone" name="home_phone" class="form-control" value="{{ old('home_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="mobile_phone" class="form-label">HP</label>
                        <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" value="{{ old('mobile_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="religion" class="form-label">Agama</label>
                        <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion') }}">
                    </div>
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Aktif</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', 'yes') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ old('is_active') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    {{-- [PERUBAHAN 1] Menambahkan dropdown Guru --}}
                    <div class="mb-3">
                        <label for="is_teacher" class="form-label">Guru (Yes/No)</label>
                        <select id="is_teacher" name="is_teacher" class="form-select">
                            <option value="no" {{ old('is_teacher', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_teacher') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>

                {{-- Kolom Kanan: Data Lainnya (Pekerjaan) --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Lainnya</h5>
                    
                    {{-- [PERUBAHAN 2] Input Pendidikan menjadi dropdown --}}
                    <div class="mb-3">
                        <label for="education" class="form-label">Pendidikan</label>
                        <select id="education" name="education" class="form-select">
                            <option value="" {{ old('education') == '' ? 'selected' : '' }}>-- Pilih Pendidikan --</option>
                            <option value="SD" {{ old('education') == 'SD' ? 'selected' : '' }}>SD</option>
                            <option value="SMP" {{ old('education') == 'SMP' ? 'selected' : '' }}>SMP</option>
                            <option value="SMA" {{ old('education') == 'SMA' ? 'selected' : '' }}>SMA</option>
                            <option value="D3" {{ old('education') == 'D3' ? 'selected' : '' }}>D3</option>
                            <option value="S1" {{ old('education') == 'S1' ? 'selected' : '' }}>S1</option>
                            <option value="S2" {{ old('education') == 'S2' ? 'selected' : '' }}>S2</option>
                            <option value="S3" {{ old('education') == 'S3' ? 'selected' : '' }}>S3</option>
                        </select>
                    </div>
                    
                    {{-- [PERUBAHAN 3] Input Pekerjaan menjadi dropdown --}}
                    <div class="mb-3">
                        <label for="profession" class="form-label">Pekerjaan</label>
                        <select id="profession" name="profession" class="form-select">
                            <option value="" {{ old('profession') == '' ? 'selected' : '' }}>-- Pilih Pekerjaan --</option>
                            <option value="PNS" {{ old('profession') == 'PNS' ? 'selected' : '' }}>PNS</option>
                            <option value="Guru/Dosen" {{ old('profession') == 'Guru/Dosen' ? 'selected' : '' }}>Guru/Dosen</option>
                            <option value="Karyawan Swasta" {{ old('profession') == 'Karyawan Swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                            <option value="Tukang" {{ old('profession') == 'Tukang' ? 'selected' : '' }}>Tukang</option>
                            <option value="Petani" {{ old('profession') == 'Petani' ? 'selected' : '' }}>Petani</option>
                            <option value="Wiraswasta" {{ old('profession') == 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                            <option value="Karyawan BUMN" {{ old('profession') == 'Karyawan BUMN' ? 'selected' : '' }}>Karyawan BUMN</option>
                            <option value="Ibu Rumah Tangga" {{ old('profession') == 'Ibu Rumah Tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                            <option value="Buruh Harian Lepas" {{ old('profession') == 'Buruh Harian Lepas' ? 'selected' : '' }}>Buruh Harian Lepas</option>
                            <option value="TNI/POLRI" {{ old('profession') == 'TNI/POLRI' ? 'selected' : '' }}>TNI/POLRI</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company" class="form-label">Nama Perusahaan</label>
                        <input type="text" id="company" name="company" class="form-control" value="{{ old('company') }}">
                    </div>
                    <div class="mb-3">
                        <label for="company_phone" class="form-label">Telp Kantor</label>
                        <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Posisi</label>
                        <input type="text" id="position" name="position" class="form-control" value="{{ old('position') }}">
                    </div>
                    
                    {{-- [PERUBAHAN 4] Input Penghasilan menjadi dropdown --}}
                    <div class="mb-3">
                        <label for="salary" class="form-label">Penghasilan</label>
                        <select id="salary" name="salary" class="form-select">
                            <option value="" {{ old('salary') == '' ? 'selected' : '' }}>-- Pilih Penghasilan --</option>
                            <option value="< Rp 1 Juta" {{ old('salary') == '< Rp 1 Juta' ? 'selected' : '' }}>&lt; Rp 1 Juta</option>
                            <option value="Rp 1 Juta - Rp 2 Juta" {{ old('salary') == 'Rp 1 Juta - Rp 2 Juta' ? 'selected' : '' }}>Rp 1 Juta - Rp 2 Juta</option>
                            <option value="Rp 2 Juta - Rp 5 Juta" {{ old('salary') == 'Rp 2 Juta - Rp 5 Juta' ? 'selected' : '' }}>Rp 2 Juta - Rp 5 Juta</option>
                            <option value="> Rp 5 juta" {{ old('salary') == '> Rp 5 juta' ? 'selected' : '' }}>&gt; Rp 5 juta</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" id="save-button" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Simpan Data Orang Tua
                </button>
                <a href="{{ route('parent.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script validasi (tidak ada validasi real-time, hanya hapus error server) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saveButton = document.getElementById('save-button');
        const form = document.getElementById('parent-form');

        function checkFormValidity() {
            const invalidInputs = form.querySelectorAll('.is-invalid');
            saveButton.disabled = invalidInputs.length > 0;
        }
        
        // Simpan error server awal
        form.querySelectorAll('input.is-invalid, select.is-invalid').forEach(function(input) {
             let errorFeedback = input.closest('.mb-3').querySelector('.invalid-feedback');
             if (errorFeedback && errorFeedback.textContent) {
                 errorFeedback.setAttribute('data-server-error', errorFeedback.textContent);
             }
        });

        // Hapus error server saat pengguna mulai mengetik ulang
        form.querySelectorAll('input, select, textarea').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                    if(errorFeedback) {
                        errorFeedback.textContent = '';
                        errorFeedback.removeAttribute('data-server-error');
                    }
                    checkFormValidity();
                }
            });
        });

        checkFormValidity();
    });
</script>
@endpush