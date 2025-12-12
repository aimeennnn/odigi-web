@extends('layout.master')

@section('main-content')
<!-- Tambahkan link ke CSS register -->
<link rel="stylesheet" href="{{ asset('assets/css/register_style.css') }}">
<div class="container mt-4">
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Detail Register</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Data Registrasi SLIK OJK & Pengajuan</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-journal-check"></i></span>
    </div>
    
    <!-- TOMBOL AKSI -->
    <div class="mb-3 d-flex gap-2 justify-content-end">
        <a href="{{ route('register.index') }}" class="btn btn-dark">Kembali</a>
        @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($register->id_reg), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        @if(\App\Helpers\RoleHelper::canEdit('register'))
            <a href="{{ route('register.edit.simple') }}?id={{ $encId }}" class="btn btn-warning">Ubah Data</a>
        @endif
        @if(\App\Helpers\RoleHelper::canDelete('register'))
            <form action="{{ route('register.destroy', $encId) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Hapus</button>
            </form>
        @endif
    </div>
    
    <!-- TAB NAVIGATION -->
    @include('partials.tab-navigation')
    
    <div class="tab-content" id="registerTabContent">
        <!-- INFO UMUM -->
        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
            <!-- Header dengan Avatar -->
            <div class="card mb-4 info-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background:linear-gradient(135deg,#1dd1a1,#49bbca); color:#fff; font-size:2.5rem; box-shadow:0 4px 12px rgba(76,222,212,0.2);">
                                {{ strtoupper(substr($register->nama,0,1)) }}
                            </div>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1" style="letter-spacing:1px; color:#162447;">{{ $register->nama }}</h2>
                            <div class="status-badge d-inline-block">
                                <i class="bi bi-check-circle me-1"></i>{{ $register->status_label }}
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="bi bi-person-badge me-1"></i>Diinput oleh: {{ $register->input_by }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Dasar -->
            <div class="card mb-4 info-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <span style="font-size:1.2em;">🧾</span> Informasi Dasar
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-hash me-2"></i>Nomor Registrasi
                                </div>
                                <div class="info-value">{{ $register->nomor }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar-event me-2"></i>Tanggal Pengajuan
                                </div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($register->tgl_pengajuan)->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-file-earmark-text me-2"></i>Jenis Pengajuan
                                </div>
                                <div class="info-value">{{ $register->jenis_pengajuan_label }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-check-circle me-2"></i>Status
                                </div>
                                <div class="info-value status-badge">{{ $register->status_label }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-tag me-2"></i>Jenis Entitas
                                </div>
                                <div class="info-value">
                                    @if($register->jenis_entitas === 'perorangan')
                                        <span class="badge bg-primary px-3 py-2">Perorangan</span>
                                    @else
                                        <span class="badge bg-success px-3 py-2">Badan Usaha</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-person-circle me-2"></i>Nama Penginput
                                </div>
                                <div class="info-value">{{ $register->input_by }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($register->jenis_entitas === 'perorangan')
            <!-- Data Pribadi -->
            <div class="card mb-4 info-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <span style="font-size:1.2em;">👤</span> Data Pribadi
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-person me-2"></i>Nama Lengkap
                                </div>
                                <div class="info-value highlight-value">{{ $register->nama }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin
                                </div>
                                <div class="info-value">{{ \App\Models\Register::jenisKelaminList()[$register->jns_kelamin] ?? $register->jns_kelamin }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-card-text me-2"></i>No Identitas
                                </div>
                                <div class="info-value">{{ $register->no_identitas }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-card-heading me-2"></i>Jenis Identitas
                                </div>
                                <div class="info-value">{{ \App\Models\Register::jenisIdentitasList()[$register->jns_identitas] ?? $register->jns_identitas }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-briefcase me-2"></i>Pekerjaan
                                </div>
                                <div class="info-value">{{ \App\Models\Register::pekerjaanList()[$register->pekerjaan] ?? $register->pekerjaan }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt me-2"></i>Alamat
                                </div>
                                <div class="info-value">{{ $register->alamat }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Data Badan Usaha -->
            <div class="card mb-4 info-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <span style="font-size:1.2em;">🏢</span> Data Badan Usaha
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-building me-2"></i>Nama Badan Usaha
                                </div>
                                <div class="info-value highlight-value">{{ $register->nama_badan_usaha }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-file-text me-2"></i>Jenis Dokumen Usaha
                                </div>
                                <div class="info-value">{{ $register->jenis_dokumen_usaha }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-hash me-2"></i>Nomor Legalitas Usaha
                                </div>
                                <div class="info-value">{{ $register->nomor_legalitas_usaha }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-briefcase me-2"></i>Bidang Usaha
                                </div>
                                <div class="info-value">{{ $register->bidang_usaha }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt me-2"></i>Alamat Usaha
                                </div>
                                <div class="info-value">{{ $register->alamat_usaha }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Pengajuan Kredit -->
            <div class="card mb-4 info-card pengajuan-kredit-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <span style="font-size:1.2em;">💰</span> Pengajuan Kredit
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 pengajuan-kredit-row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-currency-dollar me-2"></i>Nominal Pengajuan
                                </div>
                                <div class="info-value">
                                    <span class="badge bg-primary-subtle text-primary fw-bold p-2">
                                        <i class="bi bi-cash-coin me-1"></i>Rp {{ number_format($register->nominal_pengajuan,0,',','.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar-range me-2"></i>Jangka Waktu
                                </div>
                                <div class="info-value highlight-value">{{ $register->jw_pengajuan }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-shield-check me-2"></i>Jaminan
                                </div>
                                <div class="info-value">{{ \App\Models\Register::jaminanList()[$register->jaminan] ?? $register->jaminan }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Realisasi -->
            <div class="card mb-4 info-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <span style="font-size:1.2em;">✅</span> Realisasi
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar-check me-2"></i>Tanggal Realisasi
                                </div>
                                <div class="info-value">
                                    @if($register->tanggal_realisasi)
                                        {{ \Carbon\Carbon::parse($register->tanggal_realisasi)->translatedFormat('d F Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-currency-dollar me-2"></i>Nominal Disetujui
                                </div>
                                <div class="info-value">
                                    @if($register->nominal_disetujui)
                                        <span class="badge bg-primary-subtle text-primary fw-bold p-2">
                                            <i class="bi bi-cash-coin me-1"></i>Rp {{ number_format($register->nominal_disetujui,0,',','.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($register->tanggal_realisasi || $register->nominal_disetujui)
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Informasi Realisasi:</strong> Data realisasi telah diisi melalui aksi upload oleh admin.
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Belum Ada Realisasi:</strong> Data realisasi belum diisi oleh admin.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- JavaScript untuk halaman show register -->
<script src="{{ asset('assets/js/register-show.js') }}"></script>
@endpush 