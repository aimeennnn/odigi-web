// SLIK Index JavaScript - Semua fungsi untuk halaman index slik

document.addEventListener('DOMContentLoaded', function() {
    // File preview functionality
    initFilePreview();
    
    // Bulk delete functionality
    initBulkDelete();
    
    // Auto caps search functionality
    initAutoSearch();
    
    // Pagination functionality
    initPagination();
    
    // Upload modal functionality
    initUploadModal();
});

// File Preview Functionality
function initFilePreview() {
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a.slik-file-link');
        if (!a) return;
        
        const type = a.getAttribute('data-type');
        const url = a.getAttribute('data-url');
        
        if (type === 'image') {
            e.preventDefault();
            const img = document.getElementById('slikPreviewImage');
            if (img) { 
                img.src = url; 
            }
            const modal = new bootstrap.Modal(document.getElementById('slikImagePreviewModal'));
            modal.show();
        } else if (type === 'pdf') {
            e.preventDefault();
            // Open PDF in new tab with encrypted URL
            window.open(url, '_blank');
        }
        // For other file types, let the default href behavior work
    });
}

// Bulk Delete Functionality
function initBulkDelete() {
    function initHapusToggle() {
        // Cari tombol hapus di header (yang membuka modal bila ada)
        var btnHapus = document.getElementById('btnBulkSlik');
        if (!btnHapus) return;
        
        var iconHtml = '<i class="bi bi-trash me-1"></i>';
        var label = 'Hapus';
        
        function countChecked() {
            var boxes = document.querySelectorAll('tbody input[type="checkbox"]');
            var n = 0; 
            boxes.forEach(function(b) { 
                if (b.checked) n++; 
            });
            btnHapus.disabled = (n === 0);
            btnHapus.innerHTML = iconHtml + label + (n > 0 ? ' (' + n + ')' : '');
        }
        
        function getSelectedIds() {
            var ids = [];
            document.querySelectorAll('tbody input[type="checkbox"]').forEach(function(b) {
                if (b.checked) {
                    // Ambil nilai id dari data-id di baris atau dari atribut value bila ada
                    var tr = b.closest('tr');
                    var id = b.value || (tr && tr.querySelector('form[action*="/slik/"]') ? (tr.querySelector('form[action*="/slik/"]').getAttribute('action') || '').split('/').pop() : '');
                    if (id) { 
                        ids.push(id); 
                    }
                }
            });
            return ids;
        }
        
        // Delegasi event agar tetap berfungsi walau tabel dirender ulang
        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches('tbody input[type="checkbox"]')) { 
                countChecked(); 
            }
        });
        
        // Inisialisasi awal
        countChecked();

        // Submit bulk delete saat tombol diklik
        btnHapus.addEventListener('click', async function(e) {
            // Selalu cegah navigasi default (menghindari GET ke /slik/bulk-destroy)
            if (e) e.preventDefault();
            if (btnHapus.disabled) { 
                return; 
            }
            
            var ids = getSelectedIds();
            if (!ids.length) { 
                return; 
            }
            
            if (!confirm('Hapus ' + ids.length + ' data SLIK terpilih?')) { 
                return; 
            }
            
            // Fallback aman: gunakan DELETE ke /slik/{id} satu per satu agar tidak tergantung bulk route
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const reg = document.querySelector('input[name="register_id"]')?.value || '';
                
                for (const id of ids) {
                    const body = new URLSearchParams();
                    body.append('_method', 'DELETE');
                    if (reg) body.append('register_id', reg);
                    
                    await fetch(`/slik/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: body.toString()
                    });
                }
                
                // selesai, reload untuk merefleksikan perubahan
                window.location.href = window.location.href;
            } catch (err) {
                console.error('Bulk delete error:', err);
                
                // Jika fetch gagal, coba submit form bulk (jika tersedia)
                var form = document.getElementById('slikBulkDeleteForm');
                var input = document.getElementById('slikBulkIdsHidden');
                if (form && input) { 
                    input.value = ids.join(','); 
                    form.submit(); 
                }
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHapusToggle);
    } else { 
        initHapusToggle(); 
    }
}

// Auto Caps Search Functionality
function initAutoSearch() {
    // Auto caps for filter_nama
    const filterNama = document.getElementById('filter_nama');
    if (filterNama) {
        filterNama.addEventListener('input', function() {
            const currentCursor = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(currentCursor, currentCursor);
        });
        
        filterNama.addEventListener('keypress', function(e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });
    }
    
    // Auto caps for filter_no_identitas
    const filterNoIdentitas = document.getElementById('filter_no_identitas');
    if (filterNoIdentitas) {
        filterNoIdentitas.addEventListener('input', function() {
            const currentCursor = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(currentCursor, currentCursor);
        });
        
        filterNoIdentitas.addEventListener('keypress', function(e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });
    }
    
    // Real-time search for searchSlik
    const searchSlik = document.getElementById('searchSlik');
    const tableBody = document.querySelector('table tbody');
    
    if (searchSlik && tableBody) {
        const allRows = Array.from(tableBody.querySelectorAll('tr'));
        let searchTimeout;
        
        // Auto uppercase and real-time search
        searchSlik.addEventListener('input', function() {
            // Convert to uppercase
            const currentCursor = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(currentCursor, currentCursor);
            
            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase().trim();
            
            // Show loading state
            this.style.background = '#f8f9fa';
            
            searchTimeout = setTimeout(() => {
                // Remove loading state
                this.style.background = '';
                
                // Filter rows based on search term
                filterTableRows(searchTerm, allRows, tableBody);
                
                // Update results counter
                updateResultsCounter(allRows);
            }, 200); // 200ms delay for better UX
        });
        
        // Handle keypress to ensure uppercase
        searchSlik.addEventListener('keypress', function(e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });
        
        // Clear search functionality
        searchSlik.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterTableRows('', allRows, tableBody);
                updateResultsCounter(allRows);
            }
        });
    }
}

function filterTableRows(searchTerm, allRows, tableBody) {
    let visibleCount = 0;
    
    allRows.forEach((row, index) => {
        // Skip empty row or header row
        if (row.querySelector('td[colspan]') || row.querySelector('th')) {
            return;
        }
        
        const rowData = row.textContent.toLowerCase();
        const shouldShow = searchTerm === '' || rowData.includes(searchTerm);
        
        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
            
            // Add highlight effect for matching text
            if (searchTerm !== '') {
                row.style.backgroundColor = '#f8f9fa';
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 300);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show "no results" message if no rows match
    showNoResultsMessage(visibleCount === 0 && searchTerm !== '', tableBody);
}

function updateResultsCounter(allRows) {
    const visibleRows = allRows.filter(row => 
        !row.querySelector('td[colspan]') && 
        !row.querySelector('th') && 
        row.style.display !== 'none'
    );
    
    // You can add a counter display here if needed
    console.log(`Showing ${visibleRows.length} of ${allRows.length} entries`);
}

function showNoResultsMessage(show, tableBody) {
    let noResultsRow = document.getElementById('no-results-row');
    
    if (show && !noResultsRow) {
        noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="100%" class="text-center py-4" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                <i class="bi bi-search me-2"></i>
                <strong>Tidak ada data yang ditemukan</strong>
                <br>
                <small class="text-muted">Coba gunakan kata kunci lain</small>
            </td>
        `;
        tableBody.appendChild(noResultsRow);
    } else if (!show && noResultsRow) {
        noResultsRow.remove();
    }
}

// Pagination function
function initPagination() {
    window.changePerPage = function(perPage) {
        console.log('changePerPage called with:', perPage);
        const url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Reset to first page
        console.log('Redirecting to:', url.toString());
        window.location.href = url.toString();
    }
}

// Upload Modal Functionality
function initUploadModal() {
    // Set form action saat tombol upload diklik
    document.querySelectorAll('.btn-upload-hasil').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var form = document.getElementById('formUploadHasil');
            if (form) {
                form.action = '/slik/upload/' + id;
                form.reset();
                
                // Debug: Log form action
                console.log('Form action set to:', form.action);
                console.log('SLIK ID:', id);
            }
        });
    });
    
    // Debug dan validasi untuk icon detail
    const detailLinks = document.querySelectorAll('.view-icon');
    console.log('Found detail links:', detailLinks.length);
    
    detailLinks.forEach((link, index) => {
        console.log(`Detail link ${index + 1}:`, {
            href: link.href,
            title: link.title,
            element: link
        });
        
        // Tambahkan event listener untuk debugging
        link.addEventListener('click', function(e) {
            console.log('Detail link clicked:', {
                href: this.href,
                target: this.target,
                event: e
            });
            
            // Pastikan link berfungsi
            if (!this.href || this.href === '#' || this.href === window.location.href) {
                e.preventDefault();
                console.error('Invalid detail link:', this.href);
                alert('Link detail tidak valid. Silakan coba lagi.');
                return false;
            }
            
            // Log bahwa link akan dijalankan
            console.log('Proceeding to detail page:', this.href);
        });
    });
    
    // Validasi file saat dipilih
    const fileInput = document.querySelector('#formUploadHasil input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            var file = this.files[0];
            var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            var maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file) {
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                    this.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    this.value = '';
                    return;
                }
            }
        });
    }
    
    // Form submission
    const uploadForm = document.getElementById('formUploadHasil');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            // Validasi file sudah di-handle di slik-modal.js
            // Tidak perlu validasi lagi di sini
            
            // Show loading indicator
            var submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Uploading...';
                submitBtn.disabled = true;
            }
        });
    }
}

// Fungsi untuk validasi form upload
function validateUploadForm() {
    const fileInput = document.querySelector('#formUploadHasil input[name="hasil"]');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Pilih file terlebih dahulu!');
        return false;
    }
    
    // Validasi ukuran file (5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('Ukuran file maksimal 5MB!');
        return false;
    }
    
    // Validasi tipe file
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak didukung! Gunakan PDF, JPG, JPEG, atau PNG.');
        return false;
    }
    
    return true;
}
