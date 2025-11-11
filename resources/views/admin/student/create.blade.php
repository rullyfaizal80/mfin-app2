@extends('layouts.app')

@section('title', 'Siswa Baru | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Siswa Baru</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('student.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Siswa
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

        <form action="{{ route('student.store') }}" method="POST" id="student-form">
            @csrf
            
            <div class="row">
                {{-- Kolom Kiri: Data Pribadi & Data Kesehatan --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Pribadi</h5>
                    {{-- ... (Field Data Pribadi: Nama, Alamat, Email, Agama, Aktif, dll. tetap sama) ... --}}
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

                    {{-- Data Kesehatan --}}
                    <h5 class="mt-4 mb-3 text-primary border-bottom pb-2">Data Kesehatan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="height" class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" id="height" name="height" class="form-control" value="{{ old('height') }}">
                            </div>
                            <div class="mb-3">
                                <label for="cronic_desease" class="form-label">Penyakit Kronis</label>
                                <input type="text" id="cronic_desease" name="cronic_desease" class="form-control" value="{{ old('cronic_desease') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="weight" class="form-label">Berat Badan (kg)</label>
                                <input type="number" id="weight" name="weight" class="form-control" value="{{ old('weight') }}">
                            </div>
                            <div class="mb-3">
                                <label for="severe_desease" class="form-label">Penyakit Berat yg Pernah Diderita</label>
                                <textarea id="severe_desease" name="severe_desease" class="form-control">{{ old('severe_desease') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Data Lainnya --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Data Lainnya</h5>
                    {{-- ... (Field NIS, NISN, Ortu, dan data lainnya tetap sama) ... --}}
                    <div class="mb-3">
                        <label for="nis" class="form-label">NIS *</label>
                        <input type="text" id="nis" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis') }}" required>
                        @error('nis') <div class="invalid-feedback" data-server-error="{{ $message }}">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nin" class="form-label">NIS Nasional</label>
                        <input type="text" id="nin" name="nin" class="form-control @error('nin') is-invalid @enderror" value="{{ old('nin') }}">
                        @error('nin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="father_id" class="form-label">Nama Ayah</label>
                        <select id="father_id" name="father_id" class="form-select @error('father_id') is-invalid @enderror">
                            <option value="">-- Pilih Ayah --</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('father_id') == $parent->id ? 'selected' : '' }}>{{ $parent->fullname }}</option>
                            @endforeach
                        </select>
                        @error('father_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="mother_id" class="form-label">Nama Ibu</label>
                        <select id="mother_id" name="mother_id" class="form-select @error('mother_id') is-invalid @enderror">
                            <option value="">-- Pilih Ibu --</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('mother_id') == $parent->id ? 'selected' : '' }}>{{ $parent->fullname }}</option>
                            @endforeach
                        </select>
                        @error('mother_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Nama Wali</label>
                        <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">-- Pilih Wali --</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->fullname }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="parent_relation" class="form-label">Hubungan Wali</label>
                        <input type="text" id="parent_relation" name="parent_relation" class="form-control" value="{{ old('parent_relation') }}">
                    </div>
                    <div class="mb-3">
                        <label for="live_with" class="form-label">Tinggal Dengan</label>
                        <input type="text" id="live_with" name="live_with" class="form-control" value="{{ old('live_with') }}">
                    </div>
                    <div class="mb-3">
                        <label for="mothertongue" class="form-label">Bahasa Ibu</label>
                        <input type="text" id="mothertongue" name="mothertongue" class="form-control" value="{{ old('mothertongue') }}">
                    </div>
                    <div class="mb-3">
                        <label for="birthorder" class="form-label">Anak Ke-</label>
                        <input type="number" id="birthorder" name="birthorder" class="form-control" value="{{ old('birthorder') }}">
                    </div>
                    <div class="mb-3">
                        <label for="total_sibling" class="form-label">Jml Saudara Kandung</label>
                        <input type="number" id="total_sibling" name="total_sibling" class="form-control" value="{{ old('total_sibling') }}">
                    </div>
                    <div class="mb-3">
                        <label for="total_stepbrother" class="form-label">Jml Saudara Tiri</label>
                        <input type="number" id="total_stepbrother" name="total_stepbrother" class="form-control" value="{{ old('total_stepbrother') }}">
                    </div>
                    <div class="mb-3">
                        <label for="total_fosterbrother" class="form-label">Jml Saudara Angkat</label>
                        <input type="number" id="total_fosterbrother" name="total_fosterbrother" class="form-control" value="{{ old('total_fosterbrother') }}">
                    </div>
                    
                    {{-- [PERBAIKAN 1] "Jarak ke Sekolah" disesuaikan --}}
                    <div class="mb-3">
                        <label class="form-label d-block">Jarak ke Sekolah</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="distancetoschool1" id="dist1" value="<1 km" checked>
                            <label class="form-check-label" for="dist1">&lt;1 km</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="distancetoschool1" id="dist2" value="1-5 km">
                            <label class="form-check-label" for="dist2">1-5 km</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="distancetoschool1" id="dist3" value="> 5km">
                            <label class="form-check-label" for="dist3">&gt;5 km</label>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="form-check" style="flex-shrink: 0;">
                                <input class="form-check-input" type="radio" name="distancetoschool1" id="dist_other" value="other">
                                <label class="form-check-label" for="dist_other">Lainnya:</label>
                            </div>
                            <input type="text" name="distancetoschool2" class="form-control form-control-sm ms-2" style="max-width: 200px;">
                        </div>
                    </div>
                    
                    {{-- [PERBAIKAN 2] "Pergi ke sekolah" disesuaikan --}}
                    <div class="mb-3">
                        <label class="form-label d-block">Pergi ke sekolah</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gotoschool_with1" id="goto1" value="antar jemput orang tua" checked>
                            <label class="form-check-label" for="goto1">antar jemput orang tua</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gotoschool_with1" id="goto2" value="antar jemput langganan">
                            <label class="form-check-label" for="goto2">antar jemput langganan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gotoschool_with1" id="goto3" value="sendiri">
                            <label class="form-check-label" for="goto3">sendiri</label>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="form-check" style="flex-shrink: 0;">
                                <input class="form-check-input" type="radio" name="gotoschool_with1" id="goto_other" value="other">
                                <label class="form-check-label" for="goto_other">Lainnya:</label>
                            </div>
                            <input type="text" name="gotoschool_with2" class="form-control form-control-sm ms-2" style="max-width: 200px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" id="save-button" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Simpan Data Siswa
                </button>
                <a href="{{ route('student.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script validasi (hanya cek NIS) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saveButton = document.getElementById('save-button');
        const nisInput = document.getElementById('nis'); 

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
            const invalidInputs = document.querySelectorAll('#student-form .is-invalid');
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
                // Kembalikan ke error server jika ada, jika tidak, kosongkan
                errorFeedback.textContent = serverErrorMessage || ''; 
            }
            checkFormValidity();
        }
        
        storeServerErrors(); // Simpan error server saat halaman dimuat

        if (nisInput) {
            nisInput.addEventListener('input', function () {
                let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                let serverError = errorFeedback ? errorFeedback.getAttribute('data-server-error') : null;

                if (/\s/.test(this.value)) {
                    showError(this, 'NIS tidak boleh mengandung spasi.');
                } else {
                    // Jika tidak ada spasi, hapus error "spasi"
                    // tapi biarkan error server (jika ada) tetap tampil
                    if (this.classList.contains('is-invalid') && (!serverError || errorFeedback.textContent === 'NIS tidak boleh mengandung spasi.')) {
                        clearError(this);
                    }
                }
            });
        }

        // Hapus error server saat pengguna mulai mengetik ulang
        document.querySelectorAll('input, select').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    // Khusus untuk NIS, hanya hapus error jika BUKAN error "spasi"
                    if (this.id === 'nis') {
                        let errorFeedback = this.closest('.mb-3').querySelector('.invalid-feedback');
                        if (errorFeedback && errorFeedback.textContent !== 'NIS tidak boleh mengandung spasi.') {
                            this.classList.remove('is-invalid');
                            errorFeedback.textContent = ''; // Hapus pesan error server
                            errorFeedback.removeAttribute('data-server-error'); // Hapus cache error
                            checkFormValidity();
                        }
                    } else {
                        // Untuk input lain, langsung hapus
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