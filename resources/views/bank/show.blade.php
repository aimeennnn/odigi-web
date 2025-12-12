@extends('layout.master')

@section('main-content')
<!-- Tambahkan link ke CSS bank -->
<link rel="stylesheet" href="{{ asset('assets/css/bank_style.css') }}">
<div class="container mt-4">
    <!-- Header Gradient -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Detail Data Bank</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Informasi lengkap data bank nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-bank2"></i></span>
    </div>
    <!-- TOMBOL AKSI -->
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @if(request('register_id'))
            <a href="{{ route('bank.index', ['register_id' => request('register_id')]) }}" class="btn btn-dark">Kembali ke Data</a>
        @else
            <a href="{{ route('bank.index') }}" class="btn btn-dark">Kembali ke Data</a>
        @endif
        @php $encBankId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($bank->id_bank), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        @if(\App\Helpers\RoleHelper::canEdit('bank'))
            <a href="{{ route('bank.edit.simple') }}?id={{ $encBankId }}{{ request('register_id') ? '&register_id='.request('register_id') : '' }}" class="btn btn-warning">Ubah Data</a>
        @endif
        @if(\App\Helpers\RoleHelper::canDelete('bank'))
            <form action="{{ route('bank.destroy', $bank->id_bank) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data?')">
                @csrf
                @method('DELETE')
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <button class="btn btn-danger">Hapus</button>
            </form>
        @endif
    </div>
    <!-- INFO NASABAH -->
    @if($bank->register)
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Info Nasabah</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3 avatar-circle">{{ strtoupper(substr($bank->register->nama,0,1)) }}</div>
                <div>
                    <h2 class="fw-bold mb-0" style="letter-spacing:1px; color:#162447;">{{ $bank->register->nama }}</h2>
                    <span class="badge d-inline-flex align-items-center" style="background:#b2f2ff; color:#4F8CFF; font-size:1rem;" data-bs-toggle="tooltip" title="ID User yang menginput">
                        <i class="bi bi-person-badge me-1"></i> {{ $bank->status_label }}
                    </span>
                    <div class="text-muted small mt-1">Diinput oleh: {{ $bank->register->input_by }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item"><div class="info-label">Nomor Registrasi</div><div class="info-value">{{ $bank->register->nomor }}</div></div>
                @php $isBU = ($bank->register->jenis_entitas ?? '') === 'badan_usaha'; @endphp
                <div class="info-item">
                    <div class="info-label">{{ $isBU ? 'Nomor Legalitas Usaha' : 'No Identitas' }}</div>
                    <div class="info-value">{{ $isBU ? ($bank->register->nomor_legalitas_usaha ?? '-') : ($bank->register->no_identitas ?? '-') }}</div>
                </div>
                <div class="info-item"><div class="info-label">Nama Lengkap</div><div class="info-value">{{ $bank->register->nama }}</div></div>
                <div class="info-item"><div class="info-label">Nominal Pengajuan</div><div class="info-value">Rp {{ number_format($bank->register->nominal_pengajuan,0,',','.') }}</div></div>
                <div class="info-item"><div class="info-label">Tanggal Pengajuan</div><div class="info-value">{{ $bank->register->tgl_pengajuan }}</div></div>
                <div class="info-item"><div class="info-label">Status Register</div><div class="info-value">{{ $bank->register->status }}</div></div>
            </div>
        </div>
    </div>
    @endif
    <!-- INFO UMUM -->
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Data Bank</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-item"><div class="info-label">ID Bank</div><div class="info-value">{{ $bank->id_bank }}</div></div>
                <div class="info-item"><div class="info-label">ID Register</div><div class="info-value">{{ $bank->id_reg }}</div></div>
                <div class="info-item"><div class="info-label">Nama Bank</div><div class="info-value">{{ $bank->nama_bank }}</div></div>
                <div class="info-item"><div class="info-label">No Rekening</div><div class="info-value">{{ $bank->no_rekening }}</div></div>
                <div class="info-item"><div class="info-label">Status Pemeriksaan</div><div class="info-value">{{ $bank->status_label }}</div></div>
                <div class="info-item"><div class="info-label">Nomor Register</div><div class="info-value">{{ $bank->register->nomor ?? '-' }}</div></div>
            </div>
        </div>
    </div>
    <!-- FILES & HASIL -->
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark me-2"></i>Dokumen</h5>
        </div>
        <div class="card-body">
            @php
                $files = $bank->file ? (json_decode($bank->file, true) ?: [$bank->file]) : [];
            @endphp
            @if(count($files))
                <div class="row g-3">
                    @foreach($files as $p)
                        @php
                            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png']);
                            $isPdf = $ext === 'pdf';
                            // gunakan URL terenkripsi dengan 6 karakter acak
                            $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($bank->id_bank, $loop->index, 'bank', $bank->nama_bank);
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
    <!-- FILES & HASIL -->
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-check me-2"></i>Hasil Pemeriksaan</h5>
        </div>
        <div class="card-body">
            @if($bank->hasil)
                @php
                    $ext = strtolower(pathinfo($bank->hasil, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg','jpeg','png']);
                    $isPdf = $ext === 'pdf';
                    // gunakan URL terenkripsi dengan 6 karakter acak untuk hasil file
                    $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($bank->id_bank, 0, 'bank_hasil', $bank->nama_bank);
                    $url = route('file.encrypted', $encryptedUrl);
                @endphp
                <div class="file-card p-3 h-100 d-flex flex-column" style="max-width:400px;">
                    <div class="thumb-wrapper">
                        @if($isImage)
                            <img src="{{ $url }}" alt="Preview Hasil" class="file-thumb" aria-hidden="true" loading="lazy" />
                        @else
                            <div class="thumb-placeholder">
                                <i class="bi bi-file-earmark-text" style="font-size:2rem"></i>
                            </div>
                        @endif
                    </div>
                    <div class="file-title text-truncate mb-2" title="{{ basename($bank->hasil) }}"><i class="bi bi-file-earmark me-1"></i>{{ basename($bank->hasil) }}</div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ $url }}" class="btn btn-outline-success btn-sm btn-view-file" data-url="{{ $url }}" data-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"><i class="bi bi-eye me-1"></i>Lihat</a>
                        <a href="{{ $url }}?download=1" class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i>Download</a>
                    </div>
                </div>
            @else
                <div class="empty-drop">
                    <div class="ico mb-2"><i class="bi bi-file-earmark-x"></i></div>
                    <div class="fw-semibold mb-1">Belum ada hasil pemeriksaan</div>
                    <div class="text-muted small">Upload hasil pemeriksaan pada menu ubah data</div>
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
<!-- JavaScript sudah dipindahkan ke file eksternal bank-show.js -->
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/bank_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/bank-show.js') }}"></script>
@endpush