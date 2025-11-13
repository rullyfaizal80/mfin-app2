@extends('layouts.app')

@section('title', 'Pendidik Baru | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Pendidik / Tenaga Kependidikan Baru</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Pendidik
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

        <form action="{{ route('teacher.store') }}" method="POST" id="teacher-form">
            @csrf
            
            <div class="row">
                {{-- Kolom Kiri: Data Pribadi --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Pribadi</h5>
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
                </div>

                {{-- Kolom Kanan: Data Lainnya (Kepegawaian) --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Kepegawaian</h5>
                    
                    {{-- [PERBAIKAN] Tata letak 1 kolom sesuai daftar Anda --}}
                    <div class="mb-3">
                        <label for="emp_status" class="form-label">Status</label>
                        <select id="emp_status" name="emp_status" class="form-select">
                            <option value="permanent" {{ old('emp_status', 'permanent') == 'permanent' ? 'selected' : '' }}>Permanen</option>
                            <option value="probation" {{ old('emp_status') == 'probation' ? 'selected' : '' }}>Percobaan</option>
                            <option value="contract" {{ old('emp_status') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIP *</label>
                        <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" required>
                        @error('nik') <div class="invalid-feedback" data-server-error="{{ $message }}">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="att_id" class="form-label">Absensi ID</label>
                        <input type="text" id="att_id" name="att_id" class="form-control" value="{{ old('att_id') }}">
                    </div>
                    <div class="mb-3">
                        <label for="grade" class="form-label">Golongan</label>
                        <input type="text" id="grade" name="grade" class="form-control" value="{{ old('grade') }}">
                    </div>
                    <div class="mb-3">
                        <label for="license_no" class="form-label">SK Pemerintah</label>
                        <input type="text" id="license_no" name="license_no" class="form-control" value="{{ old('license_no') }}">
                    </div>
                    <div class="mb-3">
                        <label for="foundation_license_no" class="form-label">SK Yayasan</label>
                        <input type="text" id="foundation_license_no" name="foundation_license_no" class="form-control" value="{{ old('foundation_license_no') }}">
                    </div>
                    <div class="mb-3">
                        <label for="career_objective" class="form-label">Karier Objektif</label>
                        <textarea id="career_objective" name="career_objective" class="form-control" rows="3">{{ old('career_objective') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="skill" class="form-label">Keahlian</label>
                        <textarea id="skill" name="skill" class="form-control" rows="3">{{ old('skill') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Referensi</label>
                        <textarea id="reference" name="reference" class="form-control" rows="3">{{ old('reference') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea id="note" name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" id="save-button" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Simpan Data Pendidik
                </button>
                <a href="{{ route('teacher.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script validasi (hanya cek NIK) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saveButton = document.getElementById('save-button');
        const nikInput = document.getElementById('nik'); 

        // Fungsi untuk menyimpan error server awal
        function storeServerErrors() {
            document.querySelectorAll('input.is-invalid, select.is-invalid').forEach(function(input) {
                let errorFeedback = input.closest('.mb-3').querySelector('.invalid-feedback');
                if (errorFeedback && errorFeedback.textContent) {
                    errorFeedback.setAttribute('data-server-error', errorFeedback.textContent);
                }
            });
        }

        function checkFormValidity() {
            const invalidInputs = document.querySelectorAll('#teacher-form .is-invalid');
            saveButton.disabled = invalidInputs.length > 0;
        }

        function showError(input, message) {
            input.classList.add('is-invalid');
            let errorFeedback = input.closest('.mb-3').querySelector('.invalid-feedback');
            if (errorFeedback) {
                errorFeedback.textContent = message;
            }
            checkFormValidity();
        }

        function clearError(input) {
            input.classList.remove('is-invalid');
            let errorFeedback = input.closest('.mb-3').querySelector('.invalid-feedback');
            
            if (errorFeedback) {
                let serverErrorMessage = errorFeedback.getAttribute('data-server-error');
                errorFeedback.textContent = serverErrorMessage || ''; 
            }
            checkFormValidity();
        }
        
        storeServerErrors(); 

        if (nikInput) {
            nikInput.addEventListener('input', function () {
                let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                let serverError = errorFeedback ? errorFeedback.getAttribute('data-server-error') : null;

                if (/\s/.test(this.value)) {
                    showError(this, 'NIP tidak boleh mengandung spasi.');
                } else {
                    if (this.classList.contains('is-invalid') && (!serverError || errorFeedback.textContent === 'NIP tidak boleh mengandung spasi.')) {
                        clearError(this);
                    }
                }
            });
        }

        // Hapus error server saat pengguna mulai mengetik ulang
        document.querySelectorAll('input, select, textarea').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    if (this.id === 'nik') {
                        let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                        if (errorFeedback && errorFeedback.textContent !== 'NIP tidak boleh mengandung spasi.') {
                            this.classList.remove('is-invalid');
                            errorFeedback.textContent = ''; 
                            errorFeedback.removeAttribute('data-server-error'); 
                            checkFormValidity();
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                        if(errorFeedback) {
                            errorFeedback.textContent = '';
                            errorFeedback.removeAttribute('data-server-error');
                        }
                        checkFormValidity();
                    }
                }
            });
        });

        checkFormValidity();
    });
</script>
@endpush