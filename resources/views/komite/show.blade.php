@extends('layout.master')

@section('main-content')
<div class="container mt-4">
    <!-- Header Gradient -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Detail Komite</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Informasi detail memo komite</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-clipboard-data"></i></span>
    </div>
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @if(request('register_id'))
            <a href="{{ route('komite.index', ['register_id' => request('register_id')]) }}" class="btn" style="background:#3d3935; color:#fff; font-weight:500;">Kembali ke Data</a>
        @else
            <a href="{{ route('komite.index') }}" class="btn" style="background:#3d3935; color:#fff; font-weight:500;">Kembali ke Data</a>
        @endif
        @php $encK = strtr(\Illuminate\Support\Facades\Crypt::encryptString($komite->id_komite), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        @if(\App\Helpers\RoleHelper::canEditKomiteData($komite))
            <a href="{{ route('komite.edit.simple') }}?id={{ $encK }}{{ request('register_id') ? '&register_id='.request('register_id') : '' }}" class="btn" style="background:#f4cb4b; color:#222; font-weight:500;">Ubah Data</a>
        @endif
        @if(\App\Helpers\RoleHelper::canDelete('komite'))
            <form action="{{ route('komite.destroy', $komite->id_komite) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data?')">
                @csrf
                @method('DELETE')
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <button class="btn" style="background:#ff624d; color:#fff; font-weight:500;">Hapus</button>
            </form>
        @endif
    </div>
    @if($komite->register)
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head d-flex align-items-center justify-content-between">
            <div><h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Info Nasabah</h5></div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3" style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#49bbca,#ffc166); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2.5rem; box-shadow:0 2px 8px rgba(79,140,255,0.15);">
                    {{ strtoupper(substr($komite->register->nama,0,1)) }}
                </div>
                <div>
                    <h2 class="fw-bold mb-0" style="letter-spacing:1px; color:#162447;">{{ $komite->register->nama }}</h2>
                    <!-- <span class="badge d-inline-flex align-items-center" style="background:#e3f0ff; color:#49bbca; font-size:1rem;" data-bs-toggle="tooltip" title="ID Register">
                        <i class="bi bi-person-badge me-1"></i> {{ $komite->register->status }}
                    </span> -->
                    <div class="text-muted small mt-1">Diinput oleh: {{ $komite->register->user->name ?? ($komite->register->input_by) }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item"><div class="info-label">Nomor Registrasi</div><div class="info-value">{{ $komite->register->nomor }}</div></div>
                @php $isBU = ($komite->register->jenis_entitas ?? '') === 'badan_usaha'; @endphp
                <div class="info-item">
                    <div class="info-label">{{ $isBU ? 'Nomor Legalitas Usaha' : 'No Identitas' }}</div>
                    <div class="info-value">{{ $isBU ? ($komite->register->nomor_legalitas_usaha ?? '-') : ($komite->register->no_identitas ?? '-') }}</div>
                </div>
                <div class="info-item"><div class="info-label">Nama Lengkap</div><div class="info-value">{{ $komite->register->nama }}</div></div>
                <div class="info-item"><div class="info-label">Nominal Pengajuan</div><div class="info-value">Rp {{ number_format($komite->register->nominal_pengajuan,0,',','.') }}</div></div>
                <div class="info-item"><div class="info-label">Tanggal Pengajuan</div><div class="info-value">{{ $komite->register->tgl_pengajuan }}</div></div>
                <div class="info-item"><div class="info-label">Status Register</div><div class="info-value">{{ $komite->register->status }}</div></div>
            </div>
        </div>
    </div>
    @endif
    <div class="card mb-4 shadow-sm section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Komite</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Nama Nasabah</div>
                    <div class="info-value">{{ $komite->register->nama ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">{{ $komite->tgl }}</div>
                </div>
                @if(!empty($komite->keputusan) && $komite->tipe_memorandum === 'keputusan')
                <div class="info-item">
                    <div class="info-label">Keputusan</div>
                    <div class="info-value">{{ $komite->keputusan_label }}</div>
                </div>
                @endif
                <div class="info-item full-width">
                    <div class="info-label">Keterangan</div>
                    <div class="info-value">
                        <div class="komite-content">{!! $komite->keterangan !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/komite_style.css') }}">
@endsection