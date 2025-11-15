@extends('layouts.app')

@section('title', 'Edit Pendidik | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Data Pendidik: <strong class="text-primary">{{ $teacher->fullname }}</strong></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Pendidik
        </a>
    </div>
</div>

{{-- Navigasi Tab --}}
<ul class="nav nav-tabs" id="teacherTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab" aria-controls="detail" aria-selected="true">
            Personal Detail
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('teacher.editCv', ['id' => $teacher->user_id]) }}">
            Personal CV
        </a>
    </li>
</ul>

<div class="tab-content" id="teacherTabContent">
    {{-- Tab 1: Personal Detail --}}
    <div class="tab-pane fade show active" id="detail" role="tabpanel" aria-labelledby="detail-tab">
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
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('teacher.update', $teacher->user_id) }}" method="POST" id="teacher-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row pt-3">
                        {{-- Kolom Kiri: Data Pribadi --}}
                        <div class="col-md-6">
                            <h5 class="mb-3 text-primary border-bottom pb-2">Data Pribadi</h5>
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Nama Lengkap *</label>
                                <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname', $teacher->fullname) }}" required>
                                @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nickname" class="form-label">Nama Panggilan</label>
                                <input type="text" id="nickname" name="nickname" class="form-control" value="{{ old('nickname', $teacher->nickname) }}">
                            </div>
                            <div class="mb-3">
                                <label for="placeofbirth" class="form-label">Tempat Lahir</label>
                                <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" value="{{ old('placeofbirth', $teacher->placeofbirth) }}">
                            </div>
                            <div class="mb-3">
                                <label for="dateofbirth" class="form-label">Tanggal Lahir</label>
                                <input type="date" id="dateofbirth" name="dateofbirth" class="form-control" value="{{ old('dateofbirth', $teacher->dateofbirth) }}">
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Jenis Kelamin</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="M" {{ old('gender', $teacher->gender) == 'M' ? 'selected' : '' }}>Laki-Laki</option>
                                    <option value="F" {{ old('gender', $teacher->gender) == 'F' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="street" class="form-label">Alamat (Jalan)</label>
                                <textarea id="street" name="street" class="form-control">{{ old('street', $teacher->street) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $teacher->city) }}">
                            </div>
                            <div class="mb-3">
                                <label for="province" class="form-label">Provinsi</label>
                                <input type="text" id="province" name="province" class="form-control" value="{{ old('province', $teacher->province) }}">
                            </div>
                            <div class="mb-3">
                                <label for="country" class="form-label">Negara</label>
                                <input type="text" id="country" name="country" class="form-control" value="{{ old('country', $teacher->country) }}">
                            </div>
                            <div class="mb-3">
                                <label for="postalcode" class="form-label">Kode Pos</label>
                                <input type="text" id="postalcode" name="postalcode" class="form-control" value="{{ old('postalcode', $teacher->postalcode) }}">
                            </div>
                            <div class="mb-3">
                                <label for="home_phone" class="form-label">Telepon Rumah</label>
                                <input type="text" id="home_phone" name="home_phone" class="form-control" value="{{ old('home_phone', $teacher->home_phone) }}">
                            </div>
                            <div class="mb-3">
                                <label for="mobile_phone" class="form-label">HP</label>
                                <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" value="{{ old('mobile_phone', $teacher->mobile_phone) }}">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $teacher->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="religion" class="form-label">Agama</label>
                                <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion', $teacher->religion) }}">
                            </div>
                            <div class="mb-3">
                                <label for="bank_acc" class="form-label">No. Rek Bank</label>
                                <input type="text" id="bank_acc" name="bank_acc" class="form-control" value="{{ old('bank_acc', $teacher->bank_acc ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Aktif</label>
                                <select id="is_active" name="is_active" class="form-select">
                                    <option value="yes" {{ old('is_active', $teacher->is_active) == 'yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="no" {{ old('is_active', $teacher->is_active) == 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="is_parent" class="form-label">Orang Tua (Yes/No)</label>
                                <select id="is_parent" name="is_parent" class="form-select">
                                    <option value="no" {{ old('is_parent', $teacher->is_parent ?? 'no') == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('is_parent', $teacher->is_parent ?? 'no') == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Data Lainnya (Kepegawaian) --}}
                        <div class="col-md-6">
                            <h5 class="mb-3 text-primary border-bottom pb-2">Data Kepegawaian</h5>
                            
                            {{-- [PERBAIKAN] Layout 1 kolom urut ke bawah --}}
                            <div class="mb-3">
                                <label for="emp_status" class="form-label">Status</label>
                                <select id="emp_status" name="emp_status" class="form-select">
                                    <option value="permanent" {{ old('emp_status', $teacher->emp_status) == 'permanent' ? 'selected' : '' }}>Permanen</option>
                                    <option value="probation" {{ old('emp_status', $teacher->emp_status) == 'probation' ? 'selected' : '' }}>Percobaan</option>
                                    <option value="contract" {{ old('emp_status', $teacher->emp_status) == 'contract' ? 'selected' : '' }}>Kontrak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIP *</label>
                                <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $teacher->nik) }}" required>
                                @error('nik') <div class="invalid-feedback" data-server-error="{{ $message }}">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="att_id" class="form-label">Absensi ID</label>
                                <input type="text" id="att_id" name="att_id" class="form-control" value="{{ old('att_id', $teacher->att_id) }}">
                            </div>
                            <div class="mb-3">
                                <label for="grade" class="form-label">Golongan</label>
                                <input type="text" id="grade" name="grade" class="form-control" value="{{ old('grade', $teacher->grade) }}">
                            </div>
                            <div class="mb-3">
                                <label for="license_no" class="form-label">SK Pemerintah</label>
                                <input type="text" id="license_no" name="license_no" class="form-control" value="{{ old('license_no', $teacher->license_no) }}">
                            </div>
                            <div class="mb-3">
                                <label for="foundation_license_no" class="form-label">SK Yayasan</label>
                                <input type="text" id="foundation_license_no" name="foundation_license_no" class="form-control" value="{{ old('foundation_license_no', $teacher->foundation_license_no) }}">
                            </div>
                            <div class="mb-3">
                                <label for="career_objective" class="form-label">Karier Objektif</label>
                                <textarea id="career_objective" name="career_objective" class="form-control" rows="3">{{ old('career_objective', $teacher->career_objective) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="skill" class="form-label">Keahlian</label>
                                <textarea id="skill" name="skill" class="form-control" rows="3">{{ old('skill', $teacher->skill) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="reference" class="form-label">Referensi</label>
                                <textarea id="reference" name="reference" class="form-control" rows="3">{{ old('reference', $teacher->reference) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea id="note" name="note" class="form-control" rows="3">{{ old('note', $teacher->note) }}</textarea>
                            </div>
                            {{-- Field 'training' dan 'organization' (ringkasan) dihapus --}}

                            {{-- Form Upload KTP --}}
                            <hr>
                            <h5 class="mb-3 text-primary border-bottom pb-2">Personal Identity (KTP)</h5>
                            <div class="mb-3">
                                <label for="ktp_upload" class="form-label">Upload KTP Baru (Opsional)</label>
                                <input class="form-control @error('ktp_upload') is-invalid @enderror" type="file" id="ktp_upload" name="ktp_upload">
                                <small class="text-muted">Max: 1MB. Format: JPG, PNG.</small>
                                @error('ktp_upload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                           {{-- [PERBAIKAN FINAL] --}}
@if($teacher->personal_identity)
<div class="mb-3">
    <label class="form-label">KTP Saat Ini:</label>
    <div>
        {{-- Langsung gunakan nilai dari DB, karena sudah benar (contoh: personal_identity2965.jpg) --}}
        <img src="{{ asset('uploads/teachers/' . $teacher->personal_identity) }}" alt="KTP" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
    </div>
    <small class="text-muted">{{ $teacher->personal_identity }}</small>
</div>
@endif
                            

                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" id="save-button" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Perbarui Data Pendidik
                        </button>
                        <a href="{{ route('teacher.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script validasi (tidak ada validasi NIK unik) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ... (seluruh script validasi Anda yang sudah ada tetap di sini) ...
    });
</script>
@endpush