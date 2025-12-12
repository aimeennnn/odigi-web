// SLIK Modal JavaScript - Semua fungsi untuk modal tambah data dan upload hasil

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal tambah data functionality
    initTambahDataModal();
    
    // Initialize upload hasil modal functionality
    initUploadHasilModal();
    
    // Initialize auto date setting
    initAutoDate();
    
    // Initialize register auto-fill
    initRegisterAutoFill();
    
    // Initialize field validation
    initFieldValidation();
});

// Modal Tambah Data Functionality
function initTambahDataModal() {
    const form = document.querySelector('#modalTambahSlik form');
    const requiredFields = document.querySelectorAll('.required-warning');
    
    if (form) {
        // Inisialisasi status icon saat modal dibuka
        const modal = document.getElementById('modalTambahSlik');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                const inputs = form.querySelectorAll('input[type="text"], input[type="hidden"], select');
                inputs.forEach(input => {
                    updateFieldIcon(input);
                });
                
                // Update icon untuk field yang sudah terisi otomatis
                setTimeout(function() {
                    const labels = document.querySelectorAll('#modalTambahSlik .form-label');
                    labels.forEach(label => {
                        const inputGroup = label.nextElementSibling;
                        if(inputGroup && inputGroup.classList.contains('input-group')) {
                            const input = inputGroup.querySelector('input[readonly], select');
                            if(input && input.value && window.updateFieldIcon) {
                                updateFieldIcon(input);
                            }
                        }
                    });
                }, 100);
            });
        }
        
        form.addEventListener('submit', function() {
            requiredFields.forEach(field => {
                field.style.display = 'block';
                // Reset ke icon warning saat submit
                const icon = field.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-exclamation-circle';
                    field.classList.remove('text-success');
                    field.classList.add('text-danger');
                }
            });
        });
        
        form.addEventListener('input', function(e) {
            updateFieldIcon(e.target);
        });
        
        // Tambahkan event listener untuk select dropdown
        form.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                updateFieldIcon(e.target);
            }
        });
    }
}

