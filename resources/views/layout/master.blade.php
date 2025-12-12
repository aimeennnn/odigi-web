<!DOCTYPE html>
<html lang="en">

<head>
    <!-- All meta and title start-->
@include('layout.head')
<!-- meta and title end-->

    <!-- css start-->
@include('layout.css')
<!-- css end-->
<link rel="stylesheet" href="{{ asset('assets/css/layout_style.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
<!-- Loader start-->
<div class="app-wrapper">
    <div class="loader-wrapper">
        <div class="odigi-loader">
            <img src="{{asset('assets/image/Logo/logo_odigi.png')}}" alt="ODIGI" class="odigi-logo">
        </div>
    </div>
    <!-- Loader end-->

    <!-- Menu Navigation start -->
@include('layout.sidebar')
<!-- Menu Navigation end -->


    <div class="app-content">
        <!-- Header Section start -->
    @include('layout.header')
    <!-- Header Section end -->

        <!-- Main Section start -->
        <main>
            {{-- main body content --}}
            @yield('main-content')
        </main>
        <!-- Main Section end -->
    </div>

    <!-- tap on top -->
    <div class="go-top">
      <span class="progress-value">
        <i class="ti ti-arrow-up"></i>
      </span>
    </div>

    <!-- Footer Section start -->
     @include('layout.footer')
    <!-- Footer Section end -->
</div>
@stack('scripts')
</body>

<!--customizer-->
<div id="customizer"></div>

<!-- scripts start-->
@include('layout.script')
<!-- scripts end-->

</html>
