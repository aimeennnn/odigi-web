<!-- latest jquery-->
<script src="{{asset('assets/js/jquery-3.6.3.min.js')}}"></script>

<!-- Bootstrap js-->
<script src="{{asset('assets/vendor/bootstrap/bootstrap.bundle.min.js')}}"></script>

<!-- Simple bar js-->
<script src="{{asset('assets/vendor/simplebar/simplebar.js')}}"></script>

<!-- phosphor js -->
<script src="{{asset('assets/vendor/phosphor/phosphor.js')}}"></script>

<!-- Customizer js-->
<script src="{{asset('assets/js/customizer.js')}}"></script>

<!-- prism js-->
<script src="{{asset('assets/vendor/prism/prism.min.js')}}"></script>

<!-- App js-->
<script src="{{asset('assets/js/script.js')}}"></script>

<!-- Summernote -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
<link href="{{asset('assets/css/summernote-toolbar-fix.css')}}" rel="stylesheet">
<link href="{{asset('assets/css/summernote-dropdown-fix.css')}}" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script src="{{asset('assets/js/summernote-dropdown-fix.js')}}"></script>

<!-- Summernote Debug Script (only in development) -->
@if(config('app.debug'))
<script src="{{asset('assets/js/summernote-debug.js')}}"></script>
@endif

@yield('script')