// Upload Hasil Modal Functionality
function initUploadHasilModal() {
    const uploadButtons = document.querySelectorAll('.btn-upload-hasil');
    const formUpload = document.getElementById('formUploadHasil');
    
    // State untuk 2 file upload
    let currentSlikId = null;
    let fileStates = {
        1: { file: null, tempPath: null, originalName: null, uploading: false, xhr: null, isExisting: false, toDelete: false },
        2: { file: null, tempPath: null, originalName: null, uploading: false, xhr: null, isExisting: false, toDelete: false }
    };

    // Set action and load existing files when opening modal
    uploadButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentSlikId = this.getAttribute('data-id');
            if (formUpload) {
                formUpload.setAttribute('action', `/slik/upload/${currentSlikId}`);
                document.getElementById('status_update').value = 'selesai';
                // Reset state
                resetFileState(1);
                resetFileState(2);
                loadExistingFiles(currentSlikId);
            }
        });
    });

    // Reset file state
    function resetFileState(fileNum) {
        const fileDetails = document.getElementById(`fileDetails${fileNum}`);
        const fileInput = document.getElementById(`fileInput${fileNum}`);
        const progressContainer = document.getElementById(`progressContainer${fileNum}`);
        const fileError = document.getElementById(`fileError${fileNum}`);
        const btnRetry = document.getElementById(`btnRetry${fileNum}`);
        
        if (fileDetails) fileDetails.classList.add('d-none');
        if (fileInput) fileInput.value = '';
        if (progressContainer) progressContainer.classList.add('d-none');
        if (fileError) {
            fileError.classList.add('d-none');
            fileError.textContent = '';
        }
        if (btnRetry) btnRetry.classList.add('d-none');
        
        // Reset state
        fileStates[fileNum].file = null;
        fileStates[fileNum].tempPath = null;
        fileStates[fileNum].originalName = null;
        fileStates[fileNum].uploading = false;
        fileStates[fileNum].xhr = null;
        fileStates[fileNum].isExisting = false;
        fileStates[fileNum].toDelete = false;
        
        updateStatusIndicator(fileNum, 'ready');
    }

    // Initialize file inputs
    const fileInput1 = document.getElementById('fileInput1');
    const fileInput2 = document.getElementById('fileInput2');
    const btnSelect1 = document.getElementById('btnSelectFile1');
    const btnSelect2 = document.getElementById('btnSelectFile2');
    const btnDelete1 = document.getElementById('btnDelete1');
    const btnDelete2 = document.getElementById('btnDelete2');
    const btnRetry1 = document.getElementById('btnRetry1');
    const btnRetry2 = document.getElementById('btnRetry2');

    // File 1 handlers
    if (btnSelect1 && fileInput1) {
        btnSelect1.addEventListener('click', () => fileInput1.click());
        fileInput1.addEventListener('change', (e) => handleFileSelect(e.target.files[0], 1));
        if (btnDelete1) btnDelete1.addEventListener('click', () => removeFile(1));
        if (btnRetry1) btnRetry1.addEventListener('click', () => retryUpload(1));
    }

    // File 2 handlers
    if (btnSelect2 && fileInput2) {
        btnSelect2.addEventListener('click', () => fileInput2.click());
        fileInput2.addEventListener('change', (e) => handleFileSelect(e.target.files[0], 2));
        if (btnDelete2) btnDelete2.addEventListener('click', () => removeFile(2));
        if (btnRetry2) btnRetry2.addEventListener('click', () => retryUpload(2));
    }

    // Load existing files from server
    function loadExistingFiles(slikId) {
        fetch(`/slik/get-files/${slikId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hasil adalah array, ambil file pertama untuk upload document 1
                if (data.hasil && data.hasil.length > 0) {
                    displayExistingFile(data.hasil[0], 1);
                }
                // Hasil adalah array, ambil file kedua untuk upload document 2
                if (data.hasil && data.hasil.length > 1) {
                    displayExistingFile(data.hasil[1], 2);
                }
                // Hasil2 dikunci "Dalam Proses" - tidak ditampilkan di modal upload
            }
        })
        .catch(err => {
            console.error('Error loading existing files:', err);
        });
    }

    // Display existing file
    function displayExistingFile(fileInfo, fileNum) {
        const fileDetails = document.getElementById(`fileDetails${fileNum}`);
        const fileName = document.getElementById(`fileName${fileNum}`);
        const fileSize = document.getElementById(`fileSize${fileNum}`);
        const fileIcon = document.getElementById(`fileIcon${fileNum}`);
        const statusIndicator = document.getElementById(`statusIndicator${fileNum}`);
        
        if (fileDetails && fileName && fileSize) {
            fileName.textContent = fileInfo.name || 'File';
            fileSize.textContent = fileInfo.size || '';
            
            // Set icon based on file type
            const ext = fileInfo.name ? fileInfo.name.split('.').pop().toLowerCase() : '';
            if (['jpg', 'jpeg', 'png'].includes(ext)) {
                fileIcon.className = 'bi bi-file-image';
            } else if (ext === 'pdf') {
                fileIcon.className = 'bi bi-file-pdf';
            } else {
                fileIcon.className = 'bi bi-file-earmark';
            }
            
            fileDetails.classList.remove('d-none');
            updateStatusIndicator(fileNum, 'done');
            
            // Store existing file info - tandai sebagai file existing
            fileStates[fileNum].tempPath = fileInfo.path;
            fileStates[fileNum].isExisting = true;
            fileStates[fileNum].toDelete = false;
        }
    }

    // Handle file selection
    function handleFileSelect(file, fileNum) {
        if (!file) return;
        
        // Validate file type
        const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) {
            alert('Format file tidak didukung! Hanya PDF, JPG, JPEG, PNG.');
            return;
        }
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            return;
        }
        
        // Reset state untuk file baru (hapus flag existing dan toDelete)
        fileStates[fileNum].isExisting = false;
        fileStates[fileNum].toDelete = false;
        
        fileStates[fileNum].file = file;
        fileStates[fileNum].originalName = file.name; // Simpan nama asli dari file object
        displayFileInfo(file, fileNum);
        startUpload(file, fileNum);
    }

    // Display file info
    function displayFileInfo(file, fileNum) {
        const fileDetails = document.getElementById(`fileDetails${fileNum}`);
        const fileName = document.getElementById(`fileName${fileNum}`);
        const fileSize = document.getElementById(`fileSize${fileNum}`);
        const fileIcon = document.getElementById(`fileIcon${fileNum}`);
        const fileError = document.getElementById(`fileError${fileNum}`);
        const progressContainer = document.getElementById(`progressContainer${fileNum}`);
        
        if (fileDetails && fileName && fileSize) {
            fileName.textContent = file.name;
            fileSize.textContent = bytesToSize(file.size);
            
            // Set icon
            const ext = file.name.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png'].includes(ext)) {
                fileIcon.className = 'bi bi-file-image';
            } else if (ext === 'pdf') {
                fileIcon.className = 'bi bi-file-pdf';
            } else {
                fileIcon.className = 'bi bi-file-earmark';
            }
            
            if (fileError) fileError.classList.add('d-none');
            if (progressContainer) progressContainer.classList.remove('d-none');
            fileDetails.classList.remove('d-none');
            updateStatusIndicator(fileNum, 'uploading');
        }
    }

    // Start upload
    function startUpload(file, fileNum) {
        if (fileStates[fileNum].uploading) return;
        
        fileStates[fileNum].uploading = true;
        const progressBar = document.getElementById(`progressBar${fileNum}`);
        const progressPercent = document.getElementById(`progressPercent${fileNum}`);
        
        const formData = new FormData();
        formData.append('hasil', file);
        
        const xhr = new XMLHttpRequest();
        const uploadUrl = `/slik/upload-temp/${currentSlikId}`;
        xhr.open('POST', uploadUrl);
        
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                if (progressBar) progressBar.style.width = percent + '%';
                if (progressPercent) progressPercent.textContent = percent + '%';
            }
        };
        
        xhr.onload = function() {
            fileStates[fileNum].uploading = false;
            try {
                const res = JSON.parse(xhr.responseText || '{}');
                if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                    fileStates[fileNum].tempPath = res.temp_path;
                    fileStates[fileNum].originalName = res.original_name || file.name; // Simpan nama asli
                    if (progressBar) progressBar.style.width = '100%';
                    if (progressPercent) progressPercent.textContent = '100%';
                    updateStatusIndicator(fileNum, 'done');
                    const progressContainer = document.getElementById(`progressContainer${fileNum}`);
                    if (progressContainer) progressContainer.classList.add('d-none');
                } else {
                    showFileError(fileNum, res.message || 'Upload gagal');
                    updateStatusIndicator(fileNum, 'error');
                }
            } catch (err) {
                showFileError(fileNum, 'Upload gagal');
                updateStatusIndicator(fileNum, 'error');
            }
        };
        
        xhr.onerror = function() {
            fileStates[fileNum].uploading = false;
            showFileError(fileNum, 'Terjadi kesalahan jaringan');
            updateStatusIndicator(fileNum, 'error');
        };
        
        fileStates[fileNum].xhr = xhr;
        xhr.send(formData);
    }

    // Remove file
    function removeFile(fileNum) {
        if (fileStates[fileNum].xhr) {
            fileStates[fileNum].xhr.abort();
        }
        
        // Jika file yang dihapus adalah file existing, tandai untuk dihapus dari database
        if (fileStates[fileNum].isExisting) {
            fileStates[fileNum].toDelete = true;
            fileStates[fileNum].isExisting = false;
        }
        
        fileStates[fileNum].file = null;
        fileStates[fileNum].tempPath = null;
        fileStates[fileNum].originalName = null;
        fileStates[fileNum].uploading = false;
        fileStates[fileNum].xhr = null;
        
        const fileInput = document.getElementById(`fileInput${fileNum}`);
        if (fileInput) fileInput.value = '';
        
        const fileDetails = document.getElementById(`fileDetails${fileNum}`);
        if (fileDetails) fileDetails.classList.add('d-none');
        
        updateStatusIndicator(fileNum, 'ready');
    }

    // Retry upload
    function retryUpload(fileNum) {
        const file = fileStates[fileNum].file;
        if (file) {
            startUpload(file, fileNum);
        }
    }

    // Show file error
    function showFileError(fileNum, message) {
        const fileError = document.getElementById(`fileError${fileNum}`);
        const btnRetry = document.getElementById(`btnRetry${fileNum}`);
        if (fileError) {
            fileError.textContent = message;
            fileError.classList.remove('d-none');
        }
        if (btnRetry) btnRetry.classList.remove('d-none');
    }

    // Update status indicator
    function updateStatusIndicator(fileNum, status) {
        const indicator = document.getElementById(`statusIndicator${fileNum}`);
        if (!indicator) return;
        
        const icon = indicator.querySelector('i');
        const text = indicator.querySelector('span:last-child');
        
        indicator.className = 'status-indicator ' + status;
        
        if (status === 'done') {
            icon.className = 'bi bi-check-circle';
            text.textContent = 'Done';
        } else if (status === 'error') {
            icon.className = 'bi bi-exclamation-circle';
            text.textContent = 'Error';
        } else if (status === 'uploading') {
            icon.className = 'bi bi-arrow-up';
            text.textContent = 'Uploading...';
        } else {
            icon.className = 'bi bi-circle';
            text.textContent = 'Ready';
        }
    }

    // Bytes to size
    function bytesToSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submit
    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if any file is still uploading
            if (fileStates[1].uploading || fileStates[2].uploading) {
                alert('Tunggu sampai proses upload selesai!');
                return;
            }
            
            // Langsung submit tanpa validasi file
            // Controller akan handle apakah ada file atau tidak
            finalizeUpload();
        });
    }

    // Finalize upload
    function finalizeUpload() {
        const formData = new FormData();
        
        // File 1: kirim tempPath jika ada file baru, atau flag delete jika file existing dihapus
        if (fileStates[1].toDelete) {
            formData.append('delete_hasil_1', '1');
        } else if (fileStates[1].tempPath && !fileStates[1].isExisting) {
            // Hanya kirim jika ini file baru (bukan file existing yang tidak diubah)
            formData.append('temp_path_hasil', fileStates[1].tempPath);
            if (fileStates[1].originalName) {
                formData.append('original_name_hasil', fileStates[1].originalName);
            }
        }
        
        // File 2: kirim tempPath jika ada file baru, atau flag delete jika file existing dihapus
        if (fileStates[2].toDelete) {
            formData.append('delete_hasil_2', '1');
        } else if (fileStates[2].tempPath && !fileStates[2].isExisting) {
            // Hanya kirim jika ini file baru (bukan file existing yang tidak diubah)
            formData.append('temp_path_hasil2', fileStates[2].tempPath);
            if (fileStates[2].originalName) {
                formData.append('original_name_hasil2', fileStates[2].originalName);
            }
        }
        
        formData.append('status_update', document.getElementById('status_update').value);
        
        const registerIdInput = document.querySelector('input[name="register_id"]');
        if (registerIdInput) formData.append('register_id', registerIdInput.value);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', formUpload.getAttribute('action'));
        
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        
        xhr.onload = function() {
            try {
                const res = JSON.parse(xhr.responseText || '{}');
                if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                } else {
                    alert(res.message || 'Gagal menyimpan.');
                }
            } catch (err) {
                window.location.reload();
            }
        };
        
        xhr.onerror = function() {
            alert('Terjadi kesalahan jaringan saat menyimpan.');
        };
        
        xhr.send(formData);
    }
}

// Auto Date Setting
function initAutoDate() {
    try {
        var disp = document.getElementById('tgl_display');
        var val = document.getElementById('tgl_value');
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth()+1).padStart(2,'0');
        var dd = String(now.getDate()).padStart(2,'0');
        
        // Hidden value (for backend)
        if(val) val.value = `${yyyy}-${mm}-${dd}`;
        
        // Display value in Indonesian format
        var bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        if(disp) disp.value = `${dd} ${bulan[now.getMonth()]} ${yyyy}`;
    } catch(e) {
        console.error('Error setting auto date:', e);
    }
}

// Register Auto-fill Functionality
function initRegisterAutoFill() {
    // Auto-fill nama dan no identitas saat pilih register
    const registerSelect = document.getElementById('id_reg_select');
    const namaInput = document.getElementById('nama_nasabah');
    const noIdentitasInput = document.getElementById('no_identitas_nasabah');
    
    if (registerSelect && namaInput && noIdentitasInput) {
        registerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const text = selectedOption.text;
                const match = text.match(/(.+) - (.+)/);
                if (match) {
                    // Ambil data dari register yang dipilih
                    fetch(`/api/register/${selectedOption.value}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                namaInput.value = data.register.nama;
                                noIdentitasInput.value = data.register.no_identitas;
                                // Update icon setelah field diisi otomatis
                                updateFieldIcon(namaInput);
                                updateFieldIcon(noIdentitasInput);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching register data:', error);
                        });
                }
            } else {
                namaInput.value = '';
                noIdentitasInput.value = '';
            }
        });
    }

    // Autofill nama + no_identitas dari Register (fallback method)
    function fetchRegister(regId) {
        if (!regId) { 
            setFields('', ''); 
            return; 
        }
        
        fetch(`/api/register/${regId}`)
            .then(r => r.json())
            .then(d => { 
                if (d && d.success) { 
                    setFields(d.register.nama || '', d.register.no_identitas || ''); 
                } 
            })
            .catch(() => {});
    }
    
    function setFields(nama, nik) {
        var n = document.getElementById('nama_nasabah');
        var i = document.getElementById('no_identitas_nasabah');
        if (n) n.value = (nama || '').toUpperCase();
        if (i) {
            // Untuk No Identitas, hanya angka dan tidak uppercase
            i.value = (nik || '').replace(/[^0-9]/g, '');
        }
        
        // Update icon setelah field diisi otomatis
        if (n) updateFieldIcon(n);
        if (i) updateFieldIcon(i);
    }
    
    var select = document.getElementById('id_reg_select');
    if (select) {
        select.addEventListener('change', function() { 
            fetchRegister(this.value); 
        });
        if (select.value) { 
            fetchRegister(select.value); 
        }
    } else {
        var preset = document.getElementById('id_reg_hidden')?.value || document.querySelector('input[name="register_id"]')?.value;
        if (preset) { 
            fetchRegister(preset);
            // Update icon untuk field register yang sudah terisi otomatis
            setTimeout(function() {
                const labels = document.querySelectorAll('#modalTambahSlik .form-label');
                labels.forEach(label => {
                    if (label.textContent.includes('Pilih Register')) {
                        const inputGroup = label.nextElementSibling;
                        if (inputGroup && inputGroup.classList.contains('input-group')) {
                            const registerInput = inputGroup.querySelector('input[readonly]');
                            if (registerInput && registerInput.value && updateFieldIcon) {
                                updateFieldIcon(registerInput);
                            }
                        }
                    }
                });
            }, 300);
        }
    }
}

