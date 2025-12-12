// Data Show Page JavaScript
(function() {
    'use strict';

    // File Preview Functionality for Show Page
    function initFilePreview() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-view-file');
            if (!btn) return;
            
            e.preventDefault();
            const type = btn.getAttribute('data-type');
            const url = btn.getAttribute('data-url');
            
            if (type === 'image') {
                const img = document.getElementById('previewImage');
                if (img) {
                    img.src = url;
                }
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            } else {
                // For PDF and other files, open in new tab
                window.open(url, '_blank');
            }
        });
    }

    // Initialize all functions
    function init() {
        initFilePreview();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
