@extends('layouts.app')

@section('title', 'Data Siswa | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Data Siswa</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            {{-- Kita arahkan ke route 'create' yang akan kita buat nanti --}}
            <a href="{{ route('student.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>Siswa Baru</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-body-tertiary">
            <form id="student-filter-form" action="{{ route('student.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="student-search-input" class="form-control" name="search" placeholder="Cari berdasarkan NIS atau Nama..." value="{{ $searchTerm ?? '' }}">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Tampil</label>
                        <select id="student-perpage-select" class="form-select" name="perPage">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">data</span>
                    </div>
                </div>
            </form>
        </div>

        <div id="student-table-container">
            @include('admin.student._student_table', ['students' => $students])
        </div>
    </div>
@endsection

@push('scripts')
{{-- JavaScript untuk pencarian live dan paginasi --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('student-table-container');
        const searchInput = document.getElementById('student-search-input');
        const perPageSelect = document.getElementById('student-perpage-select');
        const form = document.getElementById('student-filter-form');
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

        function performSearch() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = `{{ route('student.index') }}?${params.toString()}`;
            fetchTableData(url);
        }

        searchInput.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 500);
        });

        perPageSelect.addEventListener('change', performSearch);

        tableContainer.addEventListener('click', function(event) {
            if (event.target.tagName === 'A' && event.target.closest('.page-item')) {
                event.preventDefault();
                const url = event.target.href;
                if (url) { fetchTableData(url); }
            }
        });
    });
</script>
@endpush