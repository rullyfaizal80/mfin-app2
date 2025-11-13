@extends('layouts.app')

@section('title', 'Data Orang Tua | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Data Orang Tua</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            {{-- Kita akan arahkan ke 'parent.create' nanti --}}
            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>Orang Tua Baru</a>
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
            <form id="parent-filter-form" action="{{ route('parent.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        <input type="text" id="parent-search-input" class="form-control" name="search" placeholder="Cari berdasarkan Nama..." value="{{ $searchTerm ?? '' }}">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Tampil</label>
                        <select id="parent-perpage-select" class="form-select" name="perPage">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">data</span>
                    </div>
                </div>
            </form>
        </div>
        
        <div id="parent-table-container">
            @include('admin.parent._parent_table', ['parents' => $parents])
        </div>
    </div>
@endsection

@push('scripts')
{{-- JavaScript untuk pencarian live dan paginasi (sama seperti student) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('parent-table-container');
        const searchInput = document.getElementById('parent-search-input');
        const perPageSelect = document.getElementById('parent-perpage-select');
        const form = document.getElementById('parent-filter-form');
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
            const url = `{{ route('parent.index') }}?${params.toString()}`;
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