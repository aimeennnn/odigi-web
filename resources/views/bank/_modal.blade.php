<!-- Modal Tambah Data Bank -->
<div class="modal fade" id="modalTambahBank" tabindex="-1" aria-labelledby="modalTambahBankLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 18px;">
            <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
                <h5 class="modal-title" id="modalTambahBankLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data Bank
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bank.store') }}" method="POST" enctype="multipart/form-data" id="bankForm">
                @csrf
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <div class="modal-body" style="padding: 1.25rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Pilih Register (Nomor / Nama Nasabah)</label>
                                <div class="input-group">
                                    @if(request('id_reg') || request('register_id'))
                                        @php
                                            $idReg = request('id_reg') ?? request('register_id');
                                            $reg = $registers->where('id_reg', $idReg)->first();
                                        @endphp
                                        <input type="text" class="form-control bg-light" value="{{ $reg ? ($reg->nomor . ' - ' . $reg->nama) : 'Registrasi' }}" readonly>
                                        <input type="hidden" name="id_reg" value="{{ $idReg }}">
                                    @else
                                        @php
                                            $reg = isset($registers) ? $registers->first() : null;
                                        @endphp
                                        <input type="text" class="form-control bg-light" value="{{ $reg ? ($reg->nomor . ' - ' . $reg->nama) : 'Registrasi' }}" readonly>
                                        <input type="hidden" name="id_reg" value="{{ old('id_reg', $reg->id_reg ?? '') }}">
                                    @endif
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Nama Bank</label>
                                <div class="input-group">
                                    <select class="form-select" name="nama_bank" id="nama_bank" required>
                                        <option value="">Pilih bank...</option>
                                        @foreach(\App\Models\Bank::namaBankList() as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">No Rekening</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="no_rekening" id="no_rekening" value="{{ old('no_rekening') }}" maxlength="16" required>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="no_rekening_icon"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                                <div class="form-text" style="font-size: 0.75rem;">Masukkan 10-16 digit angka</div>
                                @error('no_rekening')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Upload File</label>
                                <div class="input-group">
                                    <div id="bankDropZoneInline" class="border border-2 rounded-3 p-4 text-center flex-grow-1" style="border-style:dashed; background:#f7faff;">
                                        <div style="font-size:2rem; color:#4F8CFF;" class="mb-2"><i class="bi bi-cloud-arrow-up"></i></div>
                                        <div class="fw-semibold">Drag and drop files here</div>
                                        <div class="text-muted small">or <a href="#" id="bankChooseFiles">Choose file</a></div>
                                        <input type="file" id="bankFileInputMulti" name="file" class="d-none @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="bankFileUploadIcon"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                                @error('file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">PDF, JPG, JPEG, PNG (maks. 2MB per file)</small>
                                <div id="bankFileList" class="mt-3 d-flex flex-column gap-2"></div>
                                <input type="hidden" name="temp_paths[]" id="bankTempPathsHolder">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e6f9f5;">
                    <button type="submit" class="btn btn-success" id="btnSimpanBank" style="background:#1dd1a1; color:white; font-weight:600; border:none; cursor:pointer;">
                        <i class="bi bi-save me-1"></i>Simpan Data
                    </button>
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript sudah dipindahkan ke file eksternal bank-modal.js -->
