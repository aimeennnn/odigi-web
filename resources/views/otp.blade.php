@section('title', 'Verifikasi OTP')
@include('layout.head')

@include('layout.css')
<link rel="stylesheet" href="{{ asset('assets/css/login_style.css') }}">

<body class="sign-in-bg">
<div class="app-wrapper d-block">
    <div class="main-container">
        <!-- Body main section starts -->
        <div class="container">
            <div class="row sign-in-content-bg">
                <div class="col-lg-6 image-contentbox d-none d-lg-block">
                    <div class="form-container ">
                        <div class="signup-content mt-4">
                <span>
                  <img src="{{asset('assets/image/Logo/logo_odigi.png')}}" alt="" class="img-fluid" style="width: 200px; height: auto; max-width: 100%;">
                </span>
                        </div>

                        <div class="signup-bg-img">
                            <img src="{{asset('assets/image/Logo/login-security.svg')}}" alt="" class="img-fluid">
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 form-contentbox">
                    <div class="form-container">
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success fade show" role="alert">
                                <span id="successMessage">{{ session('success') }}</span>
                                <span id="dynamicCountdown"></span>
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-4 text-center">
                            <h3 class="text-primary f-w-600 mb-3">Verifikasi OTP</h3>
                            <div class="mb-3">
                                <img src="{{asset('assets/image/Logo/logo_odigi.png')}}" alt="ODIGI Logo" class="img-fluid logo-animated" style="width: 300px; height: auto; max-width: 100%;">
                            </div>
                            <h7 class="text-secondary f-w-500 mb-2">BPR Go Digital - Digitalkan Pekerjaanmu</h7>
                            <p class="text-muted small">Untuk keamanan Anda, masukkan kode yang dikirim ke</p>
                            <p class="text-secondary small"><strong>{{ $masked_phone }}</strong></p>
                        </div>

                        <!-- Verify OTP Form -->
                        <form class="app-form" method="POST" action="{{ route('otp.verify') }}">
                            @csrf
                            <input type="hidden" name="otp_code" id="hiddenOtp" value="">

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-center gap-2 mb-3 otp-boxes">
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" />
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" />
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" />
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" />
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" />
                                        <input class="otp-input form-control text-center" type="text" inputmode="numeric" maxlength="1" />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <small id="countdown" class="text-muted">Memuat...</small>
                                        <button id="resendBtn" type="button" class="btn btn-link p-0" disabled>
                                            <span id="resendText">Kirim ulang</span>
                                            <span id="resendSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-column gap-2 mb-3">
                                        <button id="submitBtn" type="submit" class="btn btn-primary w-100" disabled>Submit</button>
                                        <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">Cancel</a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-center">
                                        <small class="text-muted">Tidak menerima kode? Silakan hubungi administrator.</small>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Hidden resend form -->
                        <form id="resendForm" method="POST" action="{{ route('otp.resend') }}" style="display:none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Body main section ends -->
    </div>
</div>


</body>

<style>
    .otp-boxes .form-control { width: 52px; height: 52px; font-size: 22px; }
    @media (max-width: 480px) { .otp-boxes .form-control { width: 44px; height: 44px; font-size: 20px; } }
</style>

    <!-- Bootstrap js-->
    <script src="{{asset('assets/vendor/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = Array.from(document.querySelectorAll('.otp-input'));
        const hidden = document.getElementById('hiddenOtp');
        const submitBtn = document.getElementById('submitBtn');
        const resendBtn = document.getElementById('resendBtn');
        const resendText = document.getElementById('resendText');
        const resendSpinner = document.getElementById('resendSpinner');
        const resendForm = document.getElementById('resendForm');
        const countdownEl = document.getElementById('countdown');
        const expiresIso = @json($expires_at);

        console.log('OTP Debug Info:', {
            expiresIso: expiresIso,
            countdownEl: !!countdownEl,
            resendBtn: !!resendBtn,
            inputsLength: inputs.length
        });

        // Guard
        if (!hidden || inputs.length !== 6) return;

        const updateHidden = () => {
            const code = inputs.map(i => (i.value || '').replace(/\D/g,'')).join('').slice(0,6);
            hidden.value = code;
            submitBtn && (submitBtn.disabled = code.length !== 6);
        };

        // Auto-advance, backspace, and digit-only
        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                input.value = input.value.replace(/\D/g,'').slice(0,1);
                if (input.value && idx < inputs.length - 1) inputs[idx+1].focus();
                updateHidden();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    inputs[idx-1].focus();
                    inputs[idx-1].value = '';
                    updateHidden();
                }
            });
            input.addEventListener('paste', (e) => {
                const text = (e.clipboardData || window.clipboardData).getData('text');
                if (!text) return;
                e.preventDefault();
                const digits = text.replace(/\D/g,'').slice(0,6).split('');
                inputs.forEach((i, j) => i.value = digits[j] || '');
                updateHidden();
                const last = digits.length ? Math.min(digits.length, inputs.length) - 1 : 0;
                inputs[last].focus();
            });
        });

        updateHidden();

        // Countdown + resend
        console.log('Setting up countdown with expiresIso:', expiresIso);
        
        if (!expiresIso) {
            console.log('No expiration time, enabling resend button');
            if (resendBtn) resendBtn.disabled = false;
            if (countdownEl) countdownEl.textContent = 'Kode kadaluarsa';
        } else {
            console.log('Setting up countdown timer');
            const end = new Date(expiresIso).getTime();
            console.log('Countdown end time:', new Date(end));
            
            const tick = () => {
                const now = Date.now();
                const diff = Math.max(0, end - now);
                const s = Math.floor(diff/1000);
                const m = String(Math.floor(s/60)).padStart(2,'0');
                const ss = String(s%60).padStart(2,'0');
                
                console.log('Countdown tick:', { diff, m, ss });
                
                // Update main countdown
                if (countdownEl) {
                    countdownEl.textContent = diff>0 ? `Kedaluwarsa dalam ${m}:${ss}` : 'Kode kadaluarsa';
                }
                
                // Update dynamic countdown in notification
                const dynamicCountdown = document.getElementById('dynamicCountdown');
                if (dynamicCountdown) {
                    if (diff > 0) {
                        dynamicCountdown.textContent = ` (Berlaku ${m}:${ss})`;
                    } else {
                        dynamicCountdown.textContent = ' (Kode kadaluarsa)';
                    }
                }
                
                if (resendBtn) resendBtn.disabled = diff>0;
                if (diff>0) requestAnimationFrame(tick);
            };
            tick();
        }

        resendBtn && resendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (resendBtn.disabled) return;
            resendBtn.disabled = true;
            resendText.textContent = 'Mengirim...';
            resendSpinner.classList.remove('d-none');
            resendForm.submit();
        });
    });
    </script>

