@extends('layouts.app')

@section('title', 'Edit User | MFIN')

@section('content')
{{-- ... (bagian atas halaman tetap sama) ... --}}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit User: <strong class="text-primary">{{ $user->fullname }}</strong></h1>
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

        <form action="{{ route('admin.user.update', $user->id) }}" method="POST" id="user-form">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Kolom Kiri: Personal Details --}}
                <div class="col-md-6">
                    {{-- ... (Semua field personal details Anda di sini) ... --}}
                    <h5 class="mb-3 text-primary border-bottom pb-2">Personal Details</h5>

                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname', $user->fullname) }}" required>
                        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nickname</label>
                        <input type="text" id="nickname" name="nickname" class="form-control" value="{{ old('nickname', $user->nickname) }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="placeofbirth" class="form-label">Place of Birth</label>
                            <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" value="{{ old('placeofbirth', $user->placeofbirth) }}">
                        </div>
                        <div class="col">
                            <label for="dateofbirth" class="form-label">Date of Birth</label>
                            <input type="date" id="dateofbirth" name="dateofbirth" class="form-control" value="{{ old('dateofbirth', $user->dateofbirth) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="M" {{ old('gender', $user->gender) == 'M' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="F" {{ old('gender', $user->gender) == 'F' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="street" class="form-label">Street</label>
                        <textarea id="street" name="street" class="form-control">{{ old('street', $user->street) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" id="province" name="province" class="form-control" value="{{ old('province', $user->province) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" id="country" name="country" class="form-control" value="{{ old('country', $user->country) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="postalcode" class="form-label">Postal Code</label>
                            <input type="text" id="postalcode" name="postalcode" class="form-control" value="{{ old('postalcode', $user->postalcode) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mobile_phone" class="form-label">Mobile Phone</label>
                        <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" value="{{ old('mobile_phone', $user->mobile_phone) }}">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="religion" class="form-label">Religion</label>
                        <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion', $user->religion) }}">
                    </div>
                </div>

                {{-- Kolom Kanan: Account --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Account</h5>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        <div class="invalid-feedback" data-js-message="Username tidak boleh mengandung spasi.">
                            @error('username') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback" data-js-message="Password minimal harus 6 karakter.">
                                @error('password') {{ $message }} @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback" data-js-message="Konfirmasi password tidak cocok."></div>
                        </div>
                    </div>
                    
                    {{-- ... (Sisa field Account lainnya: Active, Admin, Groups, Levels, Student, Parent, Teacher, Educator) ... --}}
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Active</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', $user->is_active) == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ old('is_active', $user->is_active) == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_admin" class="form-label">Admin</label>
                        <select id="is_admin" name="is_admin" class="form-select">
                            <option value="no" {{ old('is_admin', $user->is_admin) == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_admin', $user->is_admin) == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="group_ids" class="form-label">Groups *</label>
                        <select id="group_ids" name="group_ids[]" class="form-select @error('group_ids') is-invalid @enderror" multiple size="5" required>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" {{ (in_array($group->id, old('group_ids', $selectedGroups))) ? 'selected' : '' }}>
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
                                <option value="{{ $level->id }}" {{ (in_array($level->id, old('level_ids', $selectedLevels))) ? 'selected' : '' }}>
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
                            <option value="no" {{ old('is_student', $user->is_student) == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_student', $user->is_student) == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_parent" class="form-label">Parent</label>
                        <select id="is_parent" name="is_parent" class="form-select">
                            <option value="no" {{ old('is_parent', $user->is_parent) == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_parent', $user->is_parent) == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_teacher" class="form-label">Teacher</label>
                        <select id="is_teacher" name="is_teacher" class="form-select">
                            <option value="no" {{ old('is_teacher', $user->is_teacher) == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_teacher', $user->is_teacher) == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="is_educator" class="form-label">Educator</label>
                        <select id="is_educator" name="is_educator" class="form-select">
                            <option value="no" {{ old('is_educator', $user->is_educator) == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_educator', $user->is_educator) == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                {{-- [PERUBAHAN] Tambahkan ID pada tombol Update --}}
                <button type="submit" id="save-button" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Update
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saveButton = document.getElementById('save-button'); // Ambil tombol save

        // --- Fungsi Toggle Password (Tombol Mata) ---
        function setupToggle(toggleId, inputId) {
            const toggleButton = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            if (toggleButton && input) {
                toggleButton.addEventListener('click', function () {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
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
        
        // [BARU] Fungsi untuk mengecek semua validasi dan status tombol
        function checkFormValidity() {
            // Cek semua input yang punya class 'is-invalid' di dalam form
            const invalidInputs = document.querySelectorAll('#user-form .is-invalid');
            // Nonaktifkan tombol jika ada input yang tidak valid
            saveButton.disabled = invalidInputs.length > 0;
        }

        // Fungsi helper untuk menampilkan error
        function showError(input, message) {
            input.classList.add('is-invalid');
            let errorFeedback = input.closest('.mb-3, .input-group').querySelector('.invalid-feedback');
            if (errorFeedback) {
                errorFeedback.textContent = message;
            }
            checkFormValidity(); // [BARU] Cek status tombol
        }

        // Fungsi helper untuk membersihkan error
        function clearError(input) {
            input.classList.remove('is-invalid');
            let errorFeedback = input.closest('.mb-3, .input-group').querySelector('.invalid-feedback');
            if (errorFeedback && errorFeedback.hasAttribute('data-js-message')) {
                errorFeedback.textContent = errorFeedback.getAttribute('data-js-message'); 
            }
            checkFormValidity(); // [BARU] Cek status tombol
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

        // 2. Validasi Password (minimal 6 karakter) - Opsional di Edit
        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                if (this.value.length > 0 && this.value.length < 6) {
                    showError(this, 'Password minimal harus 6 karakter.');
                } else {
                    clearError(this);
                }
                validatePasswordConfirm(); 
            });
        }

        // 3. Validasi Konfirmasi Password (harus cocok)
        function validatePasswordConfirm() {
            if (passwordInput.value.length > 0) {
                if (passwordConfirmInput.value !== passwordInput.value) {
                    showError(passwordConfirmInput, 'Konfirmasi password tidak cocok.');
                } else {
                    clearError(passwordConfirmInput);
                }
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
                if(input.id !== 'username' && input.id !== 'password' && input.id !== 'password_confirmation') {
                   input.classList.remove('is-invalid');
                   checkFormValidity();
                }
            });
        });

        // [BARU] Cek validitas form saat halaman pertama kali dimuat
        checkFormValidity();
    });
</script>
@endpush