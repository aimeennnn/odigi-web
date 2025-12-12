@extends('layout.master')
@section('main-content')
<!-- Tambahkan link ke CSS data -->
<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
<div class="container mt-4">
    
    @php
        // Pastikan variabel data tersedia
        if (!isset($data) || !$data) {
            abort(404, 'Data tidak ditemukan');
        }
    @endphp
    
    <!-- Header Gradient -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Detail Data Tambahan</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Informasi detail data tambahan nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-file-earmark-text"></i></span>
    </div>
    
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @if(request('register_id'))
            <a href="{{ route('data.index', ['register_id' => request('register_id')]) }}" class="btn" style="background:#3d3935; color:#fff; font-weight:500;">Kembali ke Data</a>
        @else
            <a href="{{ route('data.index') }}" class="btn" style="background:#3d3935; color:#fff; font-weight:500;">Kembali ke Data</a>
        @endif
        @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($data->id_data), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        @if(\App\Helpers\RoleHelper::canEdit('data'))
            <a href="{{ route('data.edit.simple') }}?id={{ $encId }}{{ request('register_id') ? '&register_id='.request('register_id') : '' }}" class="btn" style="background:#f4cb4b; color:#222; font-weight:500;">Ubah Data</a>
        @endif
        @if(\App\Helpers\RoleHelper::canDelete('data'))
            <form action="{{ route('data.destroy', $data->id_data) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data?')">
                @csrf
                @method('DELETE')
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <button class="btn" style="background:#ff624d; color:#fff; font-weight:500;">Hapus</button>
            </form>
        @endif
    </div>
    
    @if($data->register)
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head d-flex align-items-center justify-content-between">
            <div><h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Info Nasabah</h5></div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3 avatar-circle">{{ strtoupper(substr($data->register->nama,0,1)) }}</div>
                <div>
                    <h2 class="fw-bold mb-0" style="letter-spacing:1px; color:#162447;">{{ $data->register->nama }}</h2>
                    <!-- <span class="badge d-inline-flex align-items-center" style="background:#e3f0ff; color:#49bbca; font-size:1rem;" data-bs-toggle="tooltip" title="ID Register">
                        <i class="bi bi-person-badge me-1"></i> {{ $data->register->id_reg }}
                    </span> -->
                    <div class="text-muted small mt-1">Diinput oleh: {{ $data->register->input_by }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item"><div class="info-label">Nomor Registrasi</div><div class="info-value">{{ $data->register->nomor }}</div></div>
                @php $isBU = ($data->register->jenis_entitas ?? '') === 'badan_usaha'; @endphp
                <div class="info-item">
                    <div class="info-label">{{ $isBU ? 'Nomor Legalitas Usaha' : 'No Identitas' }}</div>
                    <div class="info-value">{{ $isBU ? ($data->register->nomor_legalitas_usaha ?? '-') : ($data->register->no_identitas ?? '-') }}</div>
                </div>
                <div class="info-item"><div class="info-label">Nama Lengkap</div><div class="info-value">{{ $data->register->nama }}</div></div>
                <div class="info-item"><div class="info-label">Nominal Pengajuan</div><div class="info-value">Rp {{ number_format($data->register->nominal_pengajuan,0,',','.') }}</div></div>
                <div class="info-item"><div class="info-label">Tanggal Pengajuan</div><div class="info-value">{{ $data->register->tgl_pengajuan }}</div></div>
                <div class="info-item"><div class="info-label">Status Register</div><div class="info-value">{{ $data->register->status }}</div></div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Data Tambahan</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">ID Data</div>
                    <div class="info-value">{{ $data->id_data }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ID Register</div>
                    <div class="info-value">{{ $data->id_reg }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Jenis Data</div>
                    <div class="info-value">{{ $data->jenis_data }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nama Nasabah</div>
                    <div class="info-value">{{ $data->register->nama ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nomor Register</div>
                    <div class="info-value">{{ $data->register->nomor ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Dibuat</div>
                    <div class="info-value">{{ $data->created_at ? $data->created_at->format('d/m/Y H:i') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KETERANGAN -->
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-card-text me-2"></i>Keterangan</h5>
        </div>
        <div class="card-body">
            <div class="note-box">{{ $data->keterangan ?? '-' }}</div>
        </div>
    </div>

    <!-- FILES -->
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark me-2"></i>Dokumen</h5>
        </div>
        <div class="card-body">
            @php
                use Illuminate\Support\Str;
                use Illuminate\Support\Facades\Storage;
                $files = $data->file ? (json_decode($data->file, true) ?: [$data->file]) : [];
                $resolveUrl = function ($path) {
                    if (!$path) return null;
                    $path = is_string($path) ? trim($path) : $path;
                    if (!$path) return null;
                    if (Str::startsWith($path, ['http://','https://'])) return $path;
                    $path = ltrim($path, '/');
                    // If only filename is stored, assume default directory
                    if (strpos($path, '/') === false) {
                        $path = 'data/files/' . $path;
                    }
                    if (Str::startsWith($path, 'public/')) {
                        $path = substr($path, 7);
                    }
                    if (Str::startsWith($path, 'storage/')) {
                        return asset($path);
                    }
                    // Default: assume path is stored on public disk
                    return Storage::url($path);
                };
            @endphp
            @if(count($files))
                <div class="row g-3">
                    @foreach($files as $p)
                        @php
                            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png']);
                            $isPdf = $ext === 'pdf';
                            // gunakan URL terenkripsi dengan 6 karakter acak
                            $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($data->id_data, $loop->index, 'data', $data->jenis_data);
                            $url = route('file.encrypted', $encryptedUrl);
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="file-card p-3 h-100 d-flex flex-column">
                                <div class="thumb-wrapper">
                                    @if($isImage)
                                        <img src="{{ $url }}" alt="Preview" class="file-thumb" aria-hidden="true" loading="lazy" />
                                    @else
                                        <div class="thumb-placeholder">
                                            <i class="bi bi-file-earmark-text" style="font-size:2rem"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="file-title text-truncate mb-2" title="{{ basename($p) }}"><i class="bi bi-file-earmark me-1"></i>{{ basename($p) }}</div>
                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ $url }}" class="btn btn-outline-success btn-sm btn-view-file" data-url="{{ $url }}" data-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"><i class="bi bi-eye me-1"></i>Lihat</a>
                                    <a href="{{ $url }}?download=1" class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i>Download</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-drop">
                    <div class="ico mb-2"><i class="bi bi-file-earmark-x"></i></div>
                    <div class="fw-semibold mb-1">Belum ada dokumen</div>
                    <div class="text-muted small">Tambahkan dokumen saat membuat/ubah data</div>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Modal Preview Gambar -->
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
<!-- JavaScript sudah dipindahkan ke file eksternal data-show.js -->
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/data-show.js') }}"></script>
@endpush