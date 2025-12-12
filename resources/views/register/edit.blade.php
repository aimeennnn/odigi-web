@extends('layout.master')
@section('main-content')
<!-- Tambahkan link ke CSS register -->
<link rel="stylesheet" href="{{ asset('assets/css/register_style.css') }}">
<div class="container mt-4">
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Edit Data Register</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Ubah Data Registrasi SLIK OJK & Pengajuan</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-journal-check"></i></span>
    </div>
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Data Register</h5>
        </div>
        <div class="card-body">
            @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($register->id_reg), ['+' => '-', '/' => '_', '=' => '.']); @endphp
            <form method="POST" action="{{ route('register.update', $encId) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-success">Nomor Registrasi</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" name="nomor" value="{{ old('nomor', $register->nomor) }}" readonly required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('nomor')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2" id="nama-perorangan-container">
                            <label class="form-label fw-semibold text-success">Nama Lengkap</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="nama" value="{{ old('nama', $register->nama) }}">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="mb-2" id="nama-badan-usaha-container" style="display: none;">
                            <label class="form-label fw-semibold text-success">Nama Badan Usaha</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="nama_badan_usaha" value="{{ old('nama_badan_usaha', $register->nama_badan_usaha) }}">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('nama_badan_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <!-- Jenis Entitas -->
                        <label class="form-label fw-semibold text-success">Jenis Entitas</label>
                        <div class="input-group">
                            <select class="form-select" name="jenis_entitas" required onchange="toggleJenisKelamin(this.value)">
                                <option value="">Pilih jenis entitas...</option>
                                @foreach(\App\Models\Register::jenisEntitasList() as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis_entitas', $register->jenis_entitas) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                        @error('jenis_entitas')<div class="text-danger small">{{ $message }}</div>@enderror
                        
                        <!-- Field khusus Perorangan -->
                        <div id="perorangan-fields">
                            <!-- Jenis Kelamin (required for perorangan) -->
                            <div class="mb-2" id="jenis-kelamin-container">
                                <label class="form-label fw-semibold text-success">Jenis Kelamin</label>
                                <div class="input-group">
                                    <select class="form-select" name="jns_kelamin">
                                        <option value="">Pilih jenis kelamin...</option>
                                        @foreach(\App\Models\Register::jenisKelaminList() as $key => $label)
                                            <option value="{{ $key }}" {{ old('jns_kelamin', $register->jns_kelamin) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                @error('jns_kelamin')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            
                            <!-- No Identitas (required for perorangan) -->
                            <div class="mb-2" id="no-identitas-container">
                                <label class="form-label fw-semibold text-success">Nomor Identitas</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="no_identitas" id="no_identitas" value="{{ old('no_identitas', $register->no_identitas) }}" maxlength="16">
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                                @error('no_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            
                            <!-- Jenis Identitas (required for perorangan) -->
                            <div class="mb-2" id="jenis-identitas-container">
                                <label class="form-label fw-semibold text-success">Jenis Identitas</label>
                                <div class="input-group">
                                    <select class="form-select" name="jns_identitas">
                                        <option value="">Pilih jenis identitas...</option>
                                        @foreach(\App\Models\Register::jenisIdentitasList() as $key => $label)
                                            <option value="{{ $key }}" {{ old('jns_identitas', $register->jns_identitas) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                @error('jns_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            
                            <!-- Pekerjaan (required for perorangan) -->
                            <div class="mb-2" id="pekerjaan-container">
                                <label class="form-label fw-semibold text-success">Pekerjaan</label>
                                <div class="input-group">
                                    <select class="form-select" name="pekerjaan">
                                        <option value="">Pilih kelompok pekerjaan...</option>
                                        @foreach(\App\Models\Register::pekerjaanList() as $key => $label)
                                            <option value="{{ $key }}" {{ old('pekerjaan', $register->pekerjaan) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                @error('pekerjaan')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            
                            <!-- Alamat (required for perorangan) -->
                            <div class="mb-2" id="alamat-container">
                                <label class="form-label fw-semibold text-success">Alamat</label>
                                <div class="input-group">
                                    <textarea class="form-control" name="alamat" rows="2">{{ old('alamat', $register->alamat) }}</textarea>
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                @error('alamat')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <!-- Field khusus Badan Usaha -->
                        <div id="badan-usaha-fields" style="display: none;">
                            <!-- Jenis Dokumen Usaha -->
                            <label class="form-label fw-semibold text-success">Jenis Dokumen Usaha</label>
                            <div class="input-group">
                                <select class="form-select" name="jenis_dokumen_usaha">
                                    <option value="">Pilih jenis dokumen...</option>
                                    @foreach(\App\Models\Register::jenisDokumenUsahaList() as $key => $label)
                                        <option value="{{ $key }}" {{ old('jenis_dokumen_usaha', $register->jenis_dokumen_usaha) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('jenis_dokumen_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                            
                            <!-- Nomor Legalitas Usaha -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-success">Nomor Legalitas Usaha</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="nomor_legalitas_usaha" id="nomor_legalitas_usaha" value="{{ old('nomor_legalitas_usaha', $register->nomor_legalitas_usaha) }}" maxlength="16">
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                                @error('nomor_legalitas_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            
                            <!-- Bidang Usaha -->
                            <label class="form-label fw-semibold text-success">Bidang Usaha</label>
                            <div class="input-group">
                                <select class="form-select" name="bidang_usaha">
                                    <option value="">Pilih bidang usaha...</option>
                                    @foreach(\App\Models\Register::bidangUsahaList() as $key => $label)
                                        <option value="{{ $key }}" {{ old('bidang_usaha', $register->bidang_usaha) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('bidang_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                            
                            <!-- Alamat Usaha -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-success">Alamat Usaha</label>
                                <div class="input-group">
                                    <textarea class="form-control" name="alamat_usaha" rows="2">{{ old('alamat_usaha', $register->alamat_usaha) }}</textarea>
                                    <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                                </div>
                                @error('alamat_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-success">Tanggal Pengajuan</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="tgl_pengajuan" value="{{ old('tgl_pengajuan', $register->tgl_pengajuan) }}" required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('tgl_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <!-- Jenis Pengajuan -->
                        <label class="form-label fw-semibold text-success">Jenis Pengajuan</label>
                        <div class="input-group">
                            <select class="form-select" name="jns_pengajuan" required>
                                <option value="">Pilih jenis pengajuan...</option>
                                @foreach(\App\Models\Register::jenisPengajuanList() as $key => $label)
                                    <option value="{{ $key }}" {{ old('jns_pengajuan', $register->jns_pengajuan) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                        @error('jns_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-success">Nominal Pengajuan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" name="nominal_pengajuan" value="{{ old('nominal_pengajuan', number_format($register->nominal_pengajuan,0,',','.')) }}" required autocomplete="off">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem;">Masukkan angka tanpa titik atau koma</div>
                            @error('nominal_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-success">Jangka Waktu</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="jw_pengajuan" value="{{ old('jw_pengajuan', $register->jw_pengajuan) }}" required>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('jw_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <!-- Jaminan -->
                        <label class="form-label fw-semibold text-success">Jaminan</label>
                        <div class="input-group">
                            <select class="form-select" name="jaminan" required>
                                <option value="">Pilih jenis jaminan...</option>
                                @foreach(\App\Models\Register::jaminanList() as $key => $label)
                                    <option value="{{ $key }}" {{ old('jaminan', $register->jaminan) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                        @error('jaminan')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-success">Status Pengajuan</label>
                            <div class="input-group">
                                <select class="form-select" name="status" required disabled style="background-color: #f8f9fa; color: #6c757d;">
                                    <option value="1" selected>Dalam Proses</option>
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <input type="hidden" name="status" value="1">
                            <small class="text-muted">Status terkunci pada "Dalam Proses"</small>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
                    @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($register->id_reg), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                    <a href="{{ route('register.show', $encId) }}" class="btn btn-danger px-4"><i class="bi bi-x-circle me-1"></i>Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<!-- Tambahkan CDN AutoNumeric untuk input uang -->
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>

<script>
// Fungsi untuk toggle field berdasarkan jenis entitas
function toggleJenisKelamin(jenisEntitas) {
    // Field perorangan
    const peroranganFields = document.getElementById('perorangan-fields');
    
    // Field badan usaha
    const badanUsahaFields = document.getElementById('badan-usaha-fields');
    
    if (jenisEntitas === 'perorangan') {
        // Tampilkan field perorangan, sembunyikan badan usaha
        peroranganFields.style.display = 'block';
        badanUsahaFields.style.display = 'none';
    } else if (jenisEntitas === 'badan_usaha') {
        // Sembunyikan field perorangan, tampilkan field badan usaha
        peroranganFields.style.display = 'none';
        badanUsahaFields.style.display = 'block';
    } else {
        // Sembunyikan semua field khusus
        peroranganFields.style.display = 'none';
        badanUsahaFields.style.display = 'none';
    }
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const jenisEntitasSelect = document.querySelector('select[name="jenis_entitas"]');
    if (jenisEntitasSelect) {
        toggleJenisKelamin(jenisEntitasSelect.value);
    }
});
</script>

<script src="{{ asset('assets/js/register-edit.js') }}"></script>
@endpush 