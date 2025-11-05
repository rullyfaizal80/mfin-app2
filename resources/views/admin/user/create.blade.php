@extends('layouts.app')

@section('title', 'Create New User | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back to User List
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

        <form action="{{ route('admin.user.store') }}" method="POST" id="user-form">
            @csrf
            <div class="row">
                {{-- Kolom Kiri: Personal Details --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Personal Details</h5>
                    
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname') }}" required>
                        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- ... (semua field personal details lainnya tetap sama) ... --}}
                    
                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nickname</label>
                        <input type="text" id="nickname" name="nickname" class="form-control" value="{{ old('nickname') }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="placeofbirth" class="form-label">Place of Birth</label>
                            <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" value="{{ old('placeofbirth') }}">
                        </div>
                        <div class="col">
                            <label for="dateofbirth" class="form-label">Date of Birth</label>
                            <input type="date" id="dateofbirth" name="dateofbirth" class="form-control" value="{{ old('dateofbirth') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="street" class="form-label">Street</label>
                        <textarea id="street" name="street" class="form-control">{{ old('street') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" id="province" name="province" class="form-control" value="{{ old('province') }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" id="country" name="country" class="form-control" value="{{ old('country') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="postalcode" class="form-label">Postal Code</label>
                            <input type="text" id="postalcode" name="postalcode" class="form-control" value="{{ old('postalcode') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mobile_phone" class="form-label">Mobile Phone</label>
                        <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" value="{{ old('mobile_phone') }}">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="religion" class="form-label">Religion</label>
                        <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion') }}">
                    </div>

                </div>

                {{-- Kolom Kanan: Account --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Account</h5>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required autocomplete="off">
                        {{-- Pesan error kustom untuk JS --}}
                        <div class="invalid-feedback" data-js-message="Username tidak boleh mengandung spasi.">
                            @error('username') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- [PERUBAHAN] Field Password dengan Tombol Mata --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback" data-js-message="Password minimal harus 6 karakter.">
                                @error('password') {{ $message }} @enderror
                            </div>
                        </div>
                    </div>

                    {{-- [PERUBAHAN] Field Konfirmasi Password dengan Tombol Mata --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback" data-js-message="Konfirmasi password tidak cocok."></div>
                        </div>
                    </div>
                    
                    {{-- ... (Sisa field Account lainnya tetap sama) ... --}}

                    <div class="mb-3">
                        <label for="is_active" class="form-label">Active</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', 'yes') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ old('is_active') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_admin" class="form-label">Admin</label>
                        <select id="is_admin" name="is_admin" class="form-select">
                            <option value="no" {{ old('is_admin', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_admin') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="group_ids" class="form-label">Groups *</label>
                        <select id="group_ids" name="group_ids[]" class="form-select @error('group_ids') is-invalid @enderror" multiple size="5" required>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" {{ (is_array(old('group_ids')) && in_array($group->id, old('group_ids'))) ? 'selected' : '' }}>
                                    {{ $group->group_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahan Ctrl (Cmd) untuk memilih lebih dari satu.</small>
                        @error('group_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="level_ids" class="form-label">Levels *</label>
                        <select id="level_ids" name="level_ids[]" class="form-select @error('level_ids') is-invalid @enderror" multiple size="4" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}" {{ (is_array(old('level_ids')) && in_array($level->id, old('level_ids'))) ? 'selected' : '' }}>
                                    {{ $level->title }} (Grade: {{ $level->grade }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahan Ctrl (Cmd) untuk memilih lebih dari satu.</small>
                        @error('level_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="is_student" class="form-label">Student</label>
                        <select id="is_student" name="is_student" class="form-select">
                            <option value="no" {{ old('is_student', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_student') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_parent" class="form-label">Parent</label>
                        <select id="is_parent" name="is_parent" class="form-select">
                            <option value="no" {{ old('is_parent', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_parent') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_teacher" class="form-label">Teacher</label>
                        <select id="is_teacher" name="is_teacher" class="form-select">
                            <option value="no" {{ old('is_teacher', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_teacher') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_educator" class="form-label">Educator</label>
                        <select id="is_educator" name="is_educator" class="form-select">
                            <option value="no" {{ old('is_educator', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_educator') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Save
                </button>
                <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- [TAMBAHAN BARU] JavaScript untuk validasi & tombol mata --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Fungsi Toggle Password (Tombol Mata) ---
        function setupToggle(toggleId, inputId) {
            const toggleButton = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            if (toggleButton && input) {
                toggleButton.addEventListener('click', function () {
                    // Ganti tipe input
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Ganti ikon mata
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                });
            }
        }
        
        setupToggle('togglePassword', 'password');
        setupToggle('togglePasswordConfirm', 'password_confirmation');

        // --- Fungsi Validasi Real-time ---
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');

        // Fungsi helper untuk menampilkan error
        function showError(input, message) {
            input.classList.add('is-invalid');
            let errorFeedback = input.closest('.mb-3, .input-group').querySelector('.invalid-feedback');
            if (errorFeedback) {
                errorFeedback.textContent = message;
            }
        }

        // Fungsi helper untuk membersihkan error
        function clearError(input) {
            input.classList.remove('is-invalid');
            let errorFeedback = input.closest('.mb-3, .input-group').querySelector('.invalid-feedback');
            if (errorFeedback && errorFeedback.hasAttribute('data-js-message')) {
                // Reset ke pesan error server jika ada, atau pesan JS default
                errorFeedback.textContent = errorFeedback.getAttribute('data-js-message'); 
            }
        }
        
        // 1. Validasi Username (tidak boleh spasi)
        if (usernameInput) {
            usernameInput.addEventListener('input', function () {
                if (/\s/.test(this.value)) {
                    showError(this, 'Username tidak boleh mengandung spasi.');
                } else {
                    clearError(this);
                }
            });
        }

        // 2. Validasi Password (minimal 6 karakter)
        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                if (this.value.length > 0 && this.value.length < 6) {
                    showError(this, 'Password minimal harus 6 karakter.');
                } else {
                    clearError(this);
                }
                // Cek ulang konfirmasi jika password utama diubah
                validatePasswordConfirm(); 
            });
        }

        // 3. Validasi Konfirmasi Password (harus cocok)
        function validatePasswordConfirm() {
            if (passwordConfirmInput.value.length > 0 && passwordInput.value !== passwordConfirmInput.value) {
                showError(passwordConfirmInput, 'Konfirmasi password tidak cocok.');
            } else {
                clearError(passwordConfirmInput);
            }
        }

        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', validatePasswordConfirm);
        }

        // 4. Hapus error server saat pengguna mulai mengetik
        document.querySelectorAll('input.is-invalid, select.is-invalid').forEach(function(input) {
            input.addEventListener('input', function() {
                input.classList.remove('is-invalid');
            });
        });
    });
</script>
@endpush