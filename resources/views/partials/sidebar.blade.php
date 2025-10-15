@php
    $finalMenuData = session('finalMenuData', collect());
@endphp

<ul class="nav flex-column sidebar-nav" id="sidebar-nav">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i><span>Dashboard</span>
        </a>
    </li>

    {{-- Loop untuk setiap KATEGORI UTAMA (Sekolah, Kasir, Admin, dll.) --}}
    @foreach ($finalMenuData as $category)
        
        {{-- Tampilkan nama kategori dinamis sebagai heading --}}
        <li class="nav-heading">{{ $category->category_name }}</li>

        {{-- Loop untuk setiap item menu di dalam pohon kategori tersebut --}}
        @foreach($category->menu_tree as $menu)
            {{-- Gunakan partial rekursif yang sudah kita buat sebelumnya --}}
            @include('partials.menu-item', ['menu' => $menu])
        @endforeach

    @endforeach
</ul>