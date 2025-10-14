@php
    // Ambil data menu dari session, jika tidak ada, gunakan koleksi kosong.
    $menuTree = session('menuTree', collect());
@endphp

{{-- 
    'sidebar-nav' adalah class kustom yang kita styling di dashboard.blade.php 
--}}
<ul class="nav flex-column sidebar-nav" id="sidebar-nav">

    {{-- Link statis untuk Dashboard --}}
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
        </a>
    </li>{{-- Loop dinamis untuk setiap item menu utama --}}
    @foreach ($menuTree as $menu)
        
        {{-- Cek apakah menu ini punya anak/sub-menu --}}
        @if ($menu->children->isNotEmpty())

            {{-- JIKA ADA SUB-MENU: Buat komponen dropdown --}}
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#menu-{{ $menu->id }}" data-bs-toggle="collapse" href="#">
                    {{-- Logika untuk ikon: Gunakan ikon dari DB jika valid, jika tidak, gunakan ikon folder default --}}
                    <i class="bi {{ $menu->icon && $menu->icon !== 'page.png' ? $menu->icon : 'bi-folder' }}"></i>
                    <span>{{ $menu->title }}</span>
                    {{-- Ikon panah dropdown di sebelah kanan --}}
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="menu-{{ $menu->id }}" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    
                    {{-- Loop lagi untuk menampilkan setiap item sub-menu --}}
                    @foreach ($menu->children as $submenu)
                        <li>
                            <a href="{{ url($submenu->link) }}">
                                <i class="bi bi-circle"></i><span>{{ $submenu->title }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

        @else

            {{-- JIKA TIDAK ADA SUB-MENU: Buat link biasa --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ url($menu->link) }}">
                    {{-- Logika untuk ikon: Gunakan ikon dari DB jika valid, jika tidak, gunakan ikon file default --}}
                    <i class="bi {{ $menu->icon && $menu->icon !== 'page.png' ? $menu->icon : 'bi-file-earmark' }}"></i>
                    <span>{{ $menu->title }}</span>
                </a>
            </li>

        @endif
    @endforeach

</ul>