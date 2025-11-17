@extends('layouts.app')

@section('title', 'Daftar Kelas | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Kelas</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('class_list.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kelas Baru
            </a>
        </div>
    </div>

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
    
    {{-- Card Filter --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-funnel-fill"></i> Filter Data
        </div>
        <div class="card-body">
            <form id="filter-form" action="{{ route('class_list.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="fschool" class="form-label">Sekolah</label>
                        <select id="fschool" name="fschool" class="form-select form-select-sm">
                            <option value="-">Semua</option>
                            @foreach($filter_data['schools'] as $school)
                                <option value="{{ $school->id }}" {{ $filters['fschool'] == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="fcyear" class="form-label">Tahun Ajaran</label>
                        <select id="fcyear" name="fcyear" class="form-select form-select-sm">
                            <option value="-">Semua</option>
                             @foreach($filter_data['cyears'] as $year)
                                <option value="{{ $year->id }}" {{ $filters['fcyear'] == $year->id ? 'selected' : '' }}>{{ $year->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fgrade" class="form-label">Tingkat</label>
                        <select id="fgrade" name="fgrade" class="form-select form-select-sm">
                            <option value="-">Semua</option>
                             @foreach($filter_data['grades'] as $grade)
                                <option value="{{ $grade->id }}" {{ $filters['fgrade'] == $grade->id ? 'selected' : '' }}>{{ $grade->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="ftype" class="form-label">Tipe</label>
                        <select id="ftype" name="ftype" class="form-select form-select-sm">
                            <option value="-">Semua</option>
                             @foreach($filter_data['types'] as $type)
                                <option value="{{ $type->id }}" {{ $filters['ftype'] == $type->id ? 'selected' : '' }}>{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card untuk List Data --}}
    <div class="card">
        <div class="card-header bg-body-tertiary">
             <form id="search-form" action="{{ route('class_list.index') }}" method="GET">
                {{-- Kirim filter yang ada saat ini --}}
                <input type="hidden" name="fschool" value="{{ $filters['fschool'] }}">
                <input type="hidden" name="fcyear" value="{{ $filters['fcyear'] }}">
                <input type="hidden" name="fgrade" value="{{ $filters['fgrade'] }}">
                <input type="hidden" name="ftype" value="{{ $filters['ftype'] }}">
                
                <div class="input-group input-group-sm" style="max-width: 300px;">
                    <input type="text" id="search-input" class="form-control" name="search" placeholder="Cari berdasarkan Nama Kelas..." value="{{ $filters['search'] }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
        
        <div id="table-container">
            @include('admin.class_list._class_list_table', ['list_data' => $list_data])
        </div>
    </div>
@endsection

@push('scripts')
{{-- Script untuk live search/pagination --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('table-container');
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const filterForm = document.getElementById('filter-form');
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
                const params = new URLSearchParams(new FormData(searchForm));
                const url = `${searchForm.action}?${params.toString()}`;
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
    });
</script>
@endpush