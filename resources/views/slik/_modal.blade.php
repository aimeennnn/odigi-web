<!-- Modal Tambah Data SLIK -->
<div class="modal fade" id="modalTambahSlik" tabindex="-1" aria-labelledby="modalTambahSlikLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 720px;">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
        <h5 class="modal-title text-white" id="modalTambahSlikLabel">Tambah Data SLIK</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('slik.store') }}" enctype="multipart/form-data">
        @csrf
        @if(request('register_id'))
            <input type="hidden" name="register_id" value="{{ request('register_id') }}">
        @endif
        <div class="modal-body" style="padding: 1.25rem;">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">Nomor SLIK</label>
                <div class="input-group">
                  <input type="text" class="form-control bg-light" name="nomor" value="{{ $nomor_slik_berikutnya ?? '' }}" readonly required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">Pilih Register (Nomor / Nama Nasabah)</label>
                <div class="input-group">
                  @if(request('register_id'))
                  @php
                    $currentReg = isset($registers) ? $registers->firstWhere('id_reg', request('register_id')) : null;
                  @endphp
                  <input type="text" class="form-control bg-light" value="{{ $currentReg ? ($currentReg->nomor.' - '.$currentReg->nama) : '' }}" readonly>
                  <input type="hidden" name="id_reg" id="id_reg_hidden" value="{{ request('register_id') }}">
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                  @else
                  @php
                    $reg = isset($registers) ? $registers->first() : null;
                  @endphp
                  <input type="text" class="form-control bg-light" value="{{ $reg ? ($reg->nomor.' - '.$reg->nama) : '' }}" readonly>
                  <input type="hidden" name="id_reg" id="id_reg_hidden" value="{{ old('id_reg', $reg->id_reg ?? '') }}">
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                @endif
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">Nama Nasabah</label>
                <div class="input-group">
                  <input type="text" class="form-control upper" id="nama_nasabah" name="nama"required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">No Identitas</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="no_identitas_nasabah" name="no_identitas" maxlength="16" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="no_identitas_icon"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div class="form-text" style="font-size: 0.75rem;">Masukkan 14-16 digit angka</div>
                @error('no_identitas')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">Tanggal</label>
                <div class="input-group">
                  <input type="text" class="form-control bg-light" id="tgl_display" value="" readonly>
                  <input type="hidden" name="tgl" id="tgl_value" value="">
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-2">
                <label class="form-label fw-bold text-primary">Keterkaitan</label>
                <div class="input-group">
                  <select class="form-select" name="keterkaitan" required>
                    <option value="">Pilih keterkaitan...</option>
                    @foreach(\App\Models\Slik::keterkaitanList() as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="submit" class="btn" style="background:#1dd1a1; color:white; font-weight:600;"><i class="bi bi-save me-1"></i>Simpan Data</button>
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END Modal Tambah Data SLIK -->

<!-- Upload Modal Styles -->
<style>
	/* Container */
	#modalUploadHasil .modal-content { border-radius: 18px; overflow: hidden; }
	#modalUploadHasil .modal-body { padding-top: 1.25rem; }

	/* Upload Section */
	.upload-section {
		margin-bottom: 24px;
		padding: 16px;
		border: 1px solid #e9ecef;
		border-radius: 12px;
		background: #ffffff;
	}

	.upload-section-title {
		font-weight: 600;
		color: #333;
		margin-bottom: 12px;
		font-size: 1rem;
	}

	.upload-section-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 12px;
	}

	.btn-select-file {
		background: #e3f0ff;
		color: #4F8CFF;
		border: 1px solid #b3d4ff;
		border-radius: 8px;
		padding: 8px 16px;
		font-size: 0.9rem;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.2s;
	}

	.btn-select-file:hover {
		background: #d0e5ff;
		border-color: #4F8CFF;
	}

	.status-indicator {
		display: flex;
		align-items: center;
		gap: 6px;
		font-size: 0.9rem;
		color: #4F8CFF;
	}

	.status-indicator.done {
		color: #28a745;
	}

	.status-indicator.error {
		color: #dc3545;
	}

	.status-indicator.uploading {
		color: #4F8CFF;
	}

	.file-details {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 10px;
		background: #f8f9fa;
		border-radius: 8px;
		margin-top: 12px;
	}

	.file-icon {
		width: 40px;
		height: 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: #e3f0ff;
		border-radius: 8px;
		color: #4F8CFF;
		font-size: 1.2rem;
	}

	.file-info {
		flex: 1;
		min-width: 0;
	}

	.file-name {
		font-weight: 500;
		font-size: 0.9rem;
		color: #333;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.file-size {
		font-size: 0.85rem;
		color: #6c757d;
		margin-top: 2px;
	}

	.file-error {
		font-size: 0.85rem;
		color: #dc3545;
		margin-top: 4px;
	}

	.file-actions {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.btn-file-action {
		width: 32px;
		height: 32px;
		border-radius: 50%;
		border: none;
		background: #f0f0f0;
		color: #666;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		transition: all 0.2s;
	}

	.btn-file-action:hover {
		background: #e0e0e0;
	}

	.btn-file-action.delete {
		background: #fee;
		color: #dc3545;
	}

	.btn-file-action.delete:hover {
		background: #fcc;
	}

	.progress-container {
		margin-top: 8px;
	}

	.progress {
		height: 6px;
		border-radius: 6px;
		overflow: hidden;
		background: #eef2f7;
		margin-top: 8px;
	}

	.progress-bar {
		background: linear-gradient(90deg, #4F8CFF, #6ad7c6);
		height: 100%;
		transition: width 0.3s;
	}

	.progress-percent {
		font-size: 0.85rem;
		color: #4F8CFF;
		margin-top: 4px;
		text-align: right;
	}

	.max-size-info {
		font-size: 0.85rem;
		color: #4F8CFF;
		margin-top: 8px;
	}

	/* Footer buttons */
	#modalUploadHasil .btn-primary { background: #58cbdd; border-color: #58cbdd; }
	#modalUploadHasil .btn-primary:hover { background: #42b7c9; border-color: #42b7c9; }
</style>

<!-- Modal Upload Hasil File -->
<div class="modal fade" id="modalUploadHasil" tabindex="-1" aria-labelledby="modalUploadHasilLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(90deg, #4F8CFF 0%, #b2f2e5 100%);">
        <h5 class="modal-title text-white" id="modalUploadHasilLabel">Upload Dokumen Hasil SLIK</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data" id="formUploadHasil">
        @csrf
        <div class="modal-body">
          <!-- Upload File 1 -->
          <div class="upload-section" id="uploadSection1">
            <div class="upload-section-header">
              <div class="upload-section-title">Upload document 1:</div>
              <div class="d-flex align-items-center gap-3">
                <span class="status-indicator" id="statusIndicator1">
                  <i class="bi bi-circle"></i>
                  <span>Ready</span>
                </span>
                <button type="button" class="btn-select-file" id="btnSelectFile1">Select files...</button>
              </div>
            </div>
            <input type="file" class="d-none" name="hasil" id="fileInput1" accept=".pdf,.jpg,.jpeg,.png">
            <div id="fileDetails1" class="d-none">
              <div class="file-details">
                <div class="file-icon">
                  <i class="bi bi-file-earmark" id="fileIcon1"></i>
                </div>
                <div class="file-info">
                  <div class="file-name" id="fileName1"></div>
                  <div class="file-size" id="fileSize1"></div>
                  <div class="file-error d-none" id="fileError1"></div>
                  <div class="progress-container d-none" id="progressContainer1">
                    <div class="progress">
                      <div class="progress-bar" id="progressBar1" style="width: 0%"></div>
                    </div>
                    <div class="progress-percent" id="progressPercent1">0%</div>
                  </div>
                </div>
                <div class="file-actions">
                  <button type="button" class="btn-file-action retry d-none" id="btnRetry1" title="Retry">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                  <button type="button" class="btn-file-action delete" id="btnDelete1" title="Hapus">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="max-size-info">Maximum allowed file size is 2MB.</div>
          </div>

          <!-- Upload File 2 -->
          <div class="upload-section" id="uploadSection2">
            <div class="upload-section-header">
              <div class="upload-section-title">Upload document 2:</div>
              <div class="d-flex align-items-center gap-3">
                <span class="status-indicator" id="statusIndicator2">
                  <i class="bi bi-circle"></i>
                  <span>Ready</span>
                </span>
                <button type="button" class="btn-select-file" id="btnSelectFile2">Select files...</button>
              </div>
            </div>
            <input type="file" class="d-none" name="hasil2" id="fileInput2" accept=".pdf,.jpg,.jpeg,.png">
            <div id="fileDetails2" class="d-none">
              <div class="file-details">
                <div class="file-icon">
                  <i class="bi bi-file-earmark" id="fileIcon2"></i>
                </div>
                <div class="file-info">
                  <div class="file-name" id="fileName2"></div>
                  <div class="file-size" id="fileSize2"></div>
                  <div class="file-error d-none" id="fileError2"></div>
                  <div class="progress-container d-none" id="progressContainer2">
                    <div class="progress">
                      <div class="progress-bar" id="progressBar2" style="width: 0%"></div>
                    </div>
                    <div class="progress-percent" id="progressPercent2">0%</div>
                  </div>
                </div>
                <div class="file-actions">
                  <button type="button" class="btn-file-action retry d-none" id="btnRetry2" title="Retry">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                  <button type="button" class="btn-file-action delete" id="btnDelete2" title="Hapus">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="max-size-info">Maximum allowed file size is 2MB.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-primary">Status SLIK</label>
            <select class="form-select" name="status_update" id="status_update">
              @foreach(\App\Models\Slik::statusList() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
            <div class="form-text">Update status SLIK setelah upload dokumen (opsional)</div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-upload me-1"></i>Upload
          </button>
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Batal
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript sudah dipindahkan ke file eksternal slik-modal.js -->