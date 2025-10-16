@php
    $finalMenuData = session('finalMenuData', collect());

    // [BARU] Kamus kecil untuk ikon di setiap kategori utama
    $categoryIcons = [
        'Sekolah'    => 'bi-building',
        'Kasir'      => 'bi-cash-stack',
        'Keuangan'   => 'bi-wallet2',
        'Persediaan' => 'bi-box-seam',
        'Akunting'   => 'bi-journal-check',
        'Admin'      => 'bi-person-badge',
    ];
@endphp

{{-- 
    [PERUBAHAN 1] 
    Kita ganti ID <ul> utama menjadi "sidebar-accordion". 
    Ini akan menjadi "induk" yang mengontrol semua kategori, 
    membuatnya saling menutup saat salah satu dibuka.
--}}
<ul class="nav flex-column sidebar-nav" id="sidebar-accordion">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i><span>Dashboard</span>
        </a>
    </li>

    {{-- Loop untuk setiap KATEGORI UTAMA --}}
    @foreach ($finalMenuData as $category)
        
        <li class="nav-item">
            {{-- 
                [PERUBAHAN 2] 
                Heading kategori yang tadinya hanya tulisan (`<li class="nav-heading">`),
                sekarang menjadi sebuah link/tombol yang bisa diklik untuk membuka menu di bawahnya.
            --}}
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#collapse-{{ Str::slug($category->category_name) }}">
                
                {{-- Menampilkan ikon kategori dari kamus di atas --}}
                <i class="bi {{ $categoryIcons[$category->category_name] ?? 'bi-folder' }}"></i>
                <span>{{ $category->category_name }}</span>
                <i class="bi bi-chevron-down ms-auto"></i>

            </a>

            {{-- 
                [PERUBAHAN 3] 
                Semua menu di dalam kategori ini sekarang dibungkus dalam sebuah <div> yang bisa disembunyikan.
                'data-bs-parent="#sidebar-accordion"' adalah kunci yang menciptakan efek akordion.
            --}}
            <div id="collapse-{{ Str::slug($category->category_name) }}" class="collapse nav-content" data-bs-parent="#sidebar-accordion">
                <ul>
                    @foreach($category->menu_tree as $menu)
                        {{-- Partial rekursif Anda tidak perlu diubah sama sekali --}}
                        @include('partials.menu-item', ['menu' => $menu])
                    @endforeach
                </ul>
            </div>
        </li>

    @endforeach
</ul>