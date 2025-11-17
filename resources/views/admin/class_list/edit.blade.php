@extends('layouts.app')

@section('title', 'Edit Kelas | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Kelas: <span class="text-primary">{{ $data->title }}</span></h1>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Terjadi Kesalahan!</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('class_list.update', $data->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Nama Kelas *</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $data->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="cschool_id" class="form-label">Sekolah</label>
                        <select id="cschool_id" name="cschool_id" class="form-select">
                            <option value="0">- Pilih Sekolah -</option>
                            @foreach($dropdowns['schools'] as $item)
                                <option value="{{ $item->id }}" {{ old('cschool_id', $data->cschool_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="cyear_id" class="form-label">Tahun Ajaran *</label>
                        <select id="cyear_id" name="cyear_id" class="form-select" required>
                            <option value="0">- Pilih Tahun Ajaran -</option>
                            @foreach($dropdowns['years'] as $item)
                                <option value="{{ $item->id }}" {{ old('cyear_id', $data->cyear_id) == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="csubject_id" class="form-label">Jurusan *</label>
                        <select id="csubject_id" name="csubject_id" class="form-select" required>
                            <option value="0">- Pilih Jurusan -</option>
                            @foreach($dropdowns['subjects'] as $item)
                                <option value="{{ $item->id }}" {{ old('csubject_id', $data->csubject_id) == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="cgrade_id" class="form-label">Tingkat</label>
                        <select id="cgrade_id" name="cgrade_id" class="form-select">
                            <option value="0">- Pilih Tingkat -</option>
                            @foreach($dropdowns['grades'] as $item)
                                <option value="{{ $item->id }}" {{ old('cgrade_id', $data->cgrade_id) == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cgroup_id" class="form-label">Grup</label>
                        <select id="cgroup_id" name="cgroup_id" class="form-select">
                            <option value="0">- Pilih Grup -</option>
                            @foreach($dropdowns['groups'] as $item)
                                <option value="{{ $item->id }}" {{ old('cgroup_id', $data->cgroup_id) == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="ctype_id" class="form-label">Tipe *</label>
                        <select id="ctype_id" name="ctype_id" class="form-select" required>
                            <option value="0">- Pilih Tipe -</option>
                            @foreach($dropdowns['types'] as $item)
                                <option value="{{ $item->id }}" {{ old('ctype_id', $data->ctype_id) == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="parent1_id" class="form-label">Wali Kelas 1</label>
                        <select id="parent1_id" name="parent1_id" class="form-select">
                            <option value="0">- Pilih Wali Kelas 1 -</option>
                             @foreach($dropdowns['teachers'] as $item)
                                <option value="{{ $item->id }}" {{ old('parent1_id', $data->parent1_id) == $item->id ? 'selected' : '' }}>{{ $item->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="parent2_id" class="form-label">Wali Kelas 2</label>
                        <select id="parent2_id" name="parent2_id" class="form-select">
                            <option value="0">- Pilih Wali Kelas 2 -</option>
                             @foreach($dropdowns['teachers'] as $item)
                                <option value="{{ $item->id }}" {{ old('parent2_id', $data->parent2_id) == $item->id ? 'selected' : '' }}>{{ $item->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <hr>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                        <a href="{{ route('class_list.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection