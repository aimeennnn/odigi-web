@php
    $sort = request('sort', '');
    $order = request('order', 'asc');
    $sort_link = function($col, $label) {
        $sort = request('sort', '');
        $order = request('order', 'asc');
        $isActive = $sort === $col;
        $nextOrder = $isActive && $order === 'asc' ? 'desc' : 'asc';
        $upColor = $isActive && $order === 'asc' ? 'style="color:#4F8CFF;font-weight:bold;"' : 'style="color:#bbb;"';
        $downColor = $isActive && $order === 'desc' ? 'style="color:#4F8CFF;font-weight:bold;"' : 'style="color:#bbb;"';
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
    <!-- Header Card Gradient -->
    <div class="gradient-header-slik p-4 mb-4 d-flex align-items-center justify-content-between">
    <!-- <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #4F8CFF 0%, #b2f2e5 100%); border-radius: 18px; min-height: 100px;"> -->
        <div>
            <h2 class="fw-bold mb-1 text-white">Data Pemeriksaan SLIK Nasabah</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Daftar hasil analisa riwayat kredit nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.25;"><i class="bi bi-shield-check"></i></span>
    </div>
    
    <!-- TAB NAVIGATION -->
    @include('partials.tab-navigation')
    
    <!-- TOMBOL AKSI -->
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @php
            $regEnc = session('current_register_encrypted_id');
            $regRaw = session('current_register_id');
            $q = request('register_id');
            if(!$regEnc){
                $candidate = $q ?: $regRaw;
                if($candidate){
                    try{ \Illuminate\Support\Facades\Crypt::decryptString($candidate); $regEnc = $candidate; }
                    catch(\Exception $e){ $regEnc = \Illuminate\Support\Facades\Crypt::encryptString($candidate); }
                }
            }
            if($regEnc){ $regEnc = strtr($regEnc, ['+' => '-', '/' => '_', '=' => '.']); }
        @endphp
        @if($regEnc)
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalDokumenRegister">
                <i class="bi bi-file-earmark-text me-1"></i>Dokumen
            <!-- </button>
            <a href="{{ route('register.show', $regEnc) }}" class="btn btn-dark">Kembali</a>
        @else
            <a href="{{ route('register.index') }}" class="btn btn-dark">Kembali</a>
        @endif -->
    </div>
    <!-- Card Filter -->
    <div class="card mb-3" style="border-radius: 18px;">
        <div class="card-body pb-2">
            <div class="mb-3 fw-bold d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                <i class="bi bi-funnel"></i> Filter Arsip
            </div>
            <form class="row align-items-end g-3">
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <div class="col-md-6">
                    <label for="filter_nama" class="form-label">Nama Nasabah</label>
                    <input type="text" class="form-control" id="filter_nama" name="filter_nama" placeholder="CARI NAMA NASABAH..." style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label for="filter_no_identitas" class="form-label">No Identitas</label>
                    <input type="text" class="form-control" id="filter_no_identitas" name="filter_no_identitas" placeholder="CARI NO IDENTITAS..." style="text-transform: uppercase;">
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-slik-blue"><i class="bi bi-search me-1"></i>Filter</button>
                    @if(request('register_id'))
                        <a href="{{ route('slik.index', ['register_id' => request('register_id')]) }}" class="btn btn-slik-danger"><i class="bi bi-x-circle me-1"></i>Reset</a>
                    @else
                        <a href="{{ route('slik.index') }}" class="btn btn-slik-danger"><i class="bi bi-x-circle me-1"></i>Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Tambah Data SLIK -->
    @include('slik._modal')
    <!-- Toast Notification untuk Success -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tombol dan Tabel Data SLIK -->
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3 gap-2">
                @if(\App\Helpers\RoleHelper::canCreate('slik'))
                    <button class="btn btn-slik" data-bs-toggle="modal" data-bs-target="#modalTambahSlik"><i class="bi bi-plus-circle me-1"></i>Tambah Data</button>
                @endif
                @if(\App\Helpers\RoleHelper::canDelete('slik'))
                    <button id="btnBulkSlik" type="button" class="btn btn-slik-danger" data-bs-toggle="modal" data-bs-target="#modalHapusSlik"><i class="bi bi-trash me-1"></i>Hapus</button>
                @endif
                <!-- <button class="btn btn-slik-yellow"><i class="bi bi-download me-1"></i>Ekspor Data Terpilih</button> -->
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
                    <input type="text" class="form-control" placeholder="CARI DATA SLIK..." id="searchSlik" style="text-transform: uppercase;" autocomplete="off">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <thead style="background: #e3f0ff;">
                        <tr>
                            <th scope="col">Pilih</th>
                            <!-- <th scope="col">No</th> -->
                            <th scope="col">{!! $sort_link('nomor', 'Nomor SLIK') !!}</th>
                            <th scope="col">{!! $sort_link('tanggal', 'Tanggal') !!}</th>
                            <!-- <th scope="col">Nomor Register</th> -->
                            <th scope="col">{!! $sort_link('nama', 'Nama Nasabah') !!}</th>
                            <th scope="col">{!! $sort_link('no_identitas', 'No Identitas') !!}</th>
                            <!-- <th scope="col">{!! $sort_link('keterkaitan', 'Keterkaitan') !!}</th> -->
                            <th scope="col">{!! $sort_link('hasil', 'Hasil') !!}</th>
                            <th scope="col">Hasil 2</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sliks as $i => $slik)
                        <tr>
                            <td><input type="checkbox" value="{{ $slik->id_slik }}"></td>
                            <!-- <td>{{ $sliks->firstItem() + $i }}</td> -->
                            <td class="text-start">{{ $slik->nomor }}</td>
                            <td>{{ \Carbon\Carbon::parse($slik->tgl)->translatedFormat('d F Y') }}</td>
                            <!-- <td>{{ $slik->register ? $slik->register->nomor : '-' }}</td> -->
                            <td class="text-start">{{ $slik->nama }}</td>
                            <td class="text-start">{{ $slik->no_identitas }}</td>
                            <!-- <td>{{ $slik->keterkaitan }}</td> -->
                            <td>
                                @php
                                    // Parse hasil sebagai JSON array
                                    $hasilFiles = [];
                                    if ($slik->hasil) {
                                        $parsed = is_string($slik->hasil) ? json_decode($slik->hasil, true) : $slik->hasil;
                                        if (is_array($parsed)) {
                                            $hasilFiles = $parsed;
                                        } elseif (is_string($slik->hasil)) {
                                            // Backward compatibility
                                            $hasilFiles = [$slik->hasil];
                                        }
                                    }
                                @endphp
                                @if(count($hasilFiles) > 0)
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        @foreach($hasilFiles as $index => $fileData)
                                            @php
                                                // Handle format baru (object) atau format lama (string)
                                                if (is_array($fileData) && isset($fileData['path'])) {
                                                    $filePath = $fileData['path'];
                                                } else {
                                                    $filePath = $fileData;
                                                }
                                            @endphp
                                            @if($filePath)
                                                @php
                                                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                    $isImage = in_array($ext, ['jpg','jpeg','png']);
                                                    // gunakan URL terenkripsi dengan 6 karakter acak
                                                    $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($slik->id_slik, $index, 'slik', $slik->nama);
                                                    $url = route('file.encrypted', $encryptedUrl);
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2" style="background:#f8fbff;border:1px solid #e9ecef;border-radius:12px;">
                                                    <a href="{{ $url }}" data-url="{{ $url }}" data-type="{{ $isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'other') }}" class="slik-file-link text-decoration-none" title="Lihat file">
                                                        @if($isImage)
                                                            <img src="{{ $url }}" alt="Hasil {{ $index + 1 }}" style="width:34px;height:34px;object-fit:cover;border-radius:8px;border:1px solid #e9ecef;">
                                                        @else
                                                            <i class="bi bi-file-earmark" style="font-size:1.15rem;"></i>
                                                        @endif
                                                    </a>
                                                    <span class="badge bg-success" style="font-size:0.8rem;border-radius:8px;padding:6px 12px;">
                                                        <i class="bi bi-check-circle me-1"></i>Uploaded
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                        <small class="text-muted d-block mt-1 text-center">
                                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($slik->updated_at)->format('d/m/Y H:i') }}
                                        </small>
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
                                @php
                                    // Parse hasil2 sebagai JSON array (hasil ekstraksi otomatis)
                                    $hasil2Files = [];
                                    if ($slik->hasil2) {
                                        $parsed = is_string($slik->hasil2) ? json_decode($slik->hasil2, true) : $slik->hasil2;
                                        if (is_array($parsed)) {
                                            $hasil2Files = $parsed;
                                        } elseif (is_string($slik->hasil2)) {
                                            // Backward compatibility
                                            $hasil2Files = [$slik->hasil2];
                                        }
                                    }
                                @endphp
                                @if(count($hasil2Files) > 0)
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        @foreach($hasil2Files as $index => $fileData)
                                            @php
                                                // Handle format baru (object) atau format lama (string)
                                                if (is_array($fileData) && isset($fileData['path'])) {
                                                    $filePath = $fileData['path'];
                                                } else {
                                                    $filePath = $fileData;
                                                }
                                            @endphp
                                            @if($filePath)
                                                @php
                                                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                    $isImage = in_array($ext, ['jpg','jpeg','png']);
                                                    // gunakan URL terenkripsi dengan 6 karakter acak untuk hasil2
                                                    $encryptedUrl2 = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($slik->id_slik, $index, 'slik_hasil2', $slik->nama);
                                                    $url2 = route('file.encrypted', $encryptedUrl2);
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2" style="background:#f8fbff;border:1px solid #e9ecef;border-radius:12px;">
                                                    <a href="{{ $url2 }}" data-url="{{ $url2 }}" data-type="{{ $isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'other') }}" class="slik-file-link text-decoration-none" title="Lihat file">
                                                        @if($isImage)
                                                            <img src="{{ $url2 }}" alt="Hasil 2 {{ $index + 1 }}" style="width:34px;height:34px;object-fit:cover;border-radius:8px;border:1px solid #e9ecef;">
                                                        @else
                                                            <i class="bi bi-file-earmark" style="font-size:1.15rem;"></i>
                                                        @endif
                                                    </a>
                                                    <span class="badge bg-success" style="font-size:0.8rem;border-radius:8px;padding:6px 12px;">
                                                        <i class="bi bi-check-circle me-1"></i>Uploaded
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                        <small class="text-muted d-block mt-1 text-center">
                                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($slik->updated_at)->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2" style="background:#f8fbff;border:1px solid #e9ecef;border-radius:12px;">
                                            <span class="badge" style="background:#e3f0ff; color:#4F8CFF; font-size:0.8rem;border-radius:8px;padding:6px 12px;">
                                                <i class="bi bi-clock me-1"></i>Dalam Proses
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-3 align-items-center justify-content-center">
                                    @if(request('register_id'))
                                        @php $encS = strtr(\Illuminate\Support\Facades\Crypt::encryptString($slik->id_slik), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                                        @if(\App\Helpers\RoleHelper::canViewDetailSlik())
                                            <a href="{{ route('slik.show', $encS) }}?register_id={{ request('register_id') }}" 
                                               class="action-icon view-icon" 
                                               title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        @if(\App\Helpers\RoleHelper::canUploadSlik())
                                            <button class="action-icon upload-icon btn-upload-hasil" 
                                                    data-id="{{ $slik->id_slik }}" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalUploadHasil" 
                                                    title="Upload/Update Dokumen Hasil">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        @endif
                                        @if(\App\Helpers\RoleHelper::canDelete('slik'))
                                            <form action="{{ route('slik.destroy', $slik->id_slik) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                                                <button type="submit" class="action-icon delete-icon" title="Hapus Data">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @php $encS2 = strtr(\Illuminate\Support\Facades\Crypt::encryptString($slik->id_slik), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                                        @if(\App\Helpers\RoleHelper::canViewDetailSlik())
                                            <a href="{{ route('slik.show', $encS2) }}" 
                                               class="action-icon view-icon" 
                                               title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        @if(\App\Helpers\RoleHelper::canUploadSlik())
                                            <button class="action-icon upload-icon btn-upload-hasil" 
                                                    data-id="{{ $slik->id_slik }}" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalUploadHasil" 
                                                    title="Upload/Update Dokumen Hasil">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        @endif
                                        @if(\App\Helpers\RoleHelper::canDelete('slik'))
                                            <form action="{{ route('slik.destroy', $slik->id_slik) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-icon delete-icon" title="Hapus Data">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x" style="font-size:2.5rem; color: #4F8CFF;"></i><br>
                                <h5 class="mt-2">Data tidak ditemukan</h5>
                                <p class="text-muted">Belum ada data SLIK yang tersimpan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($sliks->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 px-3">
        <div class="pagination-info">
            <span class="text-muted" style="font-size: 14px;">Showing {{ $sliks->firstItem() ?? 0 }} to {{ $sliks->lastItem() ?? 0 }} of {{ $sliks->total() }} entries</span>
        </div>
        <div class="pagination-container">
            {{ $sliks->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    </div>
    @endif
    
    <!-- Modal Upload Hasil -->
    <div class="modal fade" id="modalUploadHasil" tabindex="-1" aria-labelledby="modalUploadHasilLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="" enctype="multipart/form-data" id="formUploadHasil" onsubmit="return validateUploadForm()">
          @csrf
          @if(request('register_id'))
              <input type="hidden" name="register_id" value="{{ request('register_id') }}">
          @endif
          <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #4F8CFF 0%, #b2f2e5 100%);">
              <h5 class="modal-title text-white" id="modalUploadHasilLabel">Upload Dokumen Hasil SLIK</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-bold text-primary">Pilih File Dokumen</label>
                <input type="file" class="form-control" name="hasil" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="form-text">Format yang didukung: PDF, JPG, JPEG, PNG</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary px-4"><i class="bi bi-upload me-1"></i>Upload</button>
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
            </div>
          </div>
        </form>
      </div>
    </div>
</div>
<!-- Modal Preview Gambar untuk tabel SLIK -->
<div class="modal fade" id="slikImagePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Preview Gambar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img id="slikPreviewImage" src="" alt="Preview" style="width:100%; height:auto; border-radius:8px;" />
      </div>
    </div>
  </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/slik_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/slik-index.js') }}"></script>
<script src="{{ asset('assets/js/slik-modal.js') }}"></script>
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

<!-- Hidden form untuk bulk delete -->
<form id="slikBulkDeleteForm" method="POST" action="{{ route('slik.bulkDestroy') }}" style="display:none;">
  @csrf
  @method('DELETE')
  @if(request('register_id'))
    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
  @endif
  <input type="hidden" name="ids" id="slikBulkIdsHidden" value="">
  <button type="submit">Submit</button>
  <button type="button" id="slikBulkDeleteSubmit" style="display:none;"></button>
</form>

<!-- Modal Dokumen Register -->
@if($regEnc)
    @php
        try {
            $registerId = Crypt::decryptString($regEnc);
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
                                                <div class="info-value">{{ $register->jns_kelamin }}</div>
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
                                                <div class="info-value">{{ $register->jns_identitas }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <div class="info-label">Pekerjaan</div>
                                                <div class="info-value">{{ $register->pekerjaan }}</div>
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
                                                <div class="info-value">{{ $register->jns_pengajuan }}</div>
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
                                                <div class="info-value">{{ $register->jaminan }}</div>
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
                    <a href="{{ route('register.show', $regEnc) }}" class="btn btn-primary">
                        <i class="bi bi-eye me-1"></i>Lihat Detail Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif
@endpush