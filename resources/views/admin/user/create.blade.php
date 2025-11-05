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
        {{-- Menampilkan error validasi jika ada --}}
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

        <form action="{{ route('admin.user.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Kolom Kiri: Personal Details --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Personal Details</h5>
                    
                    {{-- (Semua kolom personal dari form Anda sebelumnya) --}}
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname') }}" required>
                        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

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
                        <label for="mobile_phone" class="form-label">Mobile Phone</label>
                        <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" value="{{ old('mobile_phone') }}">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    {{-- (Tambahkan kolom personal lain di sini jika perlu) --}}
                </div>

                {{-- Kolom Kanan: Account --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary border-bottom pb-2">Account</h5>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" required>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', 'yes') == 'yes' ? 'selected' : '' }}>Active</option>
                            <option value="no" {{ old('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- [TAMBAHAN BARU] Pilihan Group --}}
                    <div class="mb-3">
                        <label for="group_ids" class="form-label">Groups *</label>
                        <select id="group_ids" name="group_ids[]" class="form-select @error('group_ids') is-invalid @enderror" multiple size="5" required>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" {{ (is_array(old('group_ids')) && in_array($group->id, old('group_ids'))) ? 'selected' : '' }}>
                                    {{ $group->group_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu.</small>
                        @error('group_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- [TAMBAHAN BARU] Pilihan Level --}}
                    <div class="mb-3">
                        <label for="level_ids" class="form-label">Levels *</label>
                        <select id="level_ids" name="level_ids[]" class="form-select @error('level_ids') is-invalid @enderror" multiple size="4" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}" {{ (is_array(old('level_ids')) && in_array($level->id, old('level_ids'))) ? 'selected' : '' }}>
                                    {{ $level->title }} (Grade: {{ $level->grade }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu.</small>
                        @error('level_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- (Tambahkan kolom akun lain di sini jika perlu: is_admin, user_type, dll.) --}}
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