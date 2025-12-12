<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
<!-- Modal Tambah Data -->
<div class="modal fade" id="modalTambahData" tabindex="-1" aria-labelledby="modalTambahDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 18px;">
            <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
                <h5 class="modal-title" id="modalTambahDataLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data Nasabah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('data.store') }}" method="POST" enctype="multipart/form-data" id="dataForm">
                @csrf
                @if(request('register_id'))
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                @endif
                <div class="modal-body" style="padding: 1.25rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Pilih Register</label>
                                <div class="input-group">
                                    @if(request('id_reg') || request('register_id'))
                                        @php
                                            $idReg = request('id_reg') ?? request('register_id');
                                            $reg = App\Models\Register::find($idReg);
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
                                <label class="form-label fw-bold text-primary">Jenis Data</label>
                                <div class="input-group">
                                    <select class="form-select" name="jenis_data" required>
                                        <option value="">Pilih jenis data...</option>
                                        @foreach(\App\Models\Data::jenisDataList() as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Keterangan</label>
                                <div class="input-group">
                                    <textarea class="form-control upper" name="keterangan" rows="2" required></textarea>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                                @error('keterangan')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary">Upload File</label>
                                <div class="input-group">
                                    <div id="dataDropZoneInline" class="border border-2 rounded-3 p-4 text-center flex-grow-1" style="border-style:dashed; background:#f7faff;">
                                        <div style="font-size:2rem; color:#49bbca;" class="mb-2"><i class="bi bi-cloud-arrow-up"></i></div>
                                        <div class="fw-semibold">Drag and drop files here</div>
                                        <div class="text-muted small">or <a href="#" id="dataChooseFiles">Choose file</a></div>
                                        <input type="file" id="fileInputMulti" name="file" class="d-none @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="fileUploadIcon"><i class="bi bi-exclamation-circle"></i></span>
                                </div>
                                @error('file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">PDF, JPG, JPEG, PNG (maks. 2MB per file)</small>
                                <div id="dataFileList" class="mt-3 d-flex flex-column gap-2"></div>
                                <input type="hidden" name="temp_paths[]" id="tempPathsHolder">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e6f9f5;">
                    <button type="submit" class="btn btn-success" id="btnSimpanData" style="background:#1dd1a1; color:white; font-weight:600; border:none; cursor:pointer;">
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

<!-- JavaScript sudah dipindahkan ke file eksternal data-modal.js -->
