// Simple Summernote Initializer
(function() {
    'use strict';
    
    console.log('Summernote Initializer loaded');
    
    // Function to initialize Summernote
    function initializeSummernote() {
        const textarea = document.getElementById('keterangan');
        if (!textarea) {
            console.log('Textarea not found');
            return false;
        }
        
        // Check if already initialized
        if (typeof $ !== 'undefined' && $('#keterangan').data('summernote')) {
            console.log('Summernote already initialized');
            return true;
        }
        
        // Check dependencies
        if (typeof $ === 'undefined' || !$.fn.summernote) {
            console.log('Dependencies not ready, retrying...');
            setTimeout(initializeSummernote, 200);
            return false;
        }
        
        console.log('Initializing Summernote...');
        
        try {
            $('#keterangan').summernote({
                height: 300,
                minHeight: 200,
                maxHeight: 500,
                width: '100%',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder: 'Masukkan keterangan lengkap mengenai keputusan komite...',
                dialogsInBody: true,
                disableDragAndDrop: false,
                disableResizeEditor: false,
                focus: false,
                callbacks: {
                    onInit: function() {
                        console.log('✅ Summernote initialized successfully!');
                        
                        // Make sure it's visible
                        const editor = document.querySelector('.note-editor');
                        if (editor) {
                            editor.style.display = 'block';
                            editor.style.visibility = 'visible';
                            editor.style.opacity = '1';
                        }
                    }
                }
            });
            
            return true;
        } catch (error) {
            console.error('Error initializing Summernote:', error);
            return false;
        }
    }
    
    // Initialize when modal is shown
    const modal = document.getElementById('modalTambahKomite');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal shown, initializing Summernote...');
            setTimeout(initializeSummernote, 300);
        });
    }
    
    // Also try to initialize immediately (for non-modal pages)
    setTimeout(initializeSummernote, 500);
    
    // Global function for manual initialization
    window.initSummernoteNow = function() {
        console.log('Manual Summernote initialization...');
        return initializeSummernote();
    };
    
})();
