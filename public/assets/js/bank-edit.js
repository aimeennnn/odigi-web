// Bank Edit JavaScript
// Form field management and validation for edit bank form

// Fungsi untuk update icon validation
function updateValidationIcon(inputElement, isValid, isEmpty) {
    // Cari container input-group dan ikon validasi dengan aman
    const groupElement = inputElement.closest(".input-group");
    if (!groupElement) return;
    const iconElement = groupElement.querySelector(".required-warning i");
    if (!iconElement) return;

    if (isEmpty) {
        // Icon tanda seru untuk field kosong
        iconElement.className = "bi bi-exclamation-triangle-fill text-warning";
    } else if (isValid) {
        // Icon centang untuk field valid
        iconElement.className = "bi bi-check-circle-fill text-success";
    } else {
        // Icon X untuk field invalid
        iconElement.className = "bi bi-x-circle-fill text-danger";
    }
}

// Fungsi validasi nomor rekening
function validateNomorRekening(value) {
    // Hanya angka 0-9, minimal 8 digit, maksimal 20 digit
    const numericRegex = /^[0-9]+$/;
    return numericRegex.test(value) && value.length >= 8 && value.length <= 20;
}

// Fungsi validasi universal untuk semua field Bank
function validateField(inputElement) {
    // Abaikan field yang disabled (misalnya register terkunci)
    if (inputElement.disabled) {
        updateValidationIcon(inputElement, true, false);
        return true;
    }
    // Abaikan input hidden atau file yang tidak memiliki ikon validasi
    if (inputElement.type === "hidden" || inputElement.type === "file") {
        return true;
    }
    const value = inputElement.value.trim();
    const fieldName = inputElement.name;
    let isValid = false;
    const isEmpty = value === "";

    // Jika field kosong, langsung return dengan status kosong
    if (isEmpty) {
        updateValidationIcon(inputElement, false, true);
        return false;
    }

    // Validasi berdasarkan jenis field
    switch (fieldName) {
        case "id_reg":
            isValid = value !== "";
            break;
        case "nama_bank":
            isValid = value.length >= 3; // Minimal 3 karakter
            break;
        case "nomor_rekening":
            isValid = validateNomorRekening(value);
            break;
        case "atas_nama":
            isValid = value.length >= 2; // Minimal 2 karakter
            break;
        case "cabang":
            isValid = value.length >= 3; // Minimal 3 karakter
            break;
        case "alamat_bank":
            isValid = value.length >= 10; // Minimal 10 karakter
            break;
        default:
            isValid = value !== "";
    }

    updateValidationIcon(inputElement, isValid, isEmpty);
    return isValid;
}

