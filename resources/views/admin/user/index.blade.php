@extends('layouts.app')

@section('title', 'User Manager | MFIN')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">User Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>New</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-body-tertiary">
            {{-- [PERUBAHAN 1] Tambahkan ID pada form --}}
            <form id="user-filter-form" action="{{ route('admin.user.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 300px;">
                        {{-- [PERUBAHAN 2] Tambahkan ID pada input pencarian --}}
                        <input type="text" id="user-search-input" class="form-control" name="search" placeholder="Ketik untuk mencari..." value="{{ $searchTerm ?? '' }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                    <div class="input-group input-group-sm justify-content-end" style="max-width: 200px;">
                        <label class="input-group-text">Show</label>
                        <select class="form-select" name="perPage" onchange="this.form.submit()">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                </div>
            </form>
        </div>
        
        {{-- [PERUBAHAN 3] Bungkus partial dengan container yang memiliki ID --}}
        <div id="user-table-container">
            @include('admin.user._user_table', ['users' => $users])
        </div>
    </div>
@endsection

@push('scripts')
{{-- [TAMBAHAN] JavaScript untuk pencarian live --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('user-search-input');
        const form = document.getElementById('user-filter-form');
        let debounceTimer;

        searchInput.addEventListener('keyup', function (e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch();
            }, 500); // Tunggu 500ms setelah user berhenti mengetik
        });

        function performSearch() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = `{{ route('admin.user.index') }}?${params.toString()}`;

            // Update URL di browser tanpa reload
            window.history.pushState({ path: url }, '', url);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Penting untuk deteksi AJAX di Laravel
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('user-table-container').innerHTML = html;
            })
            .catch(error => console.error('Error during fetch:', error));
        }
    });
</script>
@endpush