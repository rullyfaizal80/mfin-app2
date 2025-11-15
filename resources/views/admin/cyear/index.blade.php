@extends('layouts.app')

@section('title', 'Daftar Tahun Ajaran | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Tahun Ajaran</h1>
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
    
    {{-- [PERUBAHAN] Tata Letak Grid Berdampingan --}}
    <div class="row">

        {{-- Kolom Kiri (Lebar 3/4): List Data --}}
        <div class="col-lg-9 mb-4">
            <div class="card">
                <div class="card-header bg-body-tertiary">
                    <form id="filter-form" action="{{ $data_form->id ? route('cyear.edit', $data_form->id) : route('cyear.index') }}" method="GET">
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <input type="text" id="search-input" class="form-control" name="search" placeholder="Cari berdasarkan Tahun Ajaran..." value="{{ $searchTerm ?? '' }}">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </form>
                </div>
                
                <div id="table-container">
                    @include('admin.cyear._cyear_table', ['list_data' => $list_data])
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (Sempit 1/4): Form Tambah/Edit --}}
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    @if ($data_form->id)
                        Edit: <strong>{{ $data_form->title }}</strong>
                    @else
                        Tambah Baru
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ $form_action }}" method="POST">
                        @csrf
                        @if($form_method == 'PUT')
                            @method('PUT')
                        @endif
                        
                        {{-- [PERBAIKAN] Layout form diubah jadi 1 kolom agar muat --}}
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label">Tahun Ajaran *</label>
                                <input type="text" class="form-control form-control-sm" id="title" name="title" value="{{ old('title', $data_form->title) }}" required>
                            </div>
                            <div class="col-12">
                                <label for="date_start" class="form-label">Tanggal Mulai *</label>
                                <input type="date" class="form-control form-control-sm" id="date_start" name="date_start" value="{{ old('date_start', $data_form->date_start) }}" required>
                            </div>
                            <div class="col-12">
                                <label for="date_end" class="form-label">Tanggal Selesai *</label>
                                <input type="date" class="form-control form-control-sm" id="date_end" name="date_end" value="{{ old('date_end', $data_form->date_end) }}" required>
                            </div>
                            <div class="col-12">
                                <label for="note" class="form-label">Catatan</label>
                                <input type="text" class="form-control form-control-sm" id="note" name="note" value="{{ old('note', $data_form->note) }}">
                            </div>
                            <div class="col-12">
                                <label for="is_active" class="form-label">Aktif *</label>
                                <select id="is_active" name="is_active" class="form-select form-select-sm">
                                    <option value="yes" {{ old('is_active', $data_form->is_active) == 'yes' ? 'selected' : '' }}>Ya</option>
                                    <option value="no" {{ old('is_active', $data_form->is_active) == 'no' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-grid gap-2">
                            @if ($data_form->id)
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan</button>
                                <a href="{{ route('cyear.index') }}" class="btn btn-secondary btn-sm"> Batal</a>
                            @else
                                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-plus-circle me-1"></i> Tambah</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
{{-- Script untuk live search (Tidak berubah) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('table-container');
        const searchInput = document.getElementById('search-input');
        const form = document.getElementById('filter-form');
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
                const params = new URLSearchParams(new FormData(form));
                const baseUrl = "{{ route('cyear.index') }}";
                const url = `${baseUrl}?${params.toString()}`;
                
                if (form.action.includes('/edit')) {
                    const editUrl = form.action.split('?')[0];
                    window.history.pushState({ path: url }, '', `${editUrl}?${params.toString()}`);
                } else {
                    window.history.pushState({ path: url }, '', url);
                }

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