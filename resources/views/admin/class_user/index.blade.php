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
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        {{-- Bagian Kiri: Search Form & PerPage --}}
                        <form id="search-form" action="{{ route('class_user.index', $class_list_id) }}" method="GET" class="d-flex gap-2 align-items-center flex-grow-1">
                            <div class="d-flex align-items-center">
                                <span class="me-2 small text-nowrap">Tampil:</span>
                                <select id="perPage" name="perPage" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>

                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <input type="text" id="search-input" class="form-control" name="search" placeholder="Cari Siswa..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        {{-- Bagian Kanan: Tombol Export --}}
                        <div>
                            <a href="{{ route('class_user.export', $class_list_id) }}" class="btn btn-sm btn-success text-white" target="_blank">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div id="table-container">
                    @include('admin.class_user._student_table', ['list_data' => $list_data])
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (1/4): Form Edit Status & Form Salin --}}
        <div class="col-lg-3">                     
            {{-- CARD 1: Tambah Siswa --}}
<form action="{{ route('class_user.store', $class_list_id) }}" method="POST" id="add-student-form">
    @csrf

    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <i class="bi bi-person-plus"></i> Tambah Siswa
        </div>

        <div class="card-body">

            {{-- Input pencarian --}}
            <label class="form-label small">Cari Siswa</label>
            <input type="text" id="search-student" class="form-control form-control-sm" 
                   placeholder="Ketik minimal 2 huruf...">

            {{-- Hasil pencarian (dropdown) --}}
            <div id="search-results" 
                 class="list-group position-absolute w-100 shadow" 
                 style="z-index: 99; display:none;"></div>

            {{-- ID siswa yang dipilih --}}
            <input type="hidden" name="student_id" id="student_id">

            {{-- Informasi siswa terpilih --}}
            <div id="selected-student-box" class="mt-2" style="display:none;">
    <div class="alert alert-info py-1 px-2 mb-2">
        <strong id="selected-student-name"></strong>
        <br>
        <span class="text-muted small">NIS: <span id="selected-student-nis"></span></span>

        <button type="button" class="btn-close float-end" id="clear-selected"></button>
    </div>
</div>

            <div class="d-grid mb-1">
                <button type="submit" class="btn btn-outline-success btn-sm" id="btn-add-student" disabled>
                    <i class="bi bi-plus-lg"></i> Tambah ke Kelas
                </button>
            </div>

        </div>
    </div>
</form>

            {{-- CARD 2: Salin Siswa (Copy) --}}
            <form action="{{ route('class_user.copy', $class_list_id) }}" method="POST" id="copy-form">
                @csrf
                {{-- Input tersembunyi untuk menampung ID siswa yang dicentang --}}
                <div id="selected-students-container"></div>

                <div class="card mb-3">
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
            // Logika AJAX untuk Dropdown Copy (Tahun -> Kelas)
            const filterYear = document.getElementById('filter_year');
            const classSelect = document.getElementById('class_copy_id');
            const copyBtn = document.getElementById('btn-copy');
            
            if(filterYear) {
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
            }

            // Sinkronisasi Checkbox
            const copyForm = document.getElementById('copy-form');
            const hiddenContainer = document.getElementById('selected-students-container');
            
            if(copyForm){
                copyForm.addEventListener('submit', function(e) {
                    hiddenContainer.innerHTML = ''; 
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
            }
        });
    </script>

    {{-- Script Search Table, Pagination & Per Page --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableContainer = document.getElementById('table-container');
            const searchInput = document.getElementById('search-input');
            const perPageSelect = document.getElementById('perPage'); 
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

            // Event: Ganti jumlah baris (perPage)
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
    <script>
document.addEventListener("DOMContentLoaded", function() {

    const searchInput = document.getElementById("search-student");
    const resultsBox = document.getElementById("search-results");
    const studentIdField = document.getElementById("student_id");
    const selectedBox = document.getElementById("selected-student-box");
    const selectedName = document.getElementById("selected-student-name");
    const clearBtn = document.getElementById("clear-selected");
    const btnAdd = document.getElementById("btn-add-student");

    // Ketik minimal 2 huruf → AJAX
    searchInput.addEventListener("keyup", function() {
        let q = this.value.trim();

        if (q.length < 2) {
            resultsBox.style.display = "none";
            return;
        }

        fetch("{{ route('class_user.ajax_search') }}?q=" + q)
            .then(res => res.text())
            .then(html => {
                resultsBox.innerHTML = html;
                resultsBox.style.display = "block";
            });
    });

    // Klik salah satu hasil → set student_id
    document.addEventListener("click", function(e) {
    if (e.target.classList.contains("res-item")) {

        let id = e.target.dataset.id;
        let name = e.target.dataset.name;
        let nis = e.target.dataset.nis;   // <--- BARU

        studentIdField.value = id;
        selectedName.textContent = name;
        document.getElementById("selected-student-nis").textContent = nis; // <--- BARU

        selectedBox.style.display = "block";

        resultsBox.style.display = "none";
        searchInput.value = "";

        btnAdd.disabled = false;
    }
});


    // Tombol X → hapus pilihan
    clearBtn.addEventListener("click", function() {
        studentIdField.value = "";
        selectedBox.style.display = "none";
        btnAdd.disabled = true;
    });

    $("#student-search-results").on("click", ".res-item", function () {
    let id   = $(this).data("id");
    let name = $(this).data("name");
    let nis  = $(this).data("nis");

    $("#student_id").val(id);
    $("#student_name").text(name);
    $("#student_nis").text(nis);

    $("#student-search-results").hide();
});


});
</script>
@endpush