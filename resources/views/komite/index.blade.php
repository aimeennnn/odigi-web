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

@php
    // Pastikan variabel komites dan registers tersedia
    $komites = isset($komites) ? $komites : collect();
    $registers = isset($registers) ? $registers : collect();
@endphp

<div class="container mt-4">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="gradient-header-komite p-4 mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">Data Komite</h2>
            <div class="desc">Manajemen data memo komite</div>
        </div>
        <span class="icon"><i class="bi bi-clipboard-data"></i></span>
    </div>
    
    <!-- TAB NAVIGATION -->
    @include('partials.tab-navigation')
    
    <!-- TOMBOL AKSI (DIPINDAHKAN KE ATAS DETAIL REGISTER) -->
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
        <!-- @if(\App\Helpers\RoleHelper::canCreate('komite'))
            <button class="btn btn-komite" data-bs-toggle="modal" data-bs-target="#modalTambahKomite"><i class="bi bi-plus-circle me-1"></i>Tambah Data</button>
        @endif
        @if(\App\Helpers\RoleHelper::canDelete('komite'))
            <button id="btnBulkKomite" type="button" class="btn btn-komite-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
        @endif -->
    </div>
    
    <!-- HEADER INFO SINGKAT -->
    @if($register)
    <div class="card mb-4 info-card" style="border-radius: 18px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-2">
                <div class="me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background:linear-gradient(135deg,#1dd1a1,#49bbca); color:#fff; font-size:2.5rem; box-shadow:0 4px 12px rgba(76,222,212,0.2);">
                        {{ strtoupper(substr($register->nama,0,1)) }}
            </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-1" style="letter-spacing:1px; color:#162447;">{{ $register->nama }}</h2>
                    <div class="text-muted small mt-2">
                        <i class="bi bi-person-badge me-1"></i>Diinput oleh: {{ $register->input_by }}
                    </div>
                </div>
                </div>
        </div>
    </div>
    @endif
    <!-- DETAIL REGISTER SINGKAT -->
    @if($register)
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body register-detail-uppercase">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Nomor Register</label>
                        <div class="col-sm-7 d-flex align-items-center register-detail-value">{{ $register->nomor ?? '-' }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Jenis Pengajuan</label>
                        <div class="col-sm-7 d-flex align-items-center">
                            {{ $register->jenis_pengajuan_label ?? '-' }}
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Nama Petugas</label>
                        <div class="col-sm-7 d-flex align-items-center">{{ $register->input_by ?? '-' }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Jenis Entitas</label>
                        <div class="col-sm-7 d-flex align-items-center">
                            @if($register->jenis_entitas === 'perorangan')
                                {{ strtoupper('Perorangan') }}
                            @elseif($register->jenis_entitas === 'badan_usaha')
                                {{ strtoupper('Badan Usaha') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">{{ ($register->jenis_entitas === 'badan_usaha') ? 'Nomor Legalitas Usaha' : 'No Identitas' }}</label>
                        <div class="col-sm-7 d-flex align-items-center register-detail-value">{{ ($register->jenis_entitas === 'badan_usaha') ? ($register->nomor_legalitas_usaha ?? '-') : ($register->no_identitas ?? '-') }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">{{ ($register->jenis_entitas === 'badan_usaha') ? 'Jenis Dokumen Usaha' : 'Jenis Identitas' }}</label>
                        <div class="col-sm-7 d-flex align-items-center register-detail-value">{{ ($register->jenis_entitas === 'badan_usaha') ? ($register->jenis_dokumen_usaha ?? '-') : ($register->jns_identitas ?? '-') }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">{{ ($register->jenis_entitas === 'badan_usaha') ? 'Nama Perusahaan' : 'Nama Nasabah' }}</label>
                        <div class="col-sm-7 d-flex align-items-center register-detail-value">{{ ($register->jenis_entitas === 'badan_usaha') ? ($register->nama_badan_usaha ?? '-') : ($register->nama ?? '-') }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Nominal Pengajuan</label>
                        <div class="col-sm-7 d-flex align-items-center">Rp. {{ number_format($register->nominal_pengajuan,0,',','.') }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Tanggal Pengajuan</label>
                        <div class="col-sm-7 d-flex align-items-center">{{ strtoupper(\Carbon\Carbon::parse($register->tgl_pengajuan)->translatedFormat('d F Y')) }}</div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Jangka Waktu</label>
                        <div class="col-sm-7 d-flex align-items-center">{{ $register->jw_pengajuan ?? '-' }}</div>
                        </div>
                    <div class="mb-2 row">
                        <label class="col-sm-5 col-form-label">Jaminan</label>
                        <div class="col-sm-7 d-flex align-items-center">{{ strtoupper($register->jaminan ?? '-') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- MEMORANDUM KOMITE -->
    <div class="memorandum-komite-section mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Memorandum Komite</h5>
            <button class="btn btn-outline-primary" onclick="printMemorandum()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
        <div class="row g-4">
            @if(in_array('rekomendasi_manager', \App\Helpers\RoleHelper::getVisibleKomiteColumns()))
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Rekomendasi Manager</span>
                        <div class="d-flex gap-2">
                            @if($rekomendasiManager)
                                @php $canEditThis = \App\Helpers\RoleHelper::canEditKomiteData($rekomendasiManager) && \App\Helpers\RoleHelper::canFillKomiteColumn('rekomendasi_manager'); @endphp
                                @if($canEditThis)
                                @php
                                    $encId = \Illuminate\Support\Facades\Crypt::encryptString($rekomendasiManager->id_komite);
                                    $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
                                @endphp
                                <a href="{{ route('komite.edit.simple', ['id' => $urlSafeId]) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Ubah Data
                                </a>
                                @endif
                                @php $isCreator = auth()->user() && ($rekomendasiManager->input_by === auth()->user()->nama); @endphp
                                @if($isCreator || \App\Helpers\RoleHelper::isSuperAdmin())
                                <form action="{{ route('komite.destroy', $rekomendasiManager->id_komite) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data rekomendasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                                @endif
                            @endif
                            @php $allowAdd = is_null($rekomendasiManager); @endphp
                            @if($allowAdd && \App\Helpers\RoleHelper::canCreate('komite') && \App\Helpers\RoleHelper::canFillKomiteColumn('rekomendasi_manager'))
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomite">
                                    <i></i>Tambah Rekomendasi
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($rekomendasiManager)
                            <div class="mb-2 d-flex flex-wrap align-items-center gap-3">
                                <span><strong>Tanggal Rekomendasi :</strong> {{ \Carbon\Carbon::parse($rekomendasiManager->tgl)->translatedFormat('d F Y') }}</span>
                                <span><strong>Manager :</strong> {{ $rekomendasiManager->input_by }}</span>
                            </div>
                            <div class="komite-content">{!! $rekomendasiManager->keterangan !!}</div>
                        @else
                            <div class="text-muted">Belum ada data rekomendasi manager.</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @if(in_array('opini_direktur_kepatuhan', \App\Helpers\RoleHelper::getVisibleKomiteColumns()))
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Opini Direktur Kepatuhan</span>
                        <div class="d-flex gap-2">
                            @if($opiniDirektur)
                                @php $canEditThis = \App\Helpers\RoleHelper::canEditKomiteData($opiniDirektur) && \App\Helpers\RoleHelper::canFillKomiteColumn('opini_direktur_kepatuhan'); @endphp
                                @if($canEditThis)
                                @php
                                    $encId = \Illuminate\Support\Facades\Crypt::encryptString($opiniDirektur->id_komite);
                                    $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
                                @endphp
                                <a href="{{ route('komite.edit.simple', ['id' => $urlSafeId]) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Ubah Data
                                </a>
                                @endif
                                @php $isCreator = auth()->user() && ($opiniDirektur->input_by === auth()->user()->nama); @endphp
                                @if($isCreator || \App\Helpers\RoleHelper::isSuperAdmin())
                                <form action="{{ route('komite.destroy', $opiniDirektur->id_komite) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data opini ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                                @endif
                            @endif
                            @php $allowAdd = is_null($opiniDirektur); @endphp
                            @if($allowAdd && \App\Helpers\RoleHelper::canCreate('komite') && \App\Helpers\RoleHelper::canFillKomiteColumn('opini_direktur_kepatuhan'))
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomite">
                                    <i></i>Tambah Opini
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($opiniDirektur)
                            <div class="mb-2 d-flex flex-wrap align-items-center gap-3">
                                <span><strong>Tanggal Opini :</strong> {{ \Carbon\Carbon::parse($opiniDirektur->tgl)->translatedFormat('d F Y') }}</span>
                                <span><strong>Direktur Kepatuhan :</strong> {{ $opiniDirektur->input_by }}</span>
                            </div>
                            <div class="komite-content">{!! $opiniDirektur->keterangan !!}</div>
                        @else
                            <div class="text-muted">Belum ada data opini direktur kepatuhan.</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @if(in_array('keputusan_direktur_utama', \App\Helpers\RoleHelper::getVisibleKomiteColumns()))
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Keputusan Direktur Utama</span>
                        <div class="d-flex gap-2">
                            @if($keputusanDirektur)
                                @php $canEditThis = \App\Helpers\RoleHelper::canEditKomiteData($keputusanDirektur) && \App\Helpers\RoleHelper::canFillKomiteColumn('keputusan_direktur_utama'); @endphp
                                @if($canEditThis)
                                @php
                                    $encId = \Illuminate\Support\Facades\Crypt::encryptString($keputusanDirektur->id_komite);
                                    $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
                                @endphp
                                <a href="{{ route('komite.edit.simple', ['id' => $urlSafeId]) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Ubah Data
                                </a>
                                @endif
                                @php $isCreator = auth()->user() && ($keputusanDirektur->input_by === auth()->user()->nama); @endphp
                                @if($isCreator || \App\Helpers\RoleHelper::isSuperAdmin())
                                <form action="{{ route('komite.destroy', $keputusanDirektur->id_komite) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data keputusan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                                @endif
                            @endif
                            @php $allowAdd = is_null($keputusanDirektur); @endphp
                            @if($allowAdd && \App\Helpers\RoleHelper::canCreate('komite') && \App\Helpers\RoleHelper::canFillKomiteColumn('keputusan_direktur_utama'))
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomite">
                                    <i></i>Keputusan 
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($keputusanDirektur)
                            <div class="mb-2 d-flex flex-wrap align-items-center gap-3">
                                <span><strong>Tanggal Keputusan :</strong> {{ \Carbon\Carbon::parse($keputusanDirektur->tgl)->translatedFormat('d F Y') }}</span>
                                <span><strong>Direktur Utama :</strong> {{ $keputusanDirektur->input_by }}</span>
                                @if(!empty($keputusanDirektur->keputusan))
                                <span class="ms-auto"><strong>Keputusan :</strong> {{ $keputusanDirektur->keputusan_label }}</span>
                                @endif
                            </div>
                            <div class="komite-content">{!! $keputusanDirektur->keterangan !!}</div>
                        @else
                            <div class="text-muted">Belum ada data keputusan direktur utama.</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @if(in_array('mengetahui_komisaris', \App\Helpers\RoleHelper::getVisibleKomiteColumns()))
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Mengetahui Komisaris</span>
                        <div class="d-flex gap-2">
                            @if($mengetahuiKomisaris)
                                @php $canEditThis = \App\Helpers\RoleHelper::canEditKomiteData($mengetahuiKomisaris) && \App\Helpers\RoleHelper::canFillKomiteColumn('mengetahui_komisaris'); @endphp
                                @if($canEditThis)
                                @php
                                    $encId = \Illuminate\Support\Facades\Crypt::encryptString($mengetahuiKomisaris->id_komite);
                                    $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
                                @endphp
                                <a href="{{ route('komite.edit.simple', ['id' => $urlSafeId]) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Ubah Data
                                </a>
                                @endif
                                @php $isCreator = auth()->user() && ($mengetahuiKomisaris->input_by === auth()->user()->nama); @endphp
                                @if($isCreator || \App\Helpers\RoleHelper::isSuperAdmin())
                                <form action="{{ route('komite.destroy', $mengetahuiKomisaris->id_komite) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                                @endif
                            @endif
                            @php $allowAdd = is_null($mengetahuiKomisaris); @endphp
                            @if($allowAdd && \App\Helpers\RoleHelper::canCreate('komite') && \App\Helpers\RoleHelper::canFillKomiteColumn('mengetahui_komisaris'))
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomite">
                                    <i></i>Mengetahui
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($mengetahuiKomisaris)
                            <div class="mb-2 d-flex flex-wrap align-items-center gap-3">
                                <span><strong>Tanggal :</strong> {{ \Carbon\Carbon::parse($mengetahuiKomisaris->tgl)->translatedFormat('d F Y') }}</span>
                                <span><strong>Komisaris :</strong> {{ $mengetahuiKomisaris->input_by }}</span>
                            </div>
                            <div class="komite-content">{!! $mengetahuiKomisaris->keterangan !!}</div>
                        @else
                            <div class="text-muted">Belum ada data mengetahui komisaris.</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
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

    <!-- HAPUS: CARD TABEL DAN PAGINATION KOMITE -->
</div>
@include('komite._modal')
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/komite_style.css') }}">
<style>
.register-detail-value { text-transform: uppercase; }
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/komite-index.js') }}"></script>
<script src="{{ asset('assets/js/komite-modal-summernote.js') }}"></script>
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
</script>

<script>
function printMemorandum() {
    const printableContent = document.querySelector('.printable-content');
    if (!printableContent) {
        alert('Tidak ada data memorandum untuk dicetak');
        return;
    }
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    printWindow.document.write(`
        <html>
            <head>
                <title>Cetak Memorandum</title>
                <style>
.printable-content .bordered {
    border: 2px solid #000 !important;
    padding: 10px 20px;
    margin-bottom: 22px;
    border-radius: 3px;
}
.printable-content table.detail-kredit-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
    font-size: 1em;
}
.printable-content table.detail-kredit-table td,
.printable-content table.detail-kredit-table th {
    padding: 4px 12px;
    border: 1.5px solid #222;
    vertical-align: top;
}
.printable-content .section-title {
    font-weight: bold;
    border-bottom: 2px solid #000;
    margin-bottom: 10px;
    padding-bottom: 3px;
    font-size: 1.08em;
}
.printable-content td:nth-child(2),
.printable-content td:nth-child(4) {
  text-transform: uppercase;
}
                </style>
                <style>@media print { body { margin:0; } }</style>
            </head>
            <body>
                <div class='printable-content'>
                ${printableContent.innerHTML}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() { printWindow.print(); printWindow.close(); }, 500);
}
</script>

<div class="printable-content" style="display: none;">
<div style="font-size:13px; color:#222; margin-bottom:20px;">
    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/y, H:i') }}
  </div>
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
    <img src="{{ asset('assets/image/Logo/logo_odigi.png') }}" alt="Logo" style="height:68px;">
    <div style="font-weight:bold; text-align:right;">
      <div style="font-size:20px;">Memorandum Kredit {{ $register->jenis_pengajuan_label ?? '-' }}</div>
    </div>
  </div>
  <div style="text-align:center; font-size:18px; font-weight:bold; margin-bottom:16px; letter-spacing:0.5px;">Memorandum Kredit</div>
  <table class="detail-kredit-table">
    <tr>
      <td style="font-weight:bold;">Nomor Register</td><td>{{ $register->nomor ?? '-' }}</td>
      <td style="font-weight:bold;">
        @if($register->jenis_entitas === 'badan_usaha')
          Nama Perusahaan
        @else
          Nama Nasabah
        @endif
      </td>
      <td>
        @if($register->jenis_entitas === 'badan_usaha')
          {{ $register->nama_badan_usaha ?? '-' }}
        @else
          {{ $register->nama ?? '-' }}
        @endif
      </td>
    </tr>
    <tr>
      <td style="font-weight:bold;">Jenis Pengajuan</td><td>{{ $register->jenis_pengajuan_label ?? '-' }}</td>
      <td style="font-weight:bold;">Nominal Pengajuan</td><td>Rp. {{ number_format($register->nominal_pengajuan,0,',','.') }}</td>
    </tr>
    <tr>
      <td style="font-weight:bold;">Nama Pengusul</td><td>{{ $register->input_by ?? '-' }}</td>
      <td style="font-weight:bold;">Tanggal Pengajuan</td><td>{{ strtoupper(\Carbon\Carbon::parse($register->tgl_pengajuan)->translatedFormat('d F Y')) }}</td>
    </tr>
    <tr>
      <td style="font-weight:bold;">Jenis Entitas</td><td>
        @if($register->jenis_entitas === 'perorangan')
            {{ strtoupper('Perorangan') }}
        @elseif($register->jenis_entitas === 'badan_usaha')
            {{ strtoupper('Badan Usaha') }}
        @else
            -
        @endif
      </td>
      <td style="font-weight:bold;">Jangka Waktu</td><td>{{ $register->jw_pengajuan ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight:bold;">Jaminan</td><td>{{ strtoupper($register->jaminan ?? '-') }}</td>
      <td style="font-weight:bold;">
        @if($register->jenis_entitas === 'badan_usaha')
          Nomor Legalitas Usaha
        @else
          No Identitas
        @endif
      </td>
      <td>
        @if($register->jenis_entitas === 'badan_usaha')
          {{ $register->nomor_legalitas_usaha ?? '-' }}
        @else
          {{ $register->no_identitas ?? '-' }}
        @endif
      </td>
    </tr>
    <tr>
      <td style="font-weight:bold;">
        @if($register->jenis_entitas === 'badan_usaha')
          Jenis Dokumen Usaha
        @else
          Jenis Identitas
        @endif
      </td>
      <td>
        @if($register->jenis_entitas === 'badan_usaha')
          {{ $register->jenis_dokumen_usaha ?? '-' }}
        @else
          {{ $register->jns_identitas ?? '-' }}
        @endif
      </td>
    </tr>
  </table>
    @if($rekomendasiManager)
    <div class="memorandum-section bordered">
        <div class="section-title">Rekomendasi Manager Kredit</div>
        <div><strong>Tanggal Rekomendasi:</strong> {{ \Carbon\Carbon::parse($rekomendasiManager->tgl)->translatedFormat('d F Y') }}</div>
        <div><strong>Manager Kredit:</strong> {{ $rekomendasiManager->input_by }}</div>
        <div>{!! $rekomendasiManager->keterangan !!}</div>
    </div>
    @endif
    @if($opiniDirektur)
    <div class="memorandum-section bordered">
        <div class="section-title">Opini Direktur Kepatuhan</div>
        <div><strong>Tanggal Opini:</strong> {{ \Carbon\Carbon::parse($opiniDirektur->tgl)->translatedFormat('d F Y') }}</div>
        <div><strong>Direktur Kepatuhan:</strong> {{ $opiniDirektur->input_by }}</div>
        <div>{!! $opiniDirektur->keterangan !!}</div>
    </div>
    @endif
    @if($keputusanDirektur)
    <div class="memorandum-section bordered">
        <div class="section-title">Keputusan Direktur Utama</div>
        <div><strong>Tanggal Keputusan:</strong> {{ \Carbon\Carbon::parse($keputusanDirektur->tgl)->translatedFormat('d F Y') }}</div>
        <div><strong>Direktur Utama:</strong> {{ $keputusanDirektur->input_by }}</div>
        @if(!empty($keputusanDirektur->keputusan))
        <div><strong>Keputusan:</strong> <span style="font-weight:bold">{{ $keputusanDirektur->keputusan_label }}</span></div>
        @endif
        <div>{!! $keputusanDirektur->keterangan !!}</div>
    </div>
    @endif
    @if($mengetahuiKomisaris)
    <div class="memorandum-section bordered">
        <div class="section-title">Mengetahui Komisaris</div>
        <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($mengetahuiKomisaris->tgl)->translatedFormat('d F Y') }}</div>
        <div><strong>Komisaris:</strong> {{ $mengetahuiKomisaris->input_by }}</div>
        <div>{!! $mengetahuiKomisaris->keterangan !!}</div>
    </div>
    @endif
    <div class="memorandum-section bordered" style="text-align:center; font-style:italic;">
        Memorandum ini dibuat menggunakan sistem maka tidak diperlukan tanda tangan.
    </div>
</div>
@endpush