// Field Validation
function initFieldValidation() {
    // Fungsi untuk validasi No Identitas
    function validateNoIdentitas(value) {
        // Hanya angka 0-9, minimal 14 digit, maksimal 16 digit
        const numericRegex = /^[0-9]+$/;
        return numericRegex.test(value) && value.length >= 14 && value.length <= 16;
    }

    // Validasi khusus untuk No Identitas - hanya angka
    const noIdentitasInput = document.querySelector('#modalTambahSlik input[name="no_identitas"]');
    if (noIdentitasInput) {
        noIdentitasInput.addEventListener('input', function(e) {
            // Hanya izinkan angka 0-9
            this.value = this.value.replace(/[^0-9]/g, '');
            // Validasi real-time
            updateFieldIcon(this);
        });
        
        // Validasi saat blur (kehilangan fokus)
        noIdentitasInput.addEventListener('blur', function() {
            updateFieldIcon(this);
        });
    }

    // Validasi submit form agar No Identitas minimal 14 digit
    const form = document.querySelector('#modalTambahSlik form');
    if (form && noIdentitasInput) {
        form.addEventListener('submit', function(e) {
            if (!validateNoIdentitas(noIdentitasInput.value)) {
                e.preventDefault();
                updateFieldIcon(noIdentitasInput);
                noIdentitasInput.focus();
            }
        });
    }

    // Auto-uppercase untuk field dengan class 'upper' (kecuali no_identitas)
    document.querySelectorAll('#modalTambahSlik .upper').forEach(function(el) {
        if (el.id !== 'no_identitas_nasabah') {
            el.addEventListener('input', function() { 
                this.value = this.value.toUpperCase(); 
            });
        }
    });
    
    // Update icon untuk field yang sudah terisi otomatis saat halaman dimuat
    setTimeout(function() {
        const labels = document.querySelectorAll('#modalTambahSlik .form-label');
        labels.forEach(label => {
            const inputGroup = label.nextElementSibling;
            if (inputGroup && inputGroup.classList.contains('input-group')) {
                const input = inputGroup.querySelector('input[readonly], select');
                if (input && input.value && updateFieldIcon) {
                    updateFieldIcon(input);
                }
            }
        });
    }, 200);
}

// Fungsi untuk mengupdate icon berdasarkan nilai field (global scope)
function updateFieldIcon(field) {
    const warningIcon = field.nextElementSibling;
    if (!warningIcon) return;
    
    let isValid = false;
    
    // Validasi khusus untuk No Identitas
    if (field.id === 'no_identitas_nasabah') {
        const numericRegex = /^[0-9]+$/;
        isValid = numericRegex.test(field.value.trim()) && field.value.length >= 14 && field.value.length <= 16;
    } else if (field.id === 'nama_nasabah') {
        isValid = field.value.trim().length > 0;
    } else {
        // Validasi umum untuk field lainnya
        isValid = field.value.trim() !== '';
    }
    
    const icon = warningIcon.querySelector('i');
    if (icon) {
        if (isValid) {
            icon.className = 'bi bi-check-circle-fill';
            warningIcon.classList.remove('text-danger');
            warningIcon.classList.add('text-success');
        } else {
            icon.className = 'bi bi-exclamation-circle';
            warningIcon.classList.remove('text-success');
            warningIcon.classList.add('text-danger');
        }
    }
}

// Make updateFieldIcon globally accessible
window.updateFieldIcon = updateFieldIcon;
