@extends('layouts.app')

@section('title', 'Siswa Kelas | MFIN')

@push('styles')
    {{-- Tambahkan CSS untuk Select2 jika belum ada --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Siswa Kelas</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('class_list.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Kelas
            </a>
        </div>
    </div>

    {{-- Container untuk Notifikasi --}}
    <div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi Kesalahan!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    {{-- Detail Info Kelas --}}
    <div class="card mb-4">
        <div class="card-header bg-body-tertiary fs-5 fw-bold">
            {{ $class_info->class_title }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Sekolah</dt>
                        <dd class="col-sm-8">{{ $class_info->school_name ?? '-' }}</dd>
                        
                        <dt class="col-sm-4">Tahun Ajaran</dt>
                        <dd class="col-sm-8">{{ $class_info->year_title ?? '-' }}</dd>

                        <dt class="col-sm-4">Jurusan</dt>
                        <dd class="col-sm-8">{{ $class_info->subject_title ?? '-' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Tingkat / Grup</dt>
                        <dd class="col-sm-8">{{ $class_info->grade_title ?? '-' }} / {{ $class_info->group_title ?? '-' }}</dd>

                        <dt class="col-sm-4">Tipe</dt>
                        <dd class="col-sm-8">{{ $class_info->type_title ?? '-' }}</dd>
                        
                        <dt class="col-sm-4">Wali Kelas 1</dt>
                        <dd class="col-sm-8">{{ $class_info->parent1_name ?? '-' }}</dd>

                        <dt class="col-sm-4">Wali Kelas 2</dt>
                        <dd class="col-sm-8">{{ $class_info->parent2_name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Form Tambah / Edit Siswa --}}
    <div class="card mb-4">
        <div class="card-header">
            @if ($edit_data->id)
                Edit Siswa: <strong>{{ $edit_data->fullname }}</strong>
            @else
                <i class="bi bi-person-plus-fill"></i> Tambah Siswa Baru ke Kelas
            @endif
        </div>
        <div class="card-body">
            @if ($edit_data->id)
                <form action="{{ route('class_user.update', ['class_list_id' => $class_list_id, 'class_user_id' => $edit_data->id]) }}" method="POST">
                @method('PUT')
            @else
                <form action="{{ route('class_user.store', $class_list_id) }}" method="POST">
            @endif
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="user_id" class="form-label">Nama Siswa *</label>
                        @if ($edit_data->id)
                            <input type="text" class="form-control" value="{{ $edit_data->fullname }}" readonly disabled>
                        @else
                            {{-- Select2 akan mengisi ini melalui AJAX. Hapus @foreach --}}
                            <select id="user_id" name="user_id" class="form-select" required>
                                <option value="">- Cari Nama atau NIS Siswa -</option>
                            </select>
                        @endif
                    </div>
                    <div class="col-md-2">
                         <label for="join_start" class="form-label">Tgl Masuk *</label>
                         <input type="date" class="form-control" id="join_start" name="join_start" value="{{ old('join_start', $edit_data->join_start) }}" required>
                    </div>
                    <div class="col-md-2">
                         <label for="join_end" class="form-label">Tgl Keluar *</label>
                         <input type="date" class="form-control" id="join_end" name="join_end" value="{{ old('join_end', $edit_data->join_end) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="is_active" class="form-label">Status *</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="yes" {{ old('is_active', $edit_data->is_active) == 'yes' ? 'selected' : '' }}>Aktif</option>
                            <option value="no" {{ old('is_active', $edit_data->is_active) == 'no' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        @if ($edit_data->id)
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i></button>
                        @else
                             <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-lg"></i></button>
                        @endif
                    </div>
                </div>
            </form>
            @if ($edit_data->id)
                <div class="text-end mt-2">
                    <a href="{{ route('class_user.index', $class_list_id) }}">Batal edit</a>
                </div>
            @endif
        </div>
    </div>


    {{-- Card untuk List Data & Copy Siswa --}}
    <form action="{{ route('class_user.copy', $class_list_id) }}" method="POST">
    @csrf
        <div class="card">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <form id="search-form" action="{{ route('class_user.index', $class_list_id) }}" method="GET">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="search-input" class="form-control" name="search" placeholder="Cari Siswa di Kelas Ini...">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                </form>

                {{-- Fitur Copy Siswa --}}
                <div class="d-flex">
                    <select id="class_copy_id" name="class_copy_id" class="form-select form-select-sm" style="width: 250px;" required>
                        <option value="">- Salin Siswa Terpilih ke...</option>
                        @foreach ($other_classes as $class)
                            <option value="{{ $class->id }}">{{ $class->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary ms-2" onclick="return confirm('Salin siswa yang dipilih ke kelas baru?')">
                        <i class="bi bi-clipboard-plus"></i> Salin
                    </button>
                </div>
            </div>
            
            <div id="table-container">
                {{-- Tabel akan dimuat oleh AJAX/Controller --}}
                @include('admin.class_user._student_table', ['list_data' => $list_data])
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    {{-- Script untuk Select2 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Aktifkan Select2 dengan AJAX
            $('#user_id').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Cari Nama atau NIS Siswa...",
                minimumInputLength: 3, // Mulai mencari setelah 3 karakter
                ajax: {
                    url: "{{ route('class_user.ajax_search') }}",
                    dataType: 'json',
                    delay: 250, // Jeda setelah mengetik
                    data: function (params) {
                        return {
                            term: params.term, // Kata kunci pencarian
                            class_list_id: "{{ $class_list_id }}" // Kirim ID kelas untuk filter
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
    
    {{-- Script untuk live search/pagination --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableContainer = document.getElementById('table-container');
            const searchInput = document.getElementById('search-input');
            const baseUrl = "{{ route('class_user.index', $class_list_id) }}";
            let debounceTimer;

            function fetchTableData(url) {
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    window.history.pushState({ path: url }, '', url);
                })
                .catch(error => console.error('Error fetching table data:', error));
            }

            searchInput.addEventListener('keyup', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    const params = new URLSearchParams();
                    params.append('search', searchInput.value);
                    const url = `${baseUrl}?${params.toString()}`;
                    fetchTableData(url);
                }, 500);
            });
            
            tableContainer.addEventListener('click', function(event) {
                if (event.target.tagName === 'A' && event.target.closest('.page-item')) {
                    event.preventDefault();
                    const url = event.target.href;
                    if (url) { 
                        fetchTableData(url); 
                    }
                }
            });

            // Checkbox 'select all'
            tableContainer.addEventListener('change', function(event) {
                if (event.target.id === 'select-all-students') {
                    const checkboxes = tableContainer.querySelectorAll('input[name="student_ids[]"]');
                    checkboxes.forEach(cb => {
                        cb.checked = event.target.checked;
                    });
                }
            });
        });
    </script>
@endpush