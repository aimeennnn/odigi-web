@extends('layout.master')

@section('main-content')
<div class="container mt-4">
    <!-- Header Gradient -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Edit Data Bank</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Ubah informasi data bank nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-bank2"></i></span>
    </div>
    
    <!-- Form Edit -->
    <div class="card shadow-sm" style="border-radius: 18px;">
        <div class="card-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Data Bank</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('bank.update', $bank->id_bank) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                
                <!-- Info Umum -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Info Umum</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_reg_display" class="form-label fw-bold">Pilih Register <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="id_reg_display" class="form-control bg-light @error('id_reg') is-invalid @enderror" readonly
                                       value="{{ optional($registers->firstWhere('id_reg', $bank->id_reg))->nomor }} - {{ optional($registers->firstWhere('id_reg', $bank->id_reg))->nama }}">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <input type="hidden" name="id_reg" value="{{ $bank->id_reg }}">
                            @error('id_reg')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
						<div class="col-md-6">
							<label for="nama_bank" class="form-label fw-bold">Nama Bank <span class="text-danger">*</span></label>
							<div class="input-group">
								<select class="form-select @error('nama_bank') is-invalid @enderror" name="nama_bank" id="nama_bank" required>
									@php $selectedBank = old('nama_bank', $bank->nama_bank); @endphp
									<option value="">Pilih bank...</option>
									@foreach(\App\Models\Bank::namaBankList() as $key => $label)
										<option value="{{ $key }}" {{ $selectedBank == $key ? 'selected' : '' }}>{{ $label }}</option>
									@endforeach
								</select>
								<span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
							</div>
							@error('nama_bank')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
                        
                        <div class="col-md-6">
                            <label for="nomor_rekening" class="form-label fw-bold">No Rekening <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('nomor_rekening') is-invalid @enderror" 
                                       name="nomor_rekening" id="nomor_rekening" value="{{ old('nomor_rekening', $bank->no_rekening) }}" inputmode="numeric" pattern="[0-9]{8,20}" required>
                                <span class="input-group-text bg-white border-start-0 required-warning"><i class="bi bi-exclamation-triangle-fill text-warning"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem;">Masukkan 8-20 digit angka</div>
                            @error('nomor_rekening')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold">Status Pemeriksaan <span class="text-danger">*</span></label>
                            @php 
                                $st = strtolower(old('status', $bank->status ?: 'proses'));
                                $label = $st === 'valid' ? 'Valid' : (in_array($st, ['tidak valid','tidak_valid','tidakvalid']) ? 'Tidak Valid' : 'Dalam Proses');
                                // Normalisasi nilai yang disubmit
                                $normalized = $st === 'valid' ? 'valid' : (in_array($st, ['tidak valid','tidak_valid','tidakvalid']) ? 'tidak valid' : 'proses');
                            @endphp
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" value="{{ $label }}" readonly>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <input type="hidden" name="status" value="{{ $normalized }}">
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- File Upload -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark me-2"></i>File & Dokumen</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Upload File Baru (Opsional)</label>
                            <div id="editBankDropzoneFile" class="upload-dropzone mb-2">
                                <div class="dz-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="fw-semibold mb-1">Drag and drop file here</div>
                                <div class="upload-helper">or <a href="#" id="editBankChooseFile">Choose file</a></div>
                                <input type="file" class="d-none @error('file') is-invalid @enderror" name="file" id="editBankFileInput" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div id="editBankFileInfo" class="mt-2"></div>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($bank->file)
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        File saat ini: {{ basename($bank->file) }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                    @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($bank->id_bank), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('bank.show', $encId) }}" class="btn btn-danger px-4">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/bank_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/bank-edit.js') }}"></script>
@if($errors->any())
<script>
// Auto open modal if validation errors
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('modalTambahBank'));
    modal.show();
});
</script>
@endif
@endpush