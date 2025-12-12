// Data Index Page JavaScript
(function() {
    'use strict';

    // File Preview Functionality
    function initFilePreview() {
        document.addEventListener('click', function(e){
            const a = e.target.closest('a.data-file-link');
            if(!a) return;
            const type = a.getAttribute('data-type');
            const url = a.getAttribute('data-url');
            if(type === 'image'){
                e.preventDefault();
                const img = document.getElementById('previewImage');
                if(img){ img.src = url; }
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            } else if(type === 'pdf'){
                e.preventDefault();
                // Open PDF in new tab with encrypted URL
                window.open(url, '_blank');
            }
            // For other file types, let the default href behavior work
        });
    }

    // Bulk Delete Functionality
    function initBulkDelete() {
        const btnHapus = document.getElementById('btnBulkData');
        if(!btnHapus) return;
        
        const icon = '<i class="bi bi-trash me-1"></i>';
        const label = 'Hapus';
        
        function toggleButton(){
            const boxes = document.querySelectorAll('tbody input[type="checkbox"]');
            const anyChecked = Array.from(boxes).some(cb => cb.checked);
            const n = Array.from(boxes).filter(cb=>cb.checked).length;
            btnHapus.disabled = !anyChecked;
            btnHapus.innerHTML = icon+label+(n>0?' ('+n+')':'');
        }
        
        document.addEventListener('change', function(e){ 
            if(e.target && e.target.matches('tbody input[type="checkbox"]')) {
                toggleButton(); 
            }
        });
        
        toggleButton();
        
        btnHapus.addEventListener('click', async function(e){
            if(e) e.preventDefault(); 
            if(btnHapus.disabled) return;
            
            const ids = Array.from(document.querySelectorAll('tbody input[type="checkbox"]:checked'))
                .map(cb=>cb.value).filter(Boolean);
            if(!ids.length) return; 
            if(!confirm('Hapus '+ids.length+' data terpilih?')) return;
            
            try{
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const reg = document.querySelector('input[name="register_id"]')?.value||'';
                
                for(const id of ids){ 
                    const body = new URLSearchParams(); 
                    body.append('_method','DELETE'); 
                    if(reg) body.append('register_id', reg); 
                    
                    await fetch(`/data/${id}`, { 
                        method:'POST', 
                        headers:{
                            'X-CSRF-TOKEN':token||'',
                            'X-Requested-With':'XMLHttpRequest',
                            'Accept':'text/html,application/json',
                            'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
                        }, 
                        body: body.toString() 
                    }); 
                }
                
                window.location.href = window.location.href;
            } catch(err){ 
                console.error('Data bulk delete failed', err); 
            }
        });
    }

    // Real-time Search Functionality
    function initSearch() {
        const searchData = document.getElementById('searchData');
        const tableBody = document.querySelector('table tbody');
        const allRows = Array.from(tableBody.querySelectorAll('tr'));
        
        if (!searchData) return;
        
        let searchTimeout;
        
        // Auto uppercase and real-time search
        searchData.addEventListener('input', function() {
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
                filterTableRows(searchTerm);
                
                // Update results counter
                updateResultsCounter();
            }, 200); // 200ms delay for better UX
        });
        
        // Handle keypress to ensure uppercase
        searchData.addEventListener('keypress', function(e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });
        
        // Clear search functionality
        searchData.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterTableRows('');
                updateResultsCounter();
            }
        });
        
        function filterTableRows(searchTerm) {
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
            showNoResultsMessage(visibleCount === 0 && searchTerm !== '');
        }
        
        function updateResultsCounter() {
            const visibleRows = allRows.filter(row => 
                !row.querySelector('td[colspan]') && 
                !row.querySelector('th') && 
                row.style.display !== 'none'
            );
            
            // You can add a counter display here if needed
            console.log(`Showing ${visibleRows.length} of ${allRows.length} entries`);
        }
        
        function showNoResultsMessage(show) {
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
    }

    // Pagination function
    window.changePerPage = function(perPage) {
        console.log('changePerPage called with:', perPage);
        const url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Reset to first page
        console.log('Redirecting to:', url.toString());
        window.location.href = url.toString();
    }

    // Auto hide success notification
    function initNotificationAutoHide() {
        const notification = document.querySelector('.alert.position-fixed');
        if (notification) {
            setTimeout(function() {
                if (notification && notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }
    }

    // Initialize all functions
    function init() {
        initFilePreview();
        initBulkDelete();
        initSearch();
        initNotificationAutoHide();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
