@php
    $sort = request('sort', '');
    $order = request('order', 'asc');
    $sort_link = function($col, $label) {
        $sort = request('sort', '');
        $order = request('order', 'asc');
        $isActive = $sort === $col;
        $nextOrder = $isActive && $order === 'asc' ? 'desc' : 'asc';
        $upColor = $isActive && $order === 'asc' ? 'style="color:#1dd1a1;font-weight:bold;"' : 'style="color:#bbb;"';
        $downColor = $isActive && $order === 'desc' ? 'style="color:#1dd1a1;font-weight:bold;"' : 'style="color:#bbb;"';
        $icon = '<span class="ms-1" style="font-size:1rem;vertical-align:middle;">
            <span '.$upColor.'>&uarr;</span><span '.$downColor.'>&darr;</span>
        </span>';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'order' => $nextOrder]);
        return '<a href="'.$url.'" class="text-dark fw-bold" style="text-decoration:none;">'.$label.$icon.'</a>';
    };
@endphp

@extends('layout.master')
@section('title', 'Manajemen Pengguna')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/vendor/datatable/jquery.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/pengguna_style.css') }}">
@endsection
@section('main-content')
<div class="container mt-4">
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between user-mgmt-hero">
        <div>
            <h2 class="fw-bold mb-1 title">Manajemen Pengguna</h2>
            <ul class="app-line-breadcrumbs mb-0">
                <li class=""><a href="#" class="f-s-14 f-w-500"><span><i class="ph-duotone ph-stack f-s-16"></i> Utility</span></a></li>
                <li class="active"><a href="#" class="f-s-14 f-w-500">Manajemen Pengguna</a></li>
            </ul>
        </div>
        <span style="position:absolute; top:16px; right:32px;">
            <i class="bi bi-person-circle" style="font-size:4rem; color:#fff; opacity:0.25;"></i>
        </span>
    </div>

    <!-- Toast Notification untuk Success -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tombol dan Tabel Data Manajemen Pengguna -->
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3" role="alert">
                    <strong>Gagal menyimpan.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="d-flex justify-content-end mb-3 gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahData"><i class="bi bi-plus-circle me-1"></i>Tambah User</button>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="entries-selector">
                    <label class="form-label mb-0 me-2">Show</label>
                    <select class="form-select d-inline-block w-auto mx-1" style="min-width:60px;" onchange="changePerPage(this.value)">
                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari pengguna..." value="{{ request('search') }}" onkeyup="searchUsers(this.value)">
                </div>
            </div>
            <div class="table-responsive">
                <!-- <table id="user-table" class="table align-middle table-bordered"> -->
                <table id="user-table" class="table align-middle table-bordered text-center">
                    <thead style="background: #e6f9f5;">
                        <tr>
                        <th scope="col">{!! $sort_link('nomor', 'No') !!}</th>
                            <th scope="col">{!! $sort_link('username', 'Username') !!}</th>
                            <th scope="col">{!! $sort_link('nama', 'Nama Lengkap') !!}</th>
                            <th scope="col">{!! $sort_link('nik', 'NIK') !!}</th>
                            <th scope="col">{!! $sort_link('email', 'Email') !!}</th>
                            <th scope="col">{!! $sort_link('no_hp', 'Nomor HP') !!}</th>
                            <th scope="col">{!! $sort_link('jabatan', 'Jabatan') !!}</th>
                            <th scope="col">{!! $sort_link('level', 'Level') !!}</th>
                            <th scope="col">{!! $sort_link('status', 'Status') !!}</th>
                            <th scope="col">{!! $sort_link('online', 'Status Online') !!}</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td class="text-start">{{ $user->username }}</td>
                            <td class="text-start text-uppercase">{{ $user->nama }}</td>
                            <td>{{ $user->nik }}</td>
                            <td class="text-start">{{ $user->email }}</td>
                            <td>{{ $user->no_hp }}</td>
                            <td>{{ $user->jabatan }}</td>
                            <td>
                              {{ $user->level_label ?? '-' }}
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge text-bg-success">{{ $user->status_label }}</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge text-bg-secondary">{{ $user->status_label }}</span>
                                @else
                                    <span class="badge text-bg-danger">{{ $user->status_label ?? 'Suspended' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->online)
                                    <span class="badge bg-success">Online</span>
                                @else
                                    <span class="badge bg-danger">offline</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($user->username === 'SUPERADMIN')
                                        <button type="button" class="icon-btn setting" title="Setting" data-bs-toggle="modal" data-bs-target="#modalSetting_{{ $user->id }}">
                                            <i class="ti ti-settings"></i>
                                        </button>
                                        <button type="button" class="icon-btn delete" title="Hapus" disabled>
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        <span class="badge text-bg-warning">Super Admin</span>
                                    @else
                                        <button type="button" class="icon-btn update" title="Update" data-bs-toggle="modal" data-bs-target="#modalEditUser_{{ $user->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @if($user->level == '0')
                                            <button type="button" class="icon-btn setting" title="Setting" data-bs-toggle="modal" data-bs-target="#modalSetting_{{ $user->id }}">
                                                <i class="ti ti-settings"></i>
                                            </button>
                                        @else
                                            <button type="button" class="icon-btn setting" title="Akses setting hanya untuk level 0/bukan komite" disabled style="opacity:0.5;cursor:not-allowed;">
                                                <i class="ti ti-settings"></i>
                                            </button>
                                        @endif
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn delete" title="Nonaktifkan User" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan user ini? User tidak akan bisa login lagi, namun data dan audit trail tetap tersimpan.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <!-- <td>
                                @if($user->online)
                                        </button>
                                        <button type="button" class="btn btn-light-secondary icon-btn b-r-4" title="Setting" data-bs-toggle="modal" data-bs-target="#modalSetting_{{ $user->id }}">
                                            <i class="ti ti-settings"></i>
                                        </button>
                                        <button type="button" class="btn btn-light-danger icon-btn b-r-4" title="Hapus" disabled>
                                            <i class="ti ti-trash text-muted"></i>
                                        </button>
                                        <span class="badge text-bg-warning ms-2" title="Super Admin tidak dapat diubah">Super Admin</span>
                                    @else
                                        <button type="button" class="btn btn-light-success icon-btn b-r-4" title="update" data-bs-toggle="modal" data-bs-target="#modalEditUser_{{ $user->id }}">
                                            <i class="ti ti-edit text-success"></i>
                                        </button>
                                        <button type="button" class="btn btn-light-secondary icon-btn b-r-4" title="Setting" data-bs-toggle="modal" data-bs-target="#modalSetting_{{ $user->id }}">
                                            <i class="ti ti-settings"></i>
                                        </button>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light-danger icon-btn b-r-4" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td> -->
                        </tr>
                        <!-- Include Setting Modal -->
                        @include('manajemen_pengguna.setting')
                        @if($user->username !== 'SUPERADMIN')
                            @include('manajemen_pengguna.modal_update', ['user' => $user])
                        @endif
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x" style="font-size:2.5rem; color: #1dd1a1;"></i><br>
                                <h5 class="mt-2">Data tidak ditemukan</h5>
                                <p class="text-muted">Belum ada data pengguna yang tersimpan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination - Moved outside card -->
    @if($users->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 px-3">
        <div class="pagination-info">
            <span class="text-muted" style="font-size: 14px;">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries</span>
        </div>
        <div class="pagination-container">
    {{ $users->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
    </div>
    @endif
</div>

    @include('manajemen_pengguna.modal')
@endsection

@section('script')
<script src="{{asset('assets/vendor/datatable/jquery.dataTables.min.js')}}"></script>
<!-- JavaScript telah dipindahkan ke public/assets/js/manajemen_pengguna-index.js -->
<script src="{{ asset('assets/js/manajemen_pengguna-index.js') }}"></script>
<!-- JavaScript untuk modal manajemen pengguna -->
<script src="{{ asset('assets/js/manajemen_pengguna-modal.js') }}"></script>
<!-- JavaScript untuk modal update manajemen pengguna -->
<script src="{{ asset('assets/js/manajemen_pengguna-modal_update.js') }}"></script>
<!-- JavaScript untuk setting manajemen pengguna -->
<script src="{{ asset('assets/js/manajemen_pengguna-setting.js') }}"></script>
<script>
    // Initialize with error state from Laravel
    @if ($errors->any())
        ManajemenPengguna.initializeWithErrors(true);
    @else
        ManajemenPengguna.initializeWithErrors(false);
    @endif
    
    // Auto hide success notification if exists
    @if (session('success'))
        ManajemenPengguna.initSuccessNotification();
    @endif
</script>
@endsection