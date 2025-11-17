@extends('layouts.app')

@section('title', 'Daftar Sekolah | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Sekolah</h1>
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
    
    {{-- Tata Letak Grid Berdampingan (3/4 Kiri, 1/4 Kanan) --}}
    <div class="row">

        {{-- Kolom Kiri (Lebar 3/4): List Data --}}
        <div class="col-lg-9 mb-4">
            <div class="card">
                <div class="card-header bg-body-tertiary">
                    <form id="filter-form" action="{{ $data_form->id ? route('cschool.edit', $data_form->id) : route('cschool.index') }}" method="GET">
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <input type="text" id="search-input" class="form-control" name="search" placeholder="Filter Sekolah..." value="{{ $searchTerm ?? '' }}">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </form>
                </div>
                
                <div id="table-container">
                    @include('admin.cschool._cschool_table', ['list_data' => $list_data])
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (Sempit 1/4): Form Tambah/Edit --}}
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    @if ($data_form->id)
                        Edit: <strong>{{ $data_form->name }}</strong>
                    @else
                        Sekolah Baru
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ $form_action }}" method="POST">
                        @csrf
                        @if($form_method == 'PUT')
                            @method('PUT')
                        @endif
                        
                        {{-- Formulir dibuat scrollable jika field terlalu banyak --}}
                        <div style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Nama *</label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name" value="{{ old('name', $data_form->name) }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="street" class="form-label">Jalan</label>
                                    <textarea class="form-control form-control-sm" id="street" name="street" rows="2">{{ old('street', $data_form->street) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="city" class="form-label">Kota</label>
                                    <input type="text" class="form-control form-control-sm" id="city" name="city" value="{{ old('city', $data_form->city) }}">
                                </div>
                                <div class="col-12">
                                    <label for="province" class="form-label">Provinsi</label>
                                    <input type="text" class="form-control form-control-sm" id="province" name="province" value="{{ old('province', $data_form->province) }}">
                                </div>
                                <div class="col-12">
                                    <label for="country" class="form-label">Negara</label>
                                    <input type="text" class="form-control form-control-sm" id="country" name="country" value="{{ old('country', $data_form->country) }}">
                                </div>
                                <div class="col-12">
                                    <label for="postalcode" class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control form-control-sm" id="postalcode" name="postalcode" value="{{ old('postalcode', $data_form->postalcode) }}">
                                </div>
                                <div class="col-12">
                                    <label for="telephone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control form-control-sm" id="telephone" name="telephone" value="{{ old('telephone', $data_form->telephone) }}">
                                </div>
                                <div class="col-12">
                                    <label for="fax" class="form-label">Fax</label>
                                    <input type="text" class="form-control form-control-sm" id="fax" name="fax" value="{{ old('fax', $data_form->fax) }}">
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" id="email" name="email" value="{{ old('email', $data_form->email) }}">
                                </div>
                                <div class="col-12">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="text" class="form-control form-control-sm" id="website" name="website" value="{{ old('website', $data_form->website) }}">
                                </div>
                                <div class="col-12">
                                    <label for="headmaster_id" class="form-label">ID Kepala Sekolah</label>
                                    @if ($data_form->id && $data_form->headmaster_name)
                                        <small class="d-block text-muted">Saat ini: ({{ $data_form->headmaster_name }})</small>
                                    @endif
                                    <input type="number" class="form-control form-control-sm" id="headmaster_id" name="headmaster_id" value="{{ old('headmaster_id', $data_form->headmaster_id) }}">
                                    <small class="text-muted">Masukkan ID User dari Kepala Sekolah.</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-grid gap-2">
                            @if ($data_form->id)
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan</button>
                                <a href="{{ route('cschool.index') }}" class="btn btn-secondary btn-sm"> Batal</a>
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
{{-- Script untuk live search/pagination --}}
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
                const baseUrl = "{{ route('cschool.index') }}";
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