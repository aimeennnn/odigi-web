@extends('layout.master')

@section('main-content')
<!-- Add class to body for edit page styling -->
<script>
document.body.classList.add('edit-komite-page');
</script>

@php
    // Pastikan variabel komite dan registers tersedia
    if (!isset($komite) || !$komite) {
        abort(404, 'Data komite tidak ditemukan');
    }
    $registers = isset($registers) ? $registers : collect();
@endphp

<div class="container mt-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="gradient-header-komite p-4 mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">Edit Data Komite</h2>
            <div class="desc">Ubah informasi data komite</div>
        </div>
        <span class="icon"><i class="bi bi-pencil-square"></i></span>
    </div>
    
    <!-- TOMBOL AKSI -->
    <!-- <div class="mb-3 d-flex gap-2 justify-content-end">
        @php $encK = strtr(\Illuminate\Support\Facades\Crypt::encryptString($komite->id_komite), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        <a href="{{ route('komite.show', $encK) }}" class="btn btn-dark">Kembali ke Detail</a>
    </div> -->
    
    <div class="card form-card">
        <div class="card-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Data Komite</h5>
        </div>
        <div class="card-body p-4">
            @php
                $encId = \Illuminate\Support\Facades\Crypt::encryptString($komite->id_komite);
                $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
            @endphp
            <form action="{{ route('komite.update.simple', ['id' => $urlSafeId]) }}" method="POST">
                @csrf
                
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_reg_display" class="form-label">Pilih Nasabah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="id_reg_display" name="id_reg_display" class="form-control bg-light" value="{{ optional($registers->firstWhere('id_reg', $komite->id_reg))->nama }}" readonly>
                            <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                        <input type="hidden" name="id_reg" value="{{ $komite->id_reg }}">
                        @error('id_reg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="tgl" class="form-label">Tanggal Keputusan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="date" class="form-control @error('tgl') is-invalid @enderror" id="tgl" name="tgl" value="{{ $komite->tgl }}" required>
                            <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                        @error('tgl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
				<div class="mb-3">
					<label for="keterangan" class="form-label fw-bold text-primary">Keterangan <span class="text-danger">*</span></label>
					<div class="input-group">
						<div class="summernote-container">
							<textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan lengkap mengenai keputusan komite..." required>{!! $komite->keterangan !!}</textarea>
							<!-- Proxy input untuk memunculkan tooltip native HTML5 ketika Summernote kosong -->
							<input type="text" id="keterangan_required_proxy" required aria-hidden="true" tabindex="-1" style="position:absolute; right:12px; bottom:12px; width:1px; height:1px; opacity:0; pointer-events:none; background:transparent; border:0;" />
						</div>
						<span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
					</div>
					<div id="keterangan_error" class="text-danger small mt-1" style="display:none;">Mohon isi keterangan.</div>
					@error('keterangan')
						<div class="invalid-feedback">{{ $message }}</div>
					@enderror
				</div>
                
                @php $canEditKeputusan = \App\Helpers\RoleHelper::getKomiteRole() === 'direktur_utama'; @endphp
                @if($canEditKeputusan && $komite->tipe_memorandum === 'keputusan')
                <div class="mb-4">
                    <label for="keputusan" class="form-label">Keputusan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-select @error('keputusan') is-invalid @enderror" id="keputusan" name="keputusan" required>
                            <option value="">-- Pilih Keputusan --</option>
                            @foreach(\App\Models\Komite::keputusanList() as $key => $label)
                                <option value="{{ $key }}" @if($komite->keputusan == $key) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                    </div>
                    @error('keputusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @else
                    <input type="hidden" name="keputusan" value="{{ $komite->keputusan }}">
                @endif
                
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-komite"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('komite.index') }}" class="btn btn-danger px-4"><i class="bi bi-x-circle me-1"></i>Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- JavaScript untuk validasi Komite -->
<script src="{{ asset('assets/js/komite-edit-summernote.js') }}"></script>
{{-- <script src="{{ asset('assets/js/komite-summernote-fix.js') }}"></script> --}}
@endpush

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/komite_style.css') }}">
@endsection