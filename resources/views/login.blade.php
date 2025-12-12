@section('title', 'Login')
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
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form class="app-form" method="POST" action="{{ route('login.attempt') }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-4 text-center">
                                        <h3 class="text-primary f-w-600 mb-3">Selamat Datang</h3>
                                        <div class="mb-3">
                                            <img src="{{asset('assets/image/Logo/logo_odigi.png')}}" alt="ODIGI Logo" class="img-fluid logo-animated" style="width: 300px; height: auto; max-width: 100%;">
                                        </div>
                                        <h7 class="text-secondary f-w-500 mb-2">BPR Go Digital - Digitalkan Pekerjaanmu</h7>
                                        <p class="text-muted small">Masuk dengan akun yang Anda masukkan saat registrasi</p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="username" class="form-label fw-semibold small">Username</label>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Enter Your Username" id="username" name="username" value="{{ old('username') }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="password" class="form-label fw-semibold mb-0 small">Password</label>
                                        </div>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter Your Password" id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-4">
                                        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-center">
                                        <small class="text-muted">Tidak punya akun? Silakan hubungi admin.</small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Body main section ends -->
    </div>
</div>


</body>
@section('script')
    <!-- Bootstrap js-->
    <script src="{{asset('assets/vendor/bootstrap/bootstrap.bundle.min.js')}}"></script>
@endsection

