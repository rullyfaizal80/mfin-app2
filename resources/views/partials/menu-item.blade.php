@if ($menu->children->isNotEmpty())
    {{-- JIKA ADA SUB-MENU: Buat komponen dropdown --}}
    <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#menu-{{ $menu->id }}" data-bs-toggle="collapse" href="#">
            <i class="bi {{ $menu->icon ?? 'bi-folder' }}"></i>
            <span>{{ $menu->title }}</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="menu-{{ $menu->id }}" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            {{-- PANGGILAN REKURSIF: Loop setiap anak dan render dengan file ini lagi --}}
            @foreach ($menu->children as $child)
                @include('partials.menu-item', ['menu' => $child])
            @endforeach
        </ul>
    </li>
@else
    
    {{-- JIKA TIDAK ADA SUB-MENU: Buat link biasa --}}
    <li class="nav-item">
        {{-- [PERUBAHAN] Cek jika linknya adalah 'admin/user' --}}
        @if ($menu->link === 'admin/user')
            <a class="nav-link collapsed" href="{{ route('admin.user.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'admin/level')
            <a class="nav-link collapsed" href="{{ route('admin.level.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'admin/group')
            <a class="nav-link collapsed" href="{{ route('admin.group.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'admin/acl')
            <a class="nav-link collapsed" href="{{ route('admin.acl.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'user/student')
            <a class="nav-link collapsed" href="{{ route('student.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
       @elseif ($menu->link === 'user/parents')
            <a class="nav-link collapsed" href="{{ route('parent.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'user/teacher')
            <a class="nav-link collapsed" href="{{ route('teacher.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'master/cyear')
            <a class="nav-link collapsed" href="{{ route('cyear.index') }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @elseif ($menu->link === 'master/csubject')
    <a class="nav-link collapsed" href="{{ route('csubject.index') }}">
        <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
        <span>{{ $menu->title }}</span>
    </a>
@elseif ($menu->link === 'master/cgrade')
    <a class="nav-link collapsed" href="{{ route('cgrade.index') }}">
        <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
        <span>{{ $menu->title }}</span>
    </a>
@elseif ($menu->link === 'master/cgroup')
    <a class="nav-link collapsed" href="{{ route('cgroup.index') }}">
        <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
        <span>{{ $menu->title }}</span>
    </a>
@elseif ($menu->link === 'master/ctype')
    <a class="nav-link collapsed" href="{{ route('ctype.index') }}">
        <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
        <span>{{ $menu->title }}</span>
    </a>
@else
            {{-- Link lain tetap seperti semula --}}
            <a class="nav-link collapsed" href="{{ url($menu->link) }}">
                <i class="bi {{ $menu->icon ?? 'bi-file-earmark' }}"></i>
                <span>{{ $menu->title }}</span>
            </a>
        @endif
    </li>
@endif
