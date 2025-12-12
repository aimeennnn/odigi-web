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
@section('main-content')
<div class="container mt-4">
    <div class="gradient-header-data p-4 mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">Data Nasabah</h2>
            <div class="desc">Tabel ini digunakan untuk mencatat data tambahan/dokumen nasabah</div>
        </div>
        <span class="icon"><i class="bi bi-folder2-open"></i></span>
    </div>
    
    <!-- TAB NAVIGATION -->
    @include('partials.tab-navigation')
    
    <!-- TOMBOL AKSI -->
    @php
        use Illuminate\Support\Facades\Crypt;
        // Ambil dari session agar URL bersih, fallback ke query bila ada
        $__sessionEnc = session('current_register_encrypted_id');
        $__regParam = request('register_id');
        $__encryptedId = null;
        if ($__sessionEnc) {
            $__encryptedId = $__sessionEnc;
        } elseif ($__regParam) {
            try { Crypt::decryptString($__regParam); $__encryptedId = $__regParam; }
            catch (\Exception $e) { try { $__encryptedId = Crypt::encryptString($__regParam); } catch (\Exception $e2) { $__encryptedId = null; } }
        }
    @endphp
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @if($__encryptedId)
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalDokumenRegister">
                <i class="bi bi-file-earmark-text me-1"></i>Dokumen
            </button>
            <!-- <a href="{{ route('register.show', $__encryptedId) }}" class="btn btn-dark">Kembali</a>
        @else
            <a href="{{ route('register.index') }}" class="btn btn-dark">Kembali</a>
        @endif -->
    </div>
    <div class="card mb-4 " style="border-radius: 18px; min-height: 150px;">
        <div class="card-body pb-2">
            <div class="mb-3 fw-bold d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                <i class="bi bi-funnel"></i> Filter Arsip
            </div>
            <!-- <form class="row g-3 align-items-end"> -->
            <form class="row align-items-end g-3">
                <div class="col-md-9">
                    <label for="filter_jenis" class="form-label">Jenis Data</label>
                    <select class="form-select" id="filter_jenis" name="filter_jenis">
                        <option value="">Pilih jenis data...</option>
                        @foreach(\App\Models\Data::jenisDataList() as $key => $label)
                            <option value="{{ $key }}" {{ request('filter_jenis') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-data-blue w-100"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('data.index') }}" class="btn btn-data-danger w-100">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    <!-- Toast Notification untuk Success -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3 gap-2">
                @if(\App\Helpers\RoleHelper::canCreate('data'))
                    <button class="btn btn-data" data-bs-toggle="modal" data-bs-target="#modalTambahData"><i class="bi bi-plus-circle me-1"></i>Tambah Data</button>
                @endif
                @if(\App\Helpers\RoleHelper::canDelete('data'))
                    <button id="btnBulkData" type="button" class="btn btn-data-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                @endif
                <!-- <button class="btn btn-data-yellow" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button> -->
                <!-- <button class="btn btn-data-yellow"><i class="bi bi-download me-1"></i>Ekspor Data Terpilih</button> -->
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <label class="form-label mb-0 me-2">Show
                        <select class="form-select d-inline-block w-auto mx-1" style="min-width:60px;" onchange="changePerPage(this.value)">
                            <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 5) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 5) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 5) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        entries
                    </label>
                </div>
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="CARI ARSIP..." id="searchData" style="text-transform: uppercase;" autocomplete="off">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Pilih</th>
                            <th scope="col">Nomor</th>
                            <!-- <th scope="col">Nomor Register</th> -->
                            <th scope="col">{!! $sort_link('jenis_data', 'Jenis Data') !!}</th>
                            <th scope="col">{!! $sort_link('keterangan', 'Keterangan') !!}</th>
                            <th scope="col">{!! $sort_link('file', 'File') !!}</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $item)
                        <tr>
                            <td><input type="checkbox" value="{{ $item->id_data }}"></td>
                            <td class="text-start">{{ $data->firstItem() + $i }}</td>
                            <!-- <td>{{ $item->register ? $item->register->nomor : '-' }}</td> -->
                            <td class="text-start">{{ $item->jenis_data }}</td>
                            <td class="text-start">{{ $item->keterangan }}</td>
                            <td>
                                @php $files = $item->file ? (json_decode($item->file, true) ?: []) : []; @endphp
                                @if(count($files))
                                    <div class="d-flex flex-column gap-1" style="max-width:280px; margin:0 auto;">
                                        @foreach($files as $p)
                                            @php
                                                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                                                $isImage = in_array($ext, ['jpg','jpeg','png']);
                                                // gunakan URL terenkripsi dengan 6 karakter acak
                                                $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($item->id_data, $loop->index, 'data', $item->jenis_data);
                                                $url = route('file.encrypted', $encryptedUrl);
                                            @endphp
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2" style="background:#f8fbff;border:1px solid #e9ecef;border-radius:12px;">
                                                    <a href="{{ $url }}" data-url="{{ $url }}" data-type="{{ $isImage ? 'image' : (strtolower(pathinfo($p, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'other') }}" class="data-file-link text-decoration-none" title="Lihat file" style="flex:0;">
                                                        @if($isImage)
                                                            <img src="{{ $url }}" alt="File" style="width:34px;height:34px;object-fit:cover;border-radius:8px;border:1px solid #e9ecef;">
                                                        @else
                                                            <i class="bi bi-file-earmark" style="font-size:1.15rem;"></i>
                                                        @endif
                                                        <span class="visually-hidden">{{ basename($p) }}</span>
                                                    </a>
                                                    <span class="badge bg-success" style="font-size:0.8rem;border-radius:8px;padding:6px 12px;">
                                                        <i class="bi bi-check-circle me-1"></i>Uploaded
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block mt-2 text-center">
                                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center">
                                        <span class="text-muted">
                                            <i class="bi bi-file-earmark-x me-1"></i>Belum ada file
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                 <div class="d-flex gap-2 align-items-center justify-content-center">
                                    @php 
                                        $encId = \Illuminate\Support\Facades\Crypt::encryptString($item->id_data);
                                        $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
                                    @endphp
                                    @if(\App\Helpers\RoleHelper::canViewDetailData())
                                        <a href="{{ route('data.show', $urlSafeId) }}" class="action-icon view-icon" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if(\App\Helpers\RoleHelper::canDelete('data'))
                                        <form action="{{ route('data.destroy', $item->id_data) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon delete-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x" style="font-size:2.5rem; color: #49bbca;"></i><br>
                                <h5 class="mt-2">Data tidak ditemukan</h5>
                                <p class="text-muted">Belum ada data tambahan yang tersimpan. Data akan muncul di sini setelah proses registrasi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($data->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 px-3">
        <div class="pagination-info">
            <span class="text-muted" style="font-size: 14px;">Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} entries</span>
        </div>
        <div class="pagination-container">
            {{ $data->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    </div>
    @endif
</div>
<!-- Modal Preview Gambar (reuse from show page) -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="previewImage" src="" alt="Preview" style="width:100%; height:auto; border-radius:8px;" />
            </div>
        </div>
    </div>
</div>
<!-- JavaScript sudah dipindahkan ke file eksternal data-index.js -->

<!-- Modal Dokumen Register -->
@if($__encryptedId)
    @php
        try {
            $registerId = Crypt::decryptString($__encryptedId);
            $register = \App\Models\Register::find($registerId);
        } catch (\Exception $e) {
            $register = null;
        }
    @endphp
    
    @if($register)
    <div class="modal fade" id="modalDokumenRegister" tabindex="-1" aria-labelledby="modalDokumenRegisterLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 18px;">
                <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
                    <h5 class="modal-title" id="modalDokumenRegisterLabel">
                        <i class="bi bi-file-earmark-text me-2"></i>Dokumen Register
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 2rem;">
                    <div class="row g-4">
                        <!-- Informasi Dasar -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                                <div class="card-header bg-light" style="border-radius: 15px 15px 0 0;">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-info-circle me-2"></i>Informasi Dasar
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nomor Register</div>
                                                <div class="info-value">{{ $register->nomor }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Tanggal Pengajuan</div>
                                                <div class="info-value">{{ \Carbon\Carbon::parse($register->tgl_pengajuan)->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jenis Entitas</div>
                                                <div class="info-value">
                                                    <span class="badge {{ $register->jenis_entitas == 'perorangan' ? 'bg-primary' : 'bg-success' }}">
                                                        {{ $register->jenis_entitas == 'perorangan' ? '👤 Perorangan' : '🏢 Badan Usaha' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nama Penginput</div>
                                                <div class="info-value">{{ $register->input_by }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($register->jenis_entitas == 'perorangan')
                        <!-- Data Pribadi -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                                <div class="card-header bg-light" style="border-radius: 15px 15px 0 0;">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-person me-2"></i>Data Pribadi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nama Lengkap</div>
                                                <div class="info-value">{{ $register->nama }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jenis Kelamin</div>
                                                <div class="info-value">{{ \App\Models\Register::jenisKelaminList()[$register->jns_kelamin] ?? $register->jns_kelamin }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nomor Identitas</div>
                                                <div class="info-value">{{ $register->no_identitas }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jenis Identitas</div>
                                                <div class="info-value">{{ \App\Models\Register::jenisIdentitasList()[$register->jns_identitas] ?? $register->jns_identitas }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Pekerjaan</div>
                                                <div class="info-value">{{ \App\Models\Register::pekerjaanList()[$register->pekerjaan] ?? $register->pekerjaan }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Alamat</div>
                                                <div class="info-value">{{ $register->alamat }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Data Badan Usaha -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                                <div class="card-header bg-light" style="border-radius: 15px 15px 0 0;">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-building me-2"></i>Data Badan Usaha
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nama Badan Usaha</div>
                                                <div class="info-value">{{ $register->nama_badan_usaha }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jenis Dokumen Usaha</div>
                                                <div class="info-value">{{ $register->jenis_dokumen_usaha }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nomor Legalitas Usaha</div>
                                                <div class="info-value">{{ $register->nomor_legalitas_usaha }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Bidang Usaha</div>
                                                <div class="info-value">{{ $register->bidang_usaha }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-item">
                                                <div class="info-label">Alamat Usaha</div>
                                                <div class="info-value">{{ $register->alamat_usaha }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Pengajuan Kredit -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                                <div class="card-header bg-light" style="border-radius: 15px 15px 0 0;">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-credit-card me-2"></i>Pengajuan Kredit
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jenis Pengajuan</div>
                                                <div class="info-value">{{ $register->jenis_pengajuan_label }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Nominal Pengajuan</div>
                                                <div class="info-value">Rp {{ number_format($register->nominal_pengajuan, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Jangka Waktu</div>
                                                <div class="info-value">{{ $register->jw_pengajuan }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Status</div>
                                                <div class="info-value">
                                                    <span class="badge bg-warning">Dalam Proses</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-item">
                                                <div class="info-label">Jaminan</div>
                                                <div class="info-value">{{ \App\Models\Register::jaminanList()[$register->jaminan] ?? $register->jaminan }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e6f9f5;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Tutup
                    </button>
                    <a href="{{ route('register.show', $__encryptedId) }}" class="btn btn-primary">
                        <i class="bi bi-eye me-1"></i>Lihat Detail Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

<style>
.info-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.5rem;
}

.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
}

.modal-xl {
    max-width: 1200px;
}
</style>

@include('data._modal')
@include('data._modal_upload')
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/data-index.js') }}"></script>
<script src="{{ asset('assets/js/data-modal.js') }}"></script>
@if (session('success'))
<script>
// Auto hide success notification setelah 3 detik
setTimeout(function() {
    const notification = document.querySelector('.alert.position-fixed');
    if (notification && notification.parentNode) {
        notification.remove();
    }
}, 3000);
</script>
@endif
@endpush