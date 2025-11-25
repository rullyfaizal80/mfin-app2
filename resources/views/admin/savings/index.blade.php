@extends('layouts.app')

@section('title', 'Tabungan | MFIN')

@section('content')
    {{-- LOADING OVERLAY --}}
    <div id="loading-overlay" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; flex-direction:column; justify-content:center; align-items:center;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <div class="mt-2 fw-bold text-dark fs-5">Memuat Data...</div>
    </div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tabungan Siswa & Guru</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            {{-- Tombol Setoran --}}
            <a href="{{ route('savings.create_new') }}" class="btn btn-sm btn-success me-2">
                <i class="bi bi-arrow-down-circle"></i> Setoran
            </a>
            
            {{-- Tombol Penarikan --}}
            <a href="{{ route('savings.create') }}" class="btn btn-sm btn-danger">
                <i class="bi bi-arrow-up-circle"></i> Penarikan
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-body-tertiary">
            {{-- Form Search Sederhana --}}
            <form action="{{ route('savings.index') }}" method="GET" class="row g-2" id="search-form">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Cari Nama Nasabah..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Nasabah</th>
                            <th>ID (NIS/NIK)</th>
                            <th class="text-center">Trans. Terakhir</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($savings as $index => $row)
                            <tr>
                                <td>{{ $savings->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $row->fullname }}</strong>
                                </td>
                                <td>{{ $row->identity_number }}</td>
                                <td class="text-center text-muted small">
                                    {{ $row->last_transaction ? date('d-m-Y', strtotime($row->last_transaction)) : '-' }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    Rp {{ number_format($row->total_saldo, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{-- Link Rincian --}}
                                    <a href="{{ route('savings.show', $row->user_id) }}" class="btn btn-xs btn-info text-white show-loading" title="Lihat Rincian">
                                        <i class="bi bi-list-ul"></i> Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data tabungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-3 pagination-links">
                {{ $savings->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loader = document.getElementById('loading-overlay');
            
            // FUNGSI: Tampilkan Loading
            function showLoading() {
                loader.style.display = 'flex';
            }

            // 1. Loading saat Submit Filter/Cari
            const searchForm = document.getElementById('search-form');
            if(searchForm) {
                searchForm.addEventListener('submit', showLoading);
            }

            // 2. Loading saat klik Pagination
            const paginationContainer = document.querySelector('.pagination-links');
            if(paginationContainer) {
                paginationContainer.addEventListener('click', function(e) {
                    if(e.target.tagName === 'A' || e.target.closest('a')) {
                        showLoading();
                    }
                });
            }

            // 3. Loading saat klik tombol Rincian
            const detailBtns = document.querySelectorAll('.show-loading');
            detailBtns.forEach(btn => {
                btn.addEventListener('click', showLoading);
            });
        });

        // [PENTING] Fix untuk tombol Back Browser
        // Jika user menekan Back, sembunyikan loading screen jika masih nongol
        window.addEventListener('pageshow', function(event) {
            const loader = document.getElementById('loading-overlay');
            if (event.persisted || (window.performance && window.performance.navigation.type == 2)) {
                if(loader) loader.style.display = 'none';
            }
        });
    </script>
@endpush