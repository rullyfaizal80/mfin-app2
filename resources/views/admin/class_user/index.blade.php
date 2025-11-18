@extends('layouts.app')

@section('title', 'Siswa Kelas | MFIN')

@push('styles')
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

    {{-- Container Notifikasi --}}
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

    {{-- HEADER: Info Kelas (Full Width) --}}
    <div class="card mb-4">
        <div class="card-header bg-body-tertiary fs-5 fw-bold">
            {{ $class_info->class_title }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Sekolah</dt><dd class="col-sm-8">{{ $class_info->school_name ?? '-' }}</dd>
                        <dt class="col-sm-4">Tahun Ajaran</dt><dd class="col-sm-8">{{ $class_info->year_title ?? '-' }}</dd>
                        <dt class="col-sm-4">Jurusan</dt><dd class="col-sm-8">{{ $class_info->subject_title ?? '-' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Tingkat/Grup</dt><dd class="col-sm-8">{{ $class_info->grade_title ?? '-' }} / {{ $class_info->group_title ?? '-' }}</dd>
                        <dt class="col-sm-4">Wali Kelas</dt><dd class="col-sm-8">{{ $class_info->parent1_name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        {{-- KOLOM KIRI (3/4): Daftar Siswa --}}
        <div class="col-lg-9 mb-4">
            <div class="card h-100">
                <div class="card-header bg-body-tertiary">
                    <form id="search-form" action="{{ route('class_user.index', $class_list_id) }}" method="GET">
                        <div class="d-flex justify-content-between">  
                            <div class="input-group input-group-sm" style="max-width: 300px;">
                                <input type="text" id="search-input" class="form-control" name="search" placeholder="Cari Siswa..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="me-2 small">Tampil:</span>
                                <select id="perPage" name="perPage" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>   
                        </div>
                    </form>
                </div>
                <div id="table-container">
                    @include('admin.class_user._student_table', ['list_data' => $list_data])
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (1/4): Form Tambah & Form Salin --}}
        <div class="col-lg-3">
            
            {{-- CARD 1: Tambah Siswa (Sederhana) --}}
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-person-plus"></i> Tambah Siswa
                </div>
                <div class="card-body">
                    @if ($edit_data->id)
                        <form action="{{ route('class_user.update', ['class_list_id' => $class_list_id, 'class_user_id' => $edit_data->id]) }}" method="POST">
                        @method('PUT')
                        
                        {{-- Mode Edit: Tampilkan Detail Lengkap --}}
                        <div class="mb-2">
                            <label class="form-label small">Nama Siswa</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $edit_data->fullname }}" readonly disabled>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Tgl Masuk</label>
                            <input type="date" class="form-control form-control-sm" name="join_start" value="{{ old('join_start', $edit_data->join_start) }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Tgl Keluar</label>
                            <input type="date" class="form-control form-control-sm" name="join_end" value="{{ old('join_end', $edit_data->join_end) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Status</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="yes" {{ old('is_active', $edit_data->is_active) == 'yes' ? 'selected' : '' }}>Aktif</option>
                                <option value="no" {{ old('is_active', $edit_data->is_active) == 'no' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                            <a href="{{ route('class_user.index', $class_list_id) }}" class="btn btn-outline-secondary btn-sm mt-1">Batal</a>
                        </div>

                    @else
                        {{-- Mode Tambah: Sederhana (Hanya Nama) --}}
                        <form action="{{ route('class_user.store', $class_list_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="user_id" class="form-label small">Cari & Pilih Siswa</label>
                                <select id="user_id" name="user_id" class="form-select" required></select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-sm">Tambahkan ke Kelas</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- CARD 2: Salin Siswa (Copy) --}}
            <form action="{{ route('class_user.copy', $class_list_id) }}" method="POST" id="copy-form">
                @csrf
                {{-- Input tersembunyi untuk menampung ID siswa yang dicentang --}}
                <div id="selected-students-container"></div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-files"></i> Salin Terpilih
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            Centang siswa di tabel kiri, lalu pilih tujuan salin di bawah.
                        </p>
                        
                        <div class="mb-2">
                            <label class="form-label small">1. Pilih Tahun Ajaran</label>
                            <select id="filter_year" class="form-select form-select-sm">
                                <option value="">- Pilih Tahun -</option>
                                @foreach ($all_years as $y)
                                    <option value="{{ $y->id }}">{{ $y->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">2. Pilih Kelas Tujuan</label>
                            <select id="class_copy_id" name="class_copy_id" class="form-select form-select-sm" disabled required>
                                <option value="">- Pilih Kelas -</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary btn-sm" id="btn-copy" disabled onclick="return confirm('Yakin ingin menyalin siswa yang dipilih?')">
                                <i class="bi bi-clipboard-plus"></i> Salin Siswa
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // 1. Select2 AJAX untuk Tambah Siswa
            $('#user_id').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Ketik Nama / NIS...",
                minimumInputLength: 3,
                ajax: {
                    url: "{{ route('class_user.ajax_search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term,
                            class_list_id: "{{ $class_list_id }}" // Exclude siswa yg sdh ada
                        };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    },
                    cache: true
                }
            });

            // 2. Logika AJAX untuk Dropdown Copy (Tahun -> Kelas)
            const filterYear = document.getElementById('filter_year');
            const classSelect = document.getElementById('class_copy_id');
            const copyBtn = document.getElementById('btn-copy');

            filterYear.addEventListener('change', function() {
                const yearId = this.value;
                classSelect.innerHTML = '<option value="">Loading...</option>';
                classSelect.disabled = true;
                copyBtn.disabled = true;

                if (yearId) {
                    fetch(`{{ route('class_user.ajax_get_classes') }}?cyear_id=${yearId}&current_class_id={{ $class_list_id }}`)
                        .then(response => response.json())
                        .then(data => {
                            classSelect.innerHTML = '<option value="">- Pilih Kelas Tujuan -</option>';
                            data.forEach(cls => {
                                classSelect.innerHTML += `<option value="${cls.id}">${cls.title}</option>`;
                            });
                            classSelect.disabled = false;
                        });
                } else {
                    classSelect.innerHTML = '<option value="">- Pilih Tahun Dulu -</option>';
                }
            });

            classSelect.addEventListener('change', function() {
                copyBtn.disabled = !this.value;
            });

            // 3. Sinkronisasi Checkbox Table ke Form Copy
            // Kita perlu memindahkan value checkbox dari Table (form terpisah/tanpa form) ke Form Copy
            const copyForm = document.getElementById('copy-form');
            const hiddenContainer = document.getElementById('selected-students-container');

            copyForm.addEventListener('submit', function(e) {
                hiddenContainer.innerHTML = ''; // Reset
                const checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
                
                if (checkboxes.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu siswa dari tabel untuk disalin.');
                    return;
                }

                checkboxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'student_ids[]';
                    input.value = cb.value;
                    hiddenContainer.appendChild(input);
                });
            });
        });
    </script>

    {{-- Script Search Table, Pagination & Per Page --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableContainer = document.getElementById('table-container');
            const searchInput = document.getElementById('search-input');
            const perPageSelect = document.getElementById('perPage'); // [BARU]
            const baseUrl = "{{ route('class_user.index', $class_list_id) }}";
            let debounceTimer;

            function fetchTableData(url) {
                // Pastikan parameter search dan perPage selalu terbawa jika url tidak lengkap
                const currentUrl = new URL(url, window.location.origin);
                if(searchInput.value) currentUrl.searchParams.set('search', searchInput.value);
                if(perPageSelect.value) currentUrl.searchParams.set('perPage', perPageSelect.value);

                fetch(currentUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    window.history.pushState({ path: currentUrl.toString() }, '', currentUrl.toString());
                });
            }

            // Event: Ketik di kotak pencarian
            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        fetchTableData(baseUrl); // Panggil ulang base URL dengan parameter terkini
                    }, 500);
                });
            }

            // [BARU] Event: Ganti jumlah baris (perPage)
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    fetchTableData(baseUrl);
                });
            }
            
            // Event: Klik Pagination
            tableContainer.addEventListener('click', function(event) {
                if (event.target.tagName === 'A' && event.target.closest('.page-item')) {
                    event.preventDefault();
                    fetchTableData(event.target.href); 
                }
            });

            // Select All Checkbox logic
            tableContainer.addEventListener('change', function(event) {
                if (event.target.id === 'select-all-students') {
                    const checkboxes = tableContainer.querySelectorAll('input[name="student_ids[]"]');
                    checkboxes.forEach(cb => cb.checked = event.target.checked);
                }
            });
        });
    </script>
@endpush