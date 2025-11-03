@extends('layouts.app')

@section('title', 'Access Control Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Access Control Manager</h1>
    </div>

    <div class="card">
        <div class="card-header bg-body-tertiary">
            <form id="acl-filter-form" action="{{ route('admin.acl.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="acl-search-input" class="form-control" name="search" placeholder="Cari nama grup..." value="{{ $searchTerm ?? '' }}">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Show</label>
                        <select id="acl-perpage-select" class="form-select" name="perPage">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                </div>
            </form>
        </div>

        <div id="acl-table-container">
            @include('admin.acl._acl_table', ['groups' => $groups])
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('acl-table-container');
        const searchInput = document.getElementById('acl-search-input');
        const perPageSelect = document.getElementById('acl-perpage-select');
        const form = document.getElementById('acl-filter-form');
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
            const url = `{{ route('admin.acl.index') }}?${params.toString()}`;
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