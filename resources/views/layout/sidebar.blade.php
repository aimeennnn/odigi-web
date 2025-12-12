<link rel="stylesheet" href="{{ asset('assets/css/layout_style.css') }}">
@php
    $user = auth()->user();
@endphp
<nav>
    <div class="app-logo" style="padding-left: 8px; padding-top: 8px;">
        <a class="logo d-inline-block logo-slide-animation" href="{{ route('dashboard.index') }}">
            <img src="{{asset('assets/image/Logo/logo_odigi.png')}}" alt="#" style="width: 220px; height: auto; max-width: 100%;">
        </a>
    
        <span class="bg-light-primary toggle-semi-nav">
          <i class="ti ti-chevrons-right f-s-20"></i>
        </span>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            <li>
                <a href="{{ route('dashboard.index') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
                    <i class="ph-duotone ph-house-line"></i> <span>Dashboard</span>
                </a>
            </li>
            @if($user && in_array($user->level, ['1','2','3','4']))
                <li>
                    <a href="{{ route('register.index') }}" class="{{ request()->is('register*') ? 'active' : '' }}">
                        <i class="ph-duotone ph-user-plus"></i> <span>Data Pengajuan</span>
                    </a>
                </li>
            @else
                @php
                    $isSuperAdmin = $user && $user->username === 'SUPERADMIN';
                    $authorizedMenus = $isSuperAdmin
                        ? ['menu_register','menu_manajemen','menu_data','menu_slik','menu_komite','menu_bank']
                        : ($user ? $user->getAuthorizedMenusArray() : []);
                @endphp
                @if($isSuperAdmin || in_array('menu_register', $authorizedMenus))
                <li>
                    <a href="{{ route('register.index') }}" class="{{ request()->is('register*') ? 'active' : '' }}">
                        <i class="ph-duotone ph-user-plus"></i> <span>Data Pengajuan</span>
                    </a>
                </li>
                @endif
                @if($isSuperAdmin || in_array('menu_manajemen', $authorizedMenus))
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">
                        <i class="ph-duotone ph-user-circle"></i> <span>Manajemen Pengguna</span>
                    </a>
                </li>
                @endif
            @endif
        </ul>
    </div>
    <div class="menu-navs">
        <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
        <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
    </div>
</nav>

<!-- Menu Navigation ends -->