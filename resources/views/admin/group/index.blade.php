@extends('layouts.app')

@section('title', 'Group Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Group Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.group.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>New Group</a>
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
            <form id="group-filter-form" action="{{ route('admin.group.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="group-search-input" class="form-control" name="search" placeholder="Ketik untuk mencari..." value="{{ $searchTerm ?? '' }}">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Show</label>
                        <select id="group-perpage-select" class="form-select" name="perPage">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                </div>
            </form>
        </div>
        
        <div id="group-table-container">
            @include('admin.group._group_table', ['groups' => $groups])
        </div>
    </div>
@endsection

@push('scripts')
{{-- [PERBAIKAN] Tambahkan blok JavaScript ini --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('group-table-container');
        const searchInput = document.getElementById('group-search-input');
        const perPageSelect = document.getElementById('group-perpage-select');
        const form = document.getElementById('group-filter-form');
        let debounceTimer;

        // Fungsi utama untuk mengambil data tabel via AJAX
        function fetchTableData(url) {
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                window.history.pushState({ path: url }, '', url);
            })
            .catch(error => console.error('Error fetching table data:', error));
        }

        // Fungsi untuk menjalankan pencarian/filter
        function performSearch() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = `{{ route('admin.group.index') }}?${params.toString()}`;
            fetchTableData(url);
        }

        // Event listener untuk input pencarian (dengan debounce)
        searchInput.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 500);
        });

        // Event listener untuk dropdown 'Show'
        perPageSelect.addEventListener('change', performSearch);
        
        // Event listener untuk link paginasi
        tableContainer.addEventListener('click', function(event) {
            if (event.target.tagName === 'A' && event.target.closest('.page-item')) {
                event.preventDefault();
                const url = event.target.href;
                if (url) {
                    fetchTableData(url);
                }
            }
        });
    });
</script>
@endpush