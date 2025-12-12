<!-- Modal Aksi Upload Register (Realisasi) -->
<div class="modal fade" id="modalAksiUpload" tabindex="-1" aria-labelledby="modalAksiUploadLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAksiUploadRegister" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: #fff;">
          <h5 class="modal-title" id="modalAksiUploadLabel"><i class="bi bi-upload me-2" style="color: #000;"></i>Realisasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Nasabah</label>
            <textarea name="nama" class="form-control bg-light text-dark fw-semibold text-center" style="text-align: center; min-height: 38px; resize: none; overflow-y: auto; line-height: 1.5; padding: 0.375rem 0.75rem;" readonly rows="1"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Nominal Pengajuan</label>
            <input type="text" name="nominal_pengajuan" class="form-control bg-light fw-semibold text-dark text-start" style="text-align: left;" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Nominal Disetujui</label>
            <input type="text" name="nominal_disetujui" class="form-control format-rupiah text-start" style="text-align: left;" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tanggal Realisasi</label>
            <input type="date" name="tanggal_realisasi" class="form-control text-start" style="text-align: left;" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select text-start" style="text-align: left;" required>
              <option value="">-- Pilih Status --</option>
              @foreach(\App\Models\Register::statusRealisasiList() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success" style="background:#1dd1a1;color:#fff;font-weight:bold;border-color:#1dd1a1;">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>