document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi validasi untuk semua field yang sudah ada nilainya
    function initializeValidation() {
        // Delay sedikit untuk memastikan DOM sudah selesai di-render
        setTimeout(() => {
            const allInputs = document.querySelectorAll(
                "input, select, textarea"
            );
            allInputs.forEach((input) => {
                if (
                    input.name &&
                    !input.disabled &&
                    input.type !== "hidden" &&
                    input.type !== "file"
                ) {
                    validateField(input);
                }
            });
            // Setelah inisialisasi, tandai field yang sudah memiliki nilai sebagai valid
            const rekening = document.getElementById("nomor_rekening");
            if (rekening && rekening.value.trim() !== "") {
                updateValidationIcon(rekening, true, false);
            }
            const statusSelect = document.getElementById("status");
            if (statusSelect && statusSelect.value.trim() !== "") {
                updateValidationIcon(statusSelect, true, false);
            }
        }, 100);
    }

    // Event listener untuk validasi real-time
    function setupRealTimeValidation() {
        // Validasi untuk input text
        document
            .querySelectorAll('input[type="text"], textarea')
            .forEach((input) => {
                input.addEventListener("input", function () {
                    // Auto uppercase untuk field tertentu
                    if (this.classList.contains("upper")) {
                        this.value = this.value.toUpperCase();
                    }

                    // Format khusus untuk nomor rekening
                    if (input.name === "nomor_rekening") {
                        this.value = this.value.replace(/[^0-9]/g, "").slice(0, 20);
                    }

                    validateField(this);
                });
                input.addEventListener("blur", function () {
                    validateField(this);
                });
                input.addEventListener("focus", function () {
                    validateField(this);
                });
            });

        // Validasi untuk select
        document.querySelectorAll("select").forEach((select) => {
            if (select.disabled) {
                updateValidationIcon(select, true, false);
                return;
            }
            select.addEventListener("change", function () {
                validateField(this);
            });
            select.addEventListener("focus", function () {
                validateField(this);
            });
            select.addEventListener("blur", function () {
                validateField(this);
            });
        });
    }

    // Jalankan setup validasi terlebih dahulu
    setupRealTimeValidation();

    // Jalankan inisialisasi validasi setelah setup selesai
    initializeValidation();

    // === DRAG & DROP + PROGRESS BAR + HAPUS FILE BARU UNTUK EDIT BANK ===
    var dz = document.getElementById('editBankDropzoneFile');
    var choose = document.getElementById('editBankChooseFile');
    var input = document.getElementById('editBankFileInput');
    var info = document.getElementById('editBankFileInfo');
    var form = input ? input.closest('form') : null;
    var tempPathInput = null;
    var fileLamaDiv = document.querySelector('.mt-2.p-2.bg-light.rounded');

    // Buat input hidden untuk temp path jika belum ada
    if (form && !document.getElementById('editBankTempPathHolder')) {
        tempPathInput = document.createElement('input');
        tempPathInput.type = 'hidden';
        tempPathInput.name = 'temp_path';
        tempPathInput.id = 'editBankTempPathHolder';
        form.appendChild(tempPathInput);
    } else {
        tempPathInput = document.getElementById('editBankTempPathHolder');
    }

    if (dz && choose && input && info && tempPathInput) {
        // Event: klik 'Choose file'
        choose.addEventListener('click', function(e) {
            e.preventDefault();
            input.click();
        });

        // Drag & drop events
        ['dragenter','dragover','dragleave','drop'].forEach(function(n) {
            dz.addEventListener(n, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        dz.addEventListener('dragenter', function() { dz.classList.add('dragover'); });
        dz.addEventListener('dragover', function() { dz.classList.add('dragover'); });
        dz.addEventListener('dragleave', function() { dz.classList.remove('dragover'); });
        dz.addEventListener('drop', function(e) {
            dz.classList.remove('dragover');
            var files = e.dataTransfer.files;
            if (files && files[0]) {
                input.files = files;
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });

        // Event: pilih file manual
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                startTempUpload(this.files[0]);
                this.value = '';
            }
        });

        function startTempUpload(file) {
            // Reset preview
            info.innerHTML = '';
            tempPathInput.value = '';
            if (!file) return;
            var allowed = ['application/pdf','image/jpeg','image/jpg','image/png'];
            if (!allowed.includes(file.type)) {
                alert('Format tidak didukung');
                return;
            }
            if (file.size > 2*1024*1024) {
                alert('Maks 2MB per file');
                return;
            }
            // Sembunyikan file lama
            if (fileLamaDiv) fileLamaDiv.style.display = 'none';
            var kb = (file.size/1024).toFixed(1);
            var row = document.createElement('div');
            row.className = 'p-2 border rounded bg-white d-flex align-items-center justify-content-between gap-2';
            row.innerHTML = `
                <div class="flex-grow-1 w-100" style="min-width:0;">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1" style="min-width:0;">
                        <div class="d-flex align-items-center gap-2 text-truncate" style="min-width:0;">
                            <i class="bi bi-file-earmark me-1"></i>
                            <span class="text-truncate" style="max-width: 360px;">${file.name}</span>
                            <small class="text-muted ms-2">${kb} KB</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" id="editBankClearSelected"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="progress" style="height:8px; width:100%;"><div class="progress-bar" style="width:0%"></div></div>
                    <div class="d-flex justify-content-end"><small class="pct text-primary mt-1">0%</small></div>
                </div>
            `;
            info.appendChild(row);
            var clearBtn = row.querySelector('#editBankClearSelected');
            var bar = row.querySelector('.progress-bar');
            var pct = row.querySelector('.pct');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(){
                    info.innerHTML = '';
                    tempPathInput.value = '';
                    // Tampilkan kembali file lama
                    if (fileLamaDiv) fileLamaDiv.style.display = '';
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
            // AJAX upload
            var formData = new FormData();
            formData.append('files[]', file);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/bank/upload-temp-new');
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if(token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
            xhr.setRequestHeader('Accept','application/json');
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var pctVal = Math.round((e.loaded/e.total)*100);
                    if (bar) bar.style.width = pctVal + '%';
                    if (pct) pct.textContent = pctVal + '%';
                }
            };
            xhr.onload = function() {
                try {
                    var res = JSON.parse(xhr.responseText||'{}');
                    if (xhr.status>=200 && xhr.status<300 && res.success) {
                        var newPath = Array.isArray(res.temp_paths) ? res.temp_paths[0] : res.temp_paths;
                        tempPathInput.value = newPath || '';
                        if(bar) bar.classList.add('bg-success');
                        if(pct) pct.textContent = '100%';
                    } else {
                        info.innerHTML = '<div class="text-danger small">Upload gagal: ' + (res.message||'') + '</div>';
                        tempPathInput.value = '';
                        // Tampilkan kembali file lama
                        if (fileLamaDiv) fileLamaDiv.style.display = '';
                    }
                } catch {
                    info.innerHTML = '<div class="text-danger small">Upload gagal</div>';
                    tempPathInput.value = '';
                    if (fileLamaDiv) fileLamaDiv.style.display = '';
                }
            };
            xhr.onerror = function() {
                info.innerHTML = '<div class="text-danger small">Upload gagal (jaringan)</div>';
                tempPathInput.value = '';
                if (fileLamaDiv) fileLamaDiv.style.display = '';
            };
            xhr.send(formData);
        }
    }
});
