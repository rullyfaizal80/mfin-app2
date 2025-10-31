@extends('layouts.app')

@section('title', 'Level Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Level Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>New</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-body-tertiary">
            <form id="level-filter-form" action="{{ route('admin.level.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="level-search-input" class="form-control" name="search" placeholder="Ketik untuk mencari..." value="{{ $searchTerm ?? '' }}">
                        {{-- [PERBAIKAN 1] Tampilkan kembali tombol dengan ikon kaca pembesar --}}
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Show</label>
                        <select id="level-perpage-select" class="form-select" name="perPage">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                </div>
            </form>
        </div>
        
        <div id="level-table-container">
            {{-- Konten tabel dan paginasi akan dimuat di sini --}}
            @include('admin.level._level_table', ['levels' => $levels])
        </div>
    </div>
@endsection

@push('scripts')
{{-- [PERBAIKAN 2] JavaScript diperbarui untuk menangani paginasi --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('level-table-container');
        const searchInput = document.getElementById('level-search-input');
        const perPageSelect = document.getElementById('level-perpage-select');
        const form = document.getElementById('level-filter-form');
        let debounceTimer;

        // Fungsi utama untuk mengambil data tabel via AJAX
        function fetchTableData(url) {
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                // Update URL di browser tanpa reload
                window.history.pushState({ path: url }, '', url);
            })
            .catch(error => console.error('Error fetching table data:', error));
        }

        // Fungsi untuk menjalankan pencarian/filter
        function performSearch() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = `{{ route('admin.level.index') }}?${params.toString()}`;
            fetchTableData(url);
        }

        // Event listener untuk input pencarian (dengan debounce)
        searchInput.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 500);
        });

        // Event listener untuk dropdown 'Show'
        perPageSelect.addEventListener('change', performSearch);
        
        // [BARU] Event listener untuk link paginasi
        // Kita pasang listener di container agar link baru yang dimuat AJAX juga berfungsi
        tableContainer.addEventListener('click', function(event) {
            // Cek apakah yang diklik adalah link paginasi (elemen <a> di dalam <li> dengan class 'page-item')
            if (event.target.tagName === 'A' && event.target.closest('.page-item')) {
                event.preventDefault(); // Mencegah reload halaman
                const url = event.target.href;
                if (url) {
                    fetchTableData(url);
                }
            }
        });
    });
</script>
@endpush