@extends('layout.master')

@section('main-content')
<!-- Tambahkan link ke CSS data -->
<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
<div class="container mt-4">
    <!-- Header Gradient -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Edit Data Nasabah</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Ubah informasi data tambahan nasabah</div>
        </div>
        <span style="font-size:4rem; color:#fff; opacity:0.15;"><i class="bi bi-folder2-open"></i></span>
    </div>
    
    <!-- TOMBOL AKSI -->
    <!-- <div class="mb-3 d-flex gap-2 justify-content-end">
        @php $encId = strtr(\Illuminate\Support\Facades\Crypt::encryptString($data->id_data), ['+' => '-', '/' => '_', '=' => '.']); @endphp
        <a href="{{ route('data.show', $encId) }}" class="btn btn-dark">Kembali ke Detail</a>
        @if(request('register_id'))
            <a href="{{ route('data.index', ['register_id' => request('register_id')]) }}" class="btn btn-outline-secondary">Data Tambahan</a>
        @else
            <a href="{{ route('data.index') }}" class="btn btn-outline-secondary">Data Tambahan</a>
        @endif
    </div> -->

    <!-- Form Edit -->
    <div class="card shadow-sm" style="border-radius: 18px;">
        <div class="card-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); color: white; border-radius: 18px 18px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit Data Nasabah</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('data.update', $data->id_data) }}" method="POST" enctype="multipart/form-data">
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
                                <input type="text" id="id_reg_display" class="form-control bg-light upper @error('id_reg') is-invalid @enderror" readonly
                                       value="{{ optional($registers->firstWhere('id_reg', $data->id_reg))->nomor }} - {{ optional($registers->firstWhere('id_reg', $data->id_reg))->nama }}">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <input type="hidden" name="id_reg" value="{{ $data->id_reg }}">
                            @error('id_reg')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="jenis_data" class="form-label fw-bold">Jenis Data <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('jenis_data') is-invalid @enderror" name="jenis_data" id="jenis_data" required>
                                    @php $selectedJenisData = old('jenis_data', $data->jenis_data); @endphp
                                    <option value="">Pilih jenis data...</option>
                                    @foreach(\App\Models\Data::jenisDataList() as $key => $label)
                                        <option value="{{ $key }}" {{ $selectedJenisData == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('jenis_data')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Keterangan -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-card-text me-2"></i>Keterangan</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                            <div class="input-group">
                                <textarea class="form-control upper @error('keterangan') is-invalid @enderror" 
                                          name="keterangan" id="keterangan" rows="3" required>{{ old('keterangan', $data->keterangan) }}</textarea>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- File Upload -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark me-2"></i>File & Dokumen</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Upload File Baru (Opsional)</label>
                            <div id="editDataDropzoneFile" class="upload-dropzone mb-2">
                                <div class="dz-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="fw-semibold mb-1">Drag and drop file here</div>
                                <div class="upload-helper">or <a href="#" id="editDataChooseFile">Choose file</a></div>
                                <input type="file" class="d-none @error('file') is-invalid @enderror" name="files[]" id="editDataFileInput" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="editDataSelectedFileInfo" class="mt-2"></div>
                            @if($data->file)
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        File saat ini: {{ basename($data->file) }}
                                    </small>
                                </div>
                            @endif
                            <small class="text-muted">Format yang didukung: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
                        </div>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn" style="background:#1dd1a1; color:white; font-weight:600;">
                        <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('data.show', $encId) }}" class="btn btn-danger px-4">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Drag & drop for file
    const dzFile = document.getElementById('editDataDropzoneFile');
    const fileInput = document.getElementById('editDataFileInput');
    const chooseFile = document.getElementById('editDataChooseFile');
    const fileInfo = document.getElementById('editDataSelectedFileInfo');
    if(dzFile && fileInput && chooseFile) {
        ['dragenter','dragover','dragleave','drop'].forEach(name => {
            dzFile.addEventListener(name, e => { e.preventDefault(); e.stopPropagation(); });
        });
        dzFile.addEventListener('dragenter', () => dzFile.classList.add('dragover'));
        dzFile.addEventListener('dragover', () => dzFile.classList.add('dragover'));
        dzFile.addEventListener('dragleave', () => dzFile.classList.remove('dragover'));
        dzFile.addEventListener('drop', e => {
            dzFile.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
        chooseFile.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });

        // Tampilkan nama file yang dipilih dengan bar progres sederhana
        fileInput.addEventListener('change', function() {
            if (!fileInfo) return;
            fileInfo.innerHTML = '';
            const f = this.files && this.files[0] ? this.files[0] : null;
            if (!f) return;
            const kb = (f.size/1024).toFixed(1);
            const row = document.createElement('div');
            row.className = 'p-2 border rounded bg-white d-flex align-items-center justify-content-between gap-2';
            row.innerHTML = `
                <div class="flex-grow-1 w-100" style="min-width:0;">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1" style="min-width:0;">
                        <div class="d-flex align-items-center gap-2 text-truncate" style="min-width:0;">
                            <i class="bi bi-file-earmark me-1"></i>
                            <span class="text-truncate" style="max-width: 360px;">${f.name}</span>
                            <small class="text-muted ms-2">${kb} KB</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" id="editDataClearSelected"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="progress" style="height:8px; width:100%;"><div class="progress-bar" style="width:0%"></div></div>
                    <div class="d-flex justify-content-end"><small class="pct text-primary mt-1">0%</small></div>
                </div>
            `;
            fileInfo.appendChild(row);
            const clearBtn = row.querySelector('#editDataClearSelected');
            const bar = row.querySelector('.progress-bar');
            const pct = row.querySelector('.pct');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(){
                    fileInput.value = '';
                    fileInfo.innerHTML = '';
                });
            }
            // Animasi progres sederhana
            let p = 0;
            const timer = setInterval(() => {
                p += 10;
                if (p >= 100) { p = 100; clearInterval(timer); if(bar) bar.classList.add('bg-success'); }
                if (bar) bar.style.width = p + '%';
                if (pct) pct.textContent = p + '%';
            }, 60);
        });
    }
    // Auto uppercase untuk field tertentu
    document.querySelectorAll('.upper').forEach(function(field) {
        field.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
});
</script>

<!-- JavaScript untuk validasi Data -->
<script src="{{ asset('assets/js/data-edit.js') }}"></script>
@endsection 