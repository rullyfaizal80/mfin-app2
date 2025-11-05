@extends('layouts.app')

@section('title', 'Edit User | MFIN')

@section('content')
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

        <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Kolom Kiri: Personal Details --}}
                <div class="col-md-6">
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
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', $user->is_active) == 'yes' ? 'selected' : '' }}>Active</option>
                            <option value="no" {{ old('is_active', $user->is_active) == 'no' ? 'selected' : '' }}>Inactive</option>
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
                        <label for="user_type" class="form-label">User Type</label>
                        <select id="user_type" name="user_type" class="form-select">
                            <option value="personal_educator" {{ old('user_type', $user->user_type) == 'personal_educator' ? 'selected' : '' }}>Administrator</option>
                            <option value="finance" {{ old('user_type', $user->user_type) == 'finance' ? 'selected' : '' }}>Keuangan</option>
                            <option value="administration" {{ old('user_type', $user->user_type) == 'administration' ? 'selected' : '' }}>Tata Usaha</option>
                            <option value="cashier" {{ old('user_type', $user->user_type) == 'cashier' ? 'selected' : '' }}>Kasir</option>
                            <option value="kabid" {{ old('user_type', $user->user_type) == 'kabid' ? 'selected' : '' }}>Kabid</option>
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
                        @error('group_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        @error('level_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
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