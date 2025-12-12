<link rel="stylesheet" href="{{ asset('assets/css/register_style.css') }}">
<!-- ===== Modal Tambah Data Register dengan Tema Keuangan ===== -->
<div class="modal fade" id="modalTambahData" tabindex="-1" aria-labelledby="modalTambahDataLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header Gradient Hijau-Biru -->
      <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
        <h5 class="modal-title text-white d-flex align-items-center gap-2" id="modalTambahDataLabel" style="font-size: 1.1rem;">
          <i class="bi bi-archive me-2"></i>Tambah Data Arsip Keuangan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body: Form Tambah Data -->
      <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data">
        @csrf
        <!-- Hidden input untuk jenis entitas -->
        <input type="hidden" name="jenis_entitas" id="jenis_entitas_input" value="">
        <div class="modal-body" style="padding: 1.25rem;">
          <div class="row g-3">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
              <!-- Nomor Registrasi (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-hash me-1"></i>Nomor Registrasi
                </label>
                <div class="input-group">
                  <input type="text" class="form-control bg-light" name="nomor" value="{{ $nomor_registrasi ?? '' }}" readonly required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('nomor')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Nama Nasabah/Perorangan (required) -->
              <div class="mb-2" id="nama-perorangan-container">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-person me-1"></i>Nama Lengkap
                </label>
                <div class="input-group">
                  <input type="text" class="form-control" name="nama" placeholder="Masukkan nama lengkap" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              
              <!-- Nama Badan Usaha (required for badan usaha) -->
              <div class="mb-2" id="nama-badan-usaha-container" style="display: none;">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-building me-1"></i>Nama Badan Usaha
                </label>
                <div class="input-group">
                  <input type="text" class="form-control" name="nama_badan_usaha" placeholder="Masukkan nama badan usaha">
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('nama_badan_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Jenis Kelamin (required for perorangan) -->
              <div class="mb-2" id="jenis-kelamin-container">
                
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-gender-ambiguous me-1"></i>Jenis Kelamin
                </label>
                <div class="input-group">
                  <select class="form-select" name="jns_kelamin" required>
                    <option value="">Pilih jenis kelamin...</option>
                    @foreach(\App\Models\Register::jenisKelaminList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('jns_kelamin')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              
              <!-- Field khusus Badan Usaha -->
              <div id="badan-usaha-fields" style="display: none;">
                <!-- Jenis Dokumen Usaha -->
                <div class="mb-2">
                  <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                    <i class="bi bi-file-text me-1"></i>Jenis Dokumen Usaha
                  </label>
                  <div class="input-group">
                    <select class="form-select" name="jenis_dokumen_usaha">
                      <option value="">Pilih jenis dokumen...</option>
                      @foreach(\App\Models\Register::jenisDokumenUsahaList() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                  </div>
                  @error('jenis_dokumen_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                
                <!-- Nomor Legalitas Usaha -->
                <div class="mb-2">
                  <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                    <i class="bi bi-hash me-1"></i>Nomor Legalitas Usaha
                  </label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="nomor_legalitas_usaha" id="nomor_legalitas_usaha" placeholder="Masukkan nomor legalitas" maxlength="16">
                    <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="nomor_legalitas_usaha_icon"><i class="bi bi-exclamation-circle"></i></span>
                  </div>
                  <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                  @error('nomor_legalitas_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                
                <!-- Bidang Usaha -->
                <div class="mb-2">
                  <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                    <i class="bi bi-briefcase me-1"></i>Bidang Usaha
                  </label>
                  <div class="input-group">
                    <select class="form-select" name="bidang_usaha">
                      <option value="">Pilih bidang usaha...</option>
                      @foreach(\App\Models\Register::bidangUsahaList() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                  </div>
                  @error('bidang_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                
                <!-- Alamat Usaha -->
                <div class="mb-2">
                  <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                    <i class="bi bi-geo-alt me-1"></i>Alamat Usaha
                  </label>
                  <div class="input-group">
                    <textarea class="form-control" name="alamat_usaha" rows="2" placeholder="Masukkan alamat usaha"></textarea>
                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                  </div>
                  @error('alamat_usaha')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
              </div>
              <!-- No Identitas (required for perorangan) -->
              <div class="mb-2" id="no-identitas-container">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-card-text me-1"></i>Nomor Identitas
                </label>
                <div class="input-group">
                  <input type="text" class="form-control" name="no_identitas" id="no_identitas" placeholder="Contoh: 1209302101" maxlength="16" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="no_identitas_icon"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                @error('no_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Jenis Identitas (required for perorangan) -->
              <div class="mb-2" id="jenis-identitas-container">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-card-heading me-1"></i>Jenis Identitas
                </label>
                <div class="input-group">
                  <select class="form-select" name="jns_identitas" required>
                    <option value="">Pilih jenis identitas...</option>
                    @foreach(\App\Models\Register::jenisIdentitasList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('jns_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Pekerjaan (required for perorangan) -->
              <div class="mb-2" id="pekerjaan-container">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-briefcase me-1"></i>Pekerjaan
                </label>
                <div class="input-group">
                  <select class="form-select" name="pekerjaan" required>
                    <option value="">Pilih kelompok pekerjaan...</option>
                    @foreach(\App\Models\Register::pekerjaanList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('pekerjaan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Alamat (required for perorangan) -->
              <div class="mb-2" id="alamat-container">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-geo-alt me-1"></i>Alamat
                </label>
                <div class="input-group">
                  <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap" required></textarea>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('alamat')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6">
              <!-- Tanggal Pengajuan (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-calendar-event me-1"></i>Tanggal Pengajuan
                </label>
                <div class="input-group">
                  <input type="date" class="form-control" name="tgl_pengajuan" id="tgl_pengajuan" value="{{ date('Y-m-d') }}" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('tgl_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Jenis Pengajuan (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-bank me-1"></i>Jenis Pengajuan
                </label>
                <div class="input-group">
                  <select class="form-select" name="jns_pengajuan" required>
                    <option value="">Pilih jenis pengajuan...</option>
                    @foreach(\App\Models\Register::jenisPengajuanList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('jns_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Nominal Pengajuan (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-currency-dollar me-1"></i>Nominal Pengajuan
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control" name="nominal_pengajuan" id="nominal_pengajuan" placeholder="Contoh: 1.000.000" required autocomplete="off">
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div class="form-text" style="font-size: 0.75rem;">Masukkan angka tanpa titik atau koma</div>
                @error('nominal_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Jangka Waktu Pengajuan (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-clock me-1"></i>Jangka Waktu
                </label>
                <div class="input-group">
                  <input type="text" class="form-control" name="jw_pengajuan" id="jw_pengajuan" placeholder="Contoh: 12 BULAN" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="jw_pengajuan_icon"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div class="form-text" style="font-size: 0.75rem;">Masukkan angka (contoh: 12, 24, 36)</div>
                @error('jw_pengajuan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Jaminan (required) -->
              <div class="mb-2">
                <label class="form-label fw-semibold text-success" style="font-size: 0.85rem;">
                  <i class="bi bi-shield me-1"></i>Jaminan
                </label>
                <div class="input-group">
                  <select class="form-select" name="jaminan" required>
                    <option value="">Pilih jenis jaminan...</option>
                    @foreach(\App\Models\Register::jaminanList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                @error('jaminan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <!-- Bagian upload dokumen dihapus sesuai permintaan -->
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="submit" class="btn" id="btnSimpanRegister" style="background:#1dd1a1; color:white; font-weight:600; border:none; cursor:pointer;"><i class="bi bi-save me-1"></i>Simpan Data</button>
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- ===== END Modal Tambah Data Register ===== --> 
<!-- JavaScript telah dipindahkan ke public/assets/js/register-modal.js -->
<!-- Script akan dimuat di halaman parent untuk optimasi caching --> 