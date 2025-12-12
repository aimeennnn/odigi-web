// Bank Index Page JavaScript
(function() {
    'use strict';

    // File Preview Functionality
    function initFilePreview() {
        document.addEventListener('click', function(e){
            const a = e.target.closest('a.bank-file-link');
            if(!a) return;
            const type = a.getAttribute('data-type');
            const url = a.getAttribute('data-url');
            if(type === 'image'){
                e.preventDefault();
                const img = document.getElementById('bankPreviewImage');
                if(img){ img.src = url; }
                const modal = new bootstrap.Modal(document.getElementById('bankImagePreviewModal'));
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
        var btn = document.getElementById('btnBulkBank');
        if(!btn) return;
        
        var icon = '<i class="bi bi-trash me-1"></i>';
        var label = 'Hapus';
        
        function count(){ 
            var n=0; 
            document.querySelectorAll('tbody input[type="checkbox"]').forEach(function(b){ 
                if(b.checked) n++; 
            }); 
            btn.disabled=(n===0); 
            btn.innerHTML=icon+label+(n>0?' ('+n+')':''); 
        }
        
        document.addEventListener('change', function(e){ 
            if(e.target && e.target.matches('tbody input[type="checkbox"]')) count(); 
        });
        
        count();
        
        btn.addEventListener('click', async function(e){ 
            if(e) e.preventDefault(); 
            if(btn.disabled) return; 
            
            var ids=[]; 
            document.querySelectorAll('tbody input[type="checkbox"]:checked').forEach(function(b){ 
                if(b.value) ids.push(b.value); 
            }); 
            
            if(!ids.length) return; 
            if(!confirm('Hapus '+ids.length+' data Bank terpilih?')) return; 
            
            try{ 
                const token=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); 
                const reg=document.querySelector('input[name="register_id"]')?.value||''; 
                
                for(const id of ids){ 
                    const body=new URLSearchParams(); 
                    body.append('_method','DELETE'); 
                    if(reg) body.append('register_id', reg); 
                    
                    await fetch(`/bank/${id}`, { 
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
                
                window.location.href=window.location.href; 
            } catch(err){ 
                console.error('Bank bulk delete failed', err); 
            } 
        });
    }

    // Real-time Search Functionality
    function initSearch() {
        const searchInput = document.getElementById('searchBank');
        const tableBody = document.querySelector('tbody');
        let searchTimeout;

        if (!searchInput || !tableBody) return;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });

        // Auto uppercase input
        searchInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                performSearch();
            }
        });

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(function(row) {
                // Skip header rows or empty rows
                if (row.querySelector('th') || row.querySelector('td[colspan]')) {
                    return;
                }

                const rowText = row.textContent.toLowerCase();
                const shouldShow = searchTerm === '' || rowText.includes(searchTerm);

                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                    
                    // Add highlight effect
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

            // Show no results message if needed
            showNoResultsMessage(visibleCount === 0 && searchTerm !== '');
        }

        function showNoResultsMessage(show) {
            let noResultsRow = document.getElementById('bank-no-results-row');
            
            if (show && !noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'bank-no-results-row';
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
    window.changePerPageBank = function(perPage) {
        console.log('changePerPageBank called with:', perPage);
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