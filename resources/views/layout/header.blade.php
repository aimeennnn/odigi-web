<link rel="stylesheet" href="{{ asset('assets/css/layout_style.css') }}">

<!-- Header Section starts -->
<header class="header-main">
    <div class="container-fluid ">
        <div class="row">
            <div class="col-6 col-sm-4 d-flex align-items-center header-left p-0">
                <span class="header-toggle me-3">
                  <i class="ph ph-circles-four"></i>
                </span>
            </div>

            <div class="col-6 col-sm-8 d-flex align-items-center justify-content-end header-right p-0">
                <ul class="d-flex align-items-center">
                    
                    @auth
                    <li class="header-profile">
                        <a href="#" class="d-flex align-items-center head-icon" role="button" data-bs-toggle="offcanvas"
                           data-bs-target="#profilecanvasRight" aria-controls="profilecanvasRight">
                            <div class="d-flex align-items-center justify-content-center position-relative profile-avatar" 
                                 style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%); color: white; font-weight: 600; font-size: 1.1rem; box-shadow: 0 3px 8px rgba(29, 209, 161, 0.25); overflow: hidden;">
                                <span style="position: relative; z-index: 1;">
                                    {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->username, 0, 1)) }}
                                </span>
                                <div class="position-absolute top-0 end-0 h-10 w-10 bg-emerald-500 rounded-full border-2 border-white" 
                                     style="transform: translate(25%, -25%);"></div>
                            </div>
                            <div class="ms-3 d-none d-sm-block">
                                <span class="d-block f-w-600 text-gray-900" style="font-size: 1.25rem; line-height: 1.35;">{{ Auth::user()->nama ?? Auth::user()->username }}</span>
                                <span class="d-block text-gray-500 mt-1" style="font-size: 0.9rem; font-weight: 600; margin-top: 6px;">{{ Auth::user()->role ?? 'User' }}</span>
                            </div>
                            <i class="ph-duotone ph-caret-down ms-2 text-gray-400" style="font-size: 0.8rem;"></i>
                        </a>

                        <div class="offcanvas offcanvas-end header-profile-canvas" tabindex="-1" id="profilecanvasRight"
                             aria-labelledby="profilecanvasRight">
                            <div class="offcanvas-header" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%); color: white; border-bottom: none; position: relative; overflow: hidden;">
                                <!-- ODIGI Logo Background -->
                                <div class="odigi-logo-bg-header" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1;">
                                    <img src="{{ asset('assets/image/Logo/logo_odigi.png') }}" alt="ODIGI" style="width: 140px; height: auto; filter: brightness(0) invert(1);">
                                </div>
                                <h5 class="offcanvas-title f-w-600 mb-0" style="font-size: 0.9rem; position: relative; z-index: 2;">Profile</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close" style="position: relative; z-index: 2;"></button>
                            </div>
                            <div class="offcanvas-body app-scroll p-0">
                                <ul class="list-unstyled mb-0">
                                    <li class="p-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center position-relative me-4 profile-avatar" 
                                                 style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%); color: white; font-weight: 600; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(29, 209, 161, 0.3); overflow: hidden;">
                                                <span style="position: relative; z-index: 1;">
                                                    {{ Auth::user() ? strtoupper(substr(Auth::user()->nama ?? Auth::user()->username, 0, 1)) : 'U' }}
                                                </span>
                                                <div class="position-absolute top-0 end-0 h-14 w-14 bg-emerald-500 rounded-full border-2 border-white" 
                                                     style="transform: translate(25%, -25%);"></div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2 f-w-600 text-gray-900" style="font-size: 1.125rem;">{{ Auth::user()->nama ?? Auth::user()->username }}</h6>
                                                <p class="mb-2 text-gray-600" style="font-size: 0.9rem;">{{ Auth::user()->email }}</p>
                                                <span class="badge rounded-pill px-3 py-1" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%); color: white; font-size: 0.8rem; font-weight: 500;">
                                                    {{ Auth::user()->role ?? 'User' }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="p-4">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <a class="d-flex flex-column align-items-center p-4 rounded-lg text-decoration-none profile-action-card" 
                                                   href="{{route('profile')}}" target="_blank"
                                                   style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; transition: all 0.3s ease; border: none;">
                                                    <i class="ph-duotone ph-user-circle mb-2" style="font-size: 1.25rem;"></i>
                                                    <span class="f-w-500" style="font-size: 0.8rem;">Profile</span>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a class="d-flex flex-column align-items-center p-4 rounded-lg text-decoration-none profile-action-card" 
                                                   href="{{route('setting')}}" target="_blank"
                                                   style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; transition: all 0.3s ease; border: none;">
                                                    <i class="ph-duotone ph-gear mb-2" style="font-size: 1.25rem;"></i>
                                                    <span class="f-w-500" style="font-size: 0.8rem;">Settings</span>
                                                </a>
                                            </div>
                                        </div>
                                    </li> -->

                                    <!-- <li class="px-4">
                                        <div class="card border-0 shadow-sm" style="background: #ffffff; border-radius: 10px;">
                                            <div class="card-body p-4">
                                                <h6 class="card-title mb-4 f-w-600 text-gray-900" style="font-size: 0.9rem;">
                                                    <i class="ph-duotone ph-sliders me-2" style="color: #1dd1a1;"></i>Preferences
                                                </h6>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ph-duotone ph-eye-slash me-3 text-gray-500" style="font-size: 0.9rem;"></i>
                                                        <span class="f-w-500 text-gray-700" style="font-size: 0.9rem;">Hide Settings</span>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="hideSetting" checked 
                                                               style="background-color: #1dd1a1; border-color: #1dd1a1;">
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ph-duotone ph-notification me-3 text-gray-500" style="font-size: 0.9rem;"></i>
                                                        <span class="f-w-500 text-gray-700" style="font-size: 0.9rem;">Notifications</span>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="basicSwitch" checked
                                                               style="background-color: #1dd1a1; border-color: #1dd1a1;">
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ph-duotone ph-detective me-3 text-gray-500" style="font-size: 0.9rem;"></i>
                                                        <span class="f-w-500 text-gray-700" style="font-size: 0.9rem;">Incognito Mode</span>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="incognitoSwitch"
                                                               style="background-color: #1dd1a1; border-color: #1dd1a1;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="p-4">
                                        <div class="card border-0" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%); border-radius: 10px; position: relative; overflow: hidden;">
                                            <div class="card-body p-4 text-center text-white">
                                                <div class="mb-3">
                                                    <i class="ph-duotone ph-crown" style="font-size: 1.5rem; opacity: 0.9;"></i>
                                                </div>
                                                <h6 class="mb-2 f-w-600" style="font-size: 1rem;">Premium Plan</h6>
                                                <p class="mb-4 opacity-75" style="font-size: 0.85rem;">Unlock all features</p>
                                                <button type="button" class="btn btn-light btn-sm f-w-500 rounded-pill px-4 py-2" 
                                                        style="font-size: 0.85rem; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                    Upgrade Now
                                                </button>
                                            </div>
                                            <div class="position-absolute top-0 end-0 w-100 h-100" 
                                                 style="background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
                                        </div>
                                    </li> -->

                                    <li class="p-4 border-top" style="border-color: #e5e7eb !important;">
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                                            @csrf
                                            <button type="submit" class="btn w-100 f-w-500 rounded-lg py-3 signout-btn" 
                                                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); font-size: 0.9rem;">
                                                <i class="ph-duotone ph-sign-out me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</header>
<!-- Header Section ends -->
