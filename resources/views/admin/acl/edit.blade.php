@extends('layouts.app')

@section('title', 'Edit Permissions | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Permissions for: <strong class="text-primary">{{ $group->group_name }}</strong></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.acl.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back to ACL List
        </a>
    </div>
</div>

<div class="row">
    {{-- Kolom Kiri: Halaman yang Tersedia --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Available Pages</div>
            <div class="card-body" style="height: 400px; overflow-y: auto;">
                <input type="text" id="filter-available" class="form-control mb-2" placeholder="Filter pages...">
                <ul class="list-group" id="available-pages-list">
                    @forelse ($availablePages as $page)
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-id="{{ $page->id }}">
                            {{ $page->title }}
                            <button class="btn btn-sm btn-outline-success add-btn">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">All pages have been assigned.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Kolom Tengah: Panah (Hanya visual) --}}
    <div class="col-md-2 text-center align-self-center d-none d-md-block">
        <i class="bi bi-arrow-left-right" style="font-size: 2rem;"></i>
    </div>

    {{-- Kolom Kanan: Halaman yang Diizinkan --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Allowed Pages</div>
            <div class="card-body" style="height: 400px; overflow-y: auto;">
                <input type="text" id="filter-allowed" class="form-control mb-2" placeholder="Filter pages...">
                <ul class="list-group" id="allowed-pages-list">
                    @forelse ($allowedPages as $page)
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-id="{{ $page->id }}">
                            {{ $page->title }}
                            <button class="btn btn-sm btn-outline-danger remove-btn">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No pages assigned.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupId = {{ $group->id }};
    const availableList = document.getElementById('available-pages-list');
    const allowedList = document.getElementById('allowed-pages-list');
    const filterAvailable = document.getElementById('filter-available');
    const filterAllowed = document.getElementById('filter-allowed');
    // Token CSRF diambil dari meta tag jika ada, jika tidak, gunakan dari Blade
    const csrfToken = '{{ csrf_token() }}';

    // Fungsi untuk memindahkan list item
    function moveItem(item, fromList, toList, action) {
        const pageId = item.dataset.id;
        const url = (action === 'add') ? "{{ route('admin.acl.add') }}" : "{{ route('admin.acl.remove') }}";
        const method = (action === 'add') ? 'POST' : 'DELETE';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                group_id: groupId,
                page_id: pageId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const placeholder = toList.querySelector('.text-muted');
                if (placeholder) placeholder.remove();

                toList.appendChild(item);

                if (fromList.children.length === 0 || (fromList.children.length === 1 && fromList.children[0].style.display === 'none')) {
                    fromList.innerHTML = `<li class="list-group-item text-muted">${(fromList === availableList) ? 'All pages assigned.' : 'No pages assigned.'}</li>`;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Event listener untuk tombol 'Add'
    availableList.addEventListener('click', function(e) {
        const button = e.target.closest('.add-btn');
        if (button) {
            const item = button.closest('li');
            button.classList.replace('btn-outline-success', 'btn-outline-danger');
            button.classList.replace('add-btn', 'remove-btn');
            button.innerHTML = '<i class="bi bi-x-lg"></i>';
            moveItem(item, availableList, allowedList, 'add');
        }
    });

    // Event listener untuk tombol 'Remove'
    allowedList.addEventListener('click', function(e) {
        const button = e.target.closest('.remove-btn');
        if (button) {
            const item = button.closest('li');
            button.classList.replace('btn-outline-danger', 'btn-outline-success');
            button.classList.replace('remove-btn', 'add-btn');
            button.innerHTML = '<i class="bi bi-chevron-right"></i>';
            moveItem(item, allowedList, availableList, 'remove');
        }
    });

    // Fungsi filter
    function filterList(input, list) {
        const filter = input.value.toLowerCase();
        const items = list.getElementsByTagName('li');
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            if (!item.classList.contains('text-muted')) {
                const text = item.textContent || item.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            }
        }
    }

    filterAvailable.addEventListener('keyup', () => filterList(filterAvailable, availableList));
    filterAllowed.addEventListener('keyup', () => filterList(filterAllowed, allowedList));
});
</script>
@endpush