<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'App')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Tambahkan link CSS lain jika perlu -->
</head>
<body>
    <div class="d-flex">
        @include('layout.sidebar')
        <div class="main-content flex-grow-1 p-3" style="min-height:100vh; background:#f8f9fa; margin-left:17rem;">
            @yield('content')
        </div>
    </div>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <!-- Tambahkan script JS lain jika perlu -->
</body>
</html> 