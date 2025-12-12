<link rel="stylesheet" href="{{ asset('assets/css/partials_style.css') }}">
@php
    $isSuperAdmin = (auth()->user()->username === 'SUPERADMIN');
    $authorizedMenus = $isSuperAdmin
        ? ['menu_data','menu_slik','menu_komite','menu_bank']
        : auth()->user()->getAuthorizedMenusArray();
    $isKomiteLevel = in_array(auth()->user()->level, ['1','2','3','4']);
    
    // Get current register encrypted ID
    $regEnc = session('current_register_encrypted_id');
    $regRaw = session('current_register_id');
    $q = request('register_id');
    if(!$regEnc){
        $candidate = $q ?: $regRaw;
        if($candidate){
            try{ 
                \Illuminate\Support\Facades\Crypt::decryptString($candidate); 
                $regEnc = $candidate; 
            }
            catch(\Exception $e){ 
                $regEnc = \Illuminate\Support\Facades\Crypt::encryptString($candidate); 
            }
        }
    }
    if($regEnc){ 
        $regEnc = strtr($regEnc, ['+' => '-', '/' => '_', '=' => '.']); 
    }
@endphp

<div class="tab-navigation-container">
    <div class="tab-navigation">
        <!-- Detail Tab - Always visible -->
        @if($regEnc)
            <a href="{{ route('register.show', $regEnc) }}" class="tab-nav-item{{ request()->is('register*') && !request()->is('register/index') ? ' active' : '' }}">
                <div class="tab-icon">
                    <i class="bi bi-eye"></i>
                </div>
                <span class="tab-text">Detail</span>
            </a>
        @endif
        
        @if($isSuperAdmin || in_array('menu_data', $authorizedMenus) || $isKomiteLevel)
            <a href="{{ url('data_tambahdata') }}" class="tab-nav-item{{ request()->is('data*') ? ' active' : '' }}">
                <div class="tab-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <span class="tab-text">Data</span>
            </a>
        @endif
        @if($isSuperAdmin || in_array('menu_bank', $authorizedMenus) || $isKomiteLevel)
            <a href="{{ url('bank_tambahdata') }}" class="tab-nav-item{{ request()->is('bank*') ? ' active' : '' }}">
                <div class="tab-icon">
                    <i class="bi bi-bank"></i>
                </div>
                <span class="tab-text">Bank</span>
            </a>
        @endif
        @if($isSuperAdmin || in_array('menu_slik', $authorizedMenus) || $isKomiteLevel)
            <a href="{{ url('slik_tambahdata') }}" class="tab-nav-item{{ request()->is('slik*') ? ' active' : '' }}">
                <div class="tab-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <span class="tab-text">SLIK</span>
            </a>
        @endif
        @if($isSuperAdmin || in_array('menu_komite', $authorizedMenus))
            <a href="{{ url('komite_tambahdata') }}" class="tab-nav-item{{ request()->is('komite*') ? ' active' : '' }}">
                <div class="tab-icon">
                    <i class="bi bi-people"></i>
                </div>
                <span class="tab-text">Komite</span>
            </a>
        @endif
    </div>
</div>
