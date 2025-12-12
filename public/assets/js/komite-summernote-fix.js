// Komite Summernote Fix - Simple and reliable initialization
(function() {
    'use strict';
    
    console.log('Komite Summernote Fix loaded');
    
    // Wait for dependencies
    function waitForDependencies(callback) {
        if (typeof $ !== 'undefined' && $.fn.summernote) {
            console.log('Dependencies ready, initializing...');
            callback();
        } else {
            console.log('Waiting for dependencies...');
            setTimeout(() => waitForDependencies(callback), 100);
        }
    }
    
    // Initialize Summernote for modal
    function initModalSummernote() {
        const modal = document.getElementById('modalTambahKomite');
        if (!modal) return;
        
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal shown, initializing Summernote...');
            
            // Wait a bit for modal to fully render
            setTimeout(() => {
                const textarea = document.getElementById('keterangan');
                if (!textarea) {
                    console.log('Textarea not found');
                    return;
                }
                
                // Destroy existing editor if any
                if ($('#keterangan').data('summernote')) {
                    $('#keterangan').summernote('destroy');
                    console.log('Destroyed existing Summernote');
                }
                
                // Initialize Summernote
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
                                console.log('✅ Summernote initialized successfully in modal');
                            },
                            onChange: function(contents) {
                                // Update validation icon
                                const isEmpty = !contents || contents.trim() === '' || contents === '<p><br></p>';
                                const iconSpan = document.querySelector('#keterangan')
                                    ?.closest('.input-group')
                                    ?.querySelector('.required-warning i');
                                if (iconSpan) {
                                    if (!isEmpty) {
                                        iconSpan.className = 'bi bi-check-circle-fill';
                                        iconSpan.parentElement.classList.remove('text-danger');
                                        iconSpan.parentElement.classList.add('text-success');
                                    } else {
                                        iconSpan.className = 'bi bi-exclamation-circle';
                                        iconSpan.parentElement.classList.remove('text-success');
                                        iconSpan.parentElement.classList.add('text-danger');
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing Summernote:', error);
                }
            }, 300);
        });
    }
    
    // Initialize Summernote for edit page
    function initEditSummernote() {
        const textarea = document.getElementById('keterangan');
        if (!textarea) return;
        
        // Check if we're on edit page (has existing content)
        const hasContent = textarea.value && textarea.value.trim() !== '';
        
        console.log('Initializing edit Summernote, has content:', hasContent);
        
        // Destroy existing editor if any
        if ($('#keterangan').data('summernote')) {
            $('#keterangan').summernote('destroy');
        }
        
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
                        console.log('✅ Summernote initialized successfully on edit page');
                    },
                    onChange: function(contents) {
                        // Update validation icon
                        const isEmpty = !contents || contents.trim() === '' || contents === '<p><br></p>';
                        const iconSpan = document.querySelector('#keterangan')
                            ?.closest('.input-group')
                            ?.querySelector('.required-warning i');
                        if (iconSpan) {
                            if (!isEmpty) {
                                iconSpan.className = 'bi bi-check-circle-fill';
                                iconSpan.parentElement.classList.remove('text-danger');
                                iconSpan.parentElement.classList.add('text-success');
                            } else {
                                iconSpan.className = 'bi bi-exclamation-circle';
                                iconSpan.parentElement.classList.remove('text-success');
                                iconSpan.parentElement.classList.add('text-danger');
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing edit Summernote:', error);
        }
    }
    
    // Main initialization
    function init() {
        waitForDependencies(() => {
            // Initialize modal Summernote
            initModalSummernote();
            
            // Initialize edit Summernote if on edit page
            setTimeout(() => {
                const textarea = document.getElementById('keterangan');
                if (textarea && !document.getElementById('modalTambahKomite')) {
                    initEditSummernote();
                }
            }, 500);
        });
    }
    
    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Global function for manual initialization
    window.initKomiteSummernote = function() {
        console.log('Manual Summernote initialization...');
        waitForDependencies(() => {
            const modal = document.getElementById('modalTambahKomite');
            if (modal) {
                initModalSummernote();
            }
            initEditSummernote();
        });
    };
    
})();
