<link rel="stylesheet" href="{{ asset('assets/css/data_style.css') }}">
<!-- Modal Upload File Data (tanpa status) -->
<div class="modal fade" id="modalUploadData" tabindex="-1" aria-labelledby="modalUploadDataLabel" aria-hidden="true">
	<div class="modal-dialog" style="max-width: 720px;">
		<div class="modal-content" style="border-radius: 18px; overflow: hidden;">
			<div class="modal-header" style="background: linear-gradient(90deg, #49bbca 0%, #1dd1a1 100%);">
				<h5 class="modal-title text-white" id="modalUploadDataLabel">Upload Dokumen Data</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form method="POST" action="" enctype="multipart/form-data" id="formUploadData">
				@csrf
				@if(request('register_id'))
					<input type="hidden" name="register_id" value="{{ request('register_id') }}">
				@endif
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label fw-bold text-primary">Pilih File Dokumen</label>
						<div id="dataDropzone" class="upload-dropzone">
							<div class="dz-icon"><i class="bi bi-cloud-arrow-up"></i></div>
							<div class="fw-semibold mb-1">Drag and drop file here</div>
							<div class="upload-helper">or <a href="#" id="chooseDataFile">Choose file</a></div>
							<input type="file" class="d-none" name="hasil" id="dataFileInput" accept=".pdf,.jpg,.jpeg,.png" required>
						</div>
						<div class="form-text mt-2">Format yang didukung: PDF, JPG, JPEG, PNG. Maksimal 2MB.</div>
					</div>
					<div id="dataProgressWrapper" class="d-none">
						<div id="dataProgressList" class="progress-list"></div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
	const uploadButtons = document.querySelectorAll('.btn-upload-data');
	const formUpload = document.getElementById('formUploadData');
	const dropzone = document.getElementById('dataDropzone');
	const chooseLink = document.getElementById('chooseDataFile');
	const fileInput = document.getElementById('dataFileInput');
	const progressWrapper = document.getElementById('dataProgressWrapper');
	const progressList = document.getElementById('dataProgressList');
	let pendingTempPath = null;
	let uploading = false;

	function bytesToSize(bytes){ if(bytes===0) return '0 B'; const k=1024, sizes=['B','KB','MB','GB']; const i=Math.floor(Math.log(bytes)/Math.log(k)); return parseFloat((bytes/Math.pow(k,i)).toFixed(2))+' '+sizes[i]; }

	uploadButtons.forEach(btn => {
		btn.addEventListener('click', function(){
			const id = this.getAttribute('data-id');
			formUpload.setAttribute('action', /slik/upload/${id}); // reuse final endpoint
			pendingTempPath = null; uploading = false; formUpload.reset();
			progressWrapper.classList.add('d-none'); progressList.innerHTML = '';
		});
	});

	chooseLink.addEventListener('click', function(e){ e.preventDefault(); fileInput.click(); });

	['dragenter','dragover','dragleave','drop'].forEach(name => {
		dropzone.addEventListener(name, e => { e.preventDefault(); e.stopPropagation(); });
	});
	dropzone.addEventListener('dragenter', () => dropzone.classList.add('dragover'));
	dropzone.addEventListener('dragover', () => dropzone.classList.add('dragover'));
	dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
	dropzone.addEventListener('drop', e => {
		dropzone.classList.remove('dragover');
		const files = e.dataTransfer.files; if(files && files[0]) { fileInput.files = files; fileInput.dispatchEvent(new Event('change', { bubbles:true })); }
	});

	function startTempUpload(file){
		if(uploading) return; uploading = true;
		progressWrapper.classList.remove('d-none'); progressList.innerHTML='';
		const item = document.createElement('div');
		item.className='progress-item';
		item.innerHTML = `
			<div class="progress-thumb"><i class="bi bi-file-earmark"></i></div>
			<div class="flex-grow-1">
				<div class="fw-semibold">${file.name}</div>
				<div class="text-muted small">${bytesToSize(file.size)}</div>
				<div class="progress mt-2"><div class="progress-bar" role="progressbar" style="width:0%"></div></div>
			</div>
			<div class="fw-semibold text-primary percent" style="min-width:60px;">0%</div>`;
		progressList.appendChild(item);
		const bar = item.querySelector('.progress-bar');
		const percent = item.querySelector('.percent');

		const formData = new FormData(); formData.append('hasil', file);
		const id = formUpload.getAttribute('action').split('/').pop();
		const xhr = new XMLHttpRequest();
		xhr.open('POST', ${window.location.origin}/slik/upload-temp/${id});
		const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		if(token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('Accept', 'application/json');
		xhr.upload.onprogress = e => { if(e.lengthComputable){ const p = Math.round((e.loaded/e.total)*100); bar.style.width = p+'%'; percent.textContent = p+'%'; } };
		xhr.onload = () => {
			try { const res = JSON.parse(xhr.responseText||'{}'); if(xhr.status>=200 && xhr.status<300 && res.success){ pendingTempPath = res.temp_path; percent.textContent = '100%'; } else { alert(res.message||'Upload gagal.'); uploading = false; } }
			catch{ alert('Upload gagal.'); uploading=false; }
		};
		xhr.onerror = () => { alert('Kesalahan jaringan saat upload.'); uploading=false; };
		xhr.send(formData);
	}

	fileInput.addEventListener('change', function(){
		const file = this.files[0]; if(!file) return;
		const allowed=['application/pdf','image/jpeg','image/jpg','image/png'];
		if(!allowed.includes(file.type)){ alert('Format file tidak didukung!'); this.value=''; return; }
		if(file.size > 2*1024*1024){ alert('Ukuran file maksimal 2MB!'); this.value=''; return; }
		startTempUpload(file);
	});

	formUpload.addEventListener('submit', function(e){
		e.preventDefault();
		if(!pendingTempPath){ alert('Tunggu hingga 100% terlebih dahulu.'); return; }
		const id = formUpload.getAttribute('action').split('/').pop();
		const fd = new FormData(); fd.append('temp_path', pendingTempPath);
		const registerIdInput = this.querySelector('input[name="register_id"]'); if(registerIdInput){ fd.append('register_id', registerIdInput.value); }
		const xhr = new XMLHttpRequest(); xhr.open('POST', /slik/upload/${id});
		const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); if(token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
		xhr.setRequestHeader('X-Requested-With','XMLHttpRequest'); xhr.setRequestHeader('Accept','application/json');
		xhr.onload = () => { try{ const res = JSON.parse(xhr.responseText||'{}'); if(xhr.status>=200 && xhr.status<300 && res.success){ window.location.reload(); } else { alert(res.message||'Gagal menyimpan.'); } } catch{ window.location.reload(); } };
		xhr.onerror = () => alert('Kesalahan jaringan saat menyimpan.');
		xhr.send(fd);
	});
});
</script>
@endpush