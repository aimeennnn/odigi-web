@extends('layout.master')
@section('main-content')
<div class="container mt-4">
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #4F8CFF 0%, #b2f2e5 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Edit Data SLIK</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Ubah Data Pemeriksaan SLIK Nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-shield-check"></i></span>
    </div>
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-header" style="background: linear-gradient(90deg, #4F8CFF 0%, #b2f2e5 100%); color: white; border-radius: 18px 18px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Data SLIK</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('slik.update', $slik->id_slik) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">Nomor SLIK</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" name="nomor" value="{{ old('nomor', $slik->nomor) }}" readonly required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('nomor')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">Pilih Register (Nomor / Nama Nasabah)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" name="id_reg_display" readonly
                                       value="{{ optional($registers->firstWhere('id_reg', old('id_reg', $slik->id_reg)))->nomor }} - {{ optional($registers->firstWhere('id_reg', old('id_reg', $slik->id_reg)))->nama }}">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <input type="hidden" name="id_reg" value="{{ old('id_reg', $slik->id_reg) }}">
                            @error('id_reg')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">Nama Nasabah</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="nama" value="{{ old('nama', $slik->nama) }}" required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">No Identitas</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="no_identitas" value="{{ old('no_identitas', $slik->no_identitas) }}" inputmode="numeric" pattern="[0-9]{14,16}" title="Masukkan 14-16 digit angka" required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                            @error('no_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="tgl" value="{{ old('tgl', $slik->tgl) }}" required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('tgl')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary">Keterkaitan</label>
                            <div class="input-group">
                                <select class="form-select" name="keterkaitan" required>
                                    @php $selectedKeterkaitan = old('keterkaitan', $slik->keterkaitan); @endphp
                                    <option value="">Pilih keterkaitan...</option>
                                    @foreach(\App\Models\Slik::keterkaitanList() as $key => $label)
                                        <option value="{{ $key }}" {{ $selectedKeterkaitan == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('keterkaitan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    @php $encS = strtr(\Illuminate\Support\Facades\Crypt::encryptString($slik->id_slik), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                    <button type="submit" class="btn btn-primary px-4"><i class="bbi bi-check-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('slik.show', $encS) }}" class="btn btn-danger px-4"><i class="bi bi-x-circle me-1"></i>Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- JavaScript untuk validasi SLIK -->
<script src="{{ asset('assets/js/slik-edit.js') }}"></script>
@endpush

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/slik_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/slik-edit.js') }}"></script>
@endpush