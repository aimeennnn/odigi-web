// Summernote initialization for Komite edit page
(function() {
    let keteranganEditor = null;

    // Show loading state immediately
    function showLoadingState() {
        const textarea = document.getElementById('keterangan');
        const container = document.querySelector('.summernote-container');
        if (textarea && container) {
            textarea.style.visibility = 'hidden';
            textarea.style.display = 'none';
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'summernote-loading';
            loadingDiv.id = 'keterangan-loading';
            container.appendChild(loadingDiv);
        }
    }

    // Hide loading state and show editor
    function hideLoadingState() {
        const loadingDiv = document.getElementById('keterangan-loading');
        const textarea = document.getElementById('keterangan');
        if (loadingDiv) {
            loadingDiv.remove();
        }
        if (textarea) {
            textarea.style.display = 'block';
        }
    }

    // Wait for jQuery and Summernote to be loaded
    function initSummernote() {
        if (typeof $ !== 'undefined' && $.fn.summernote) {
            hideLoadingState();
            
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
                        console.log('Summernote initialized successfully');
                        
                        // Show the editor with smooth transition
                        const editor = document.querySelector('.note-editor');
                        if (editor) {
                            editor.classList.add('summernote-loaded');
                        }
                        
                        // Ensure toolbar is clickable and fix dropdowns
                        setTimeout(() => {
                            const toolbar = document.querySelector('.note-toolbar');
                            if (toolbar) {
                                toolbar.style.pointerEvents = 'auto';
                                toolbar.style.zIndex = '1000';
                                
                                // Fix all buttons
                                const buttons = toolbar.querySelectorAll('.btn');
                                buttons.forEach(btn => {
                                    btn.style.pointerEvents = 'auto';
                                    btn.style.cursor = 'pointer';
                                    btn.style.zIndex = '1001';
                                });
                                
                                // Close all dropdowns initially
                                const dropdowns = toolbar.querySelectorAll('.dropdown-menu, .note-color-palette, .note-table, .note-popover');
                                dropdowns.forEach(dropdown => {
                                    dropdown.style.display = 'none';
                                    dropdown.style.visibility = 'hidden';
                                    dropdown.style.opacity = '0';
                                });
                            }
                            
                            // Setup dropdown handlers
                            if (typeof window.setupSummernoteDropdownHandlers === 'function') {
                                window.setupSummernoteDropdownHandlers();
                            }
                        }, 100);
                    },
                    onImageUpload: function(files) {
                        // Upload image using existing upload endpoint
                        const formData = new FormData();
                        formData.append('file', files[0]);
                        
                        fetch('/upload-image', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.location) {
                                $(this).summernote('insertImage', result.location);
                            } else {
                                console.error('Upload failed');
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                        });
                    },
                    onChange: function(contents, $editable) {
                        // Update textarea and validation on content change
                        const keteranganTextarea = document.querySelector("#keterangan");
                        const proxy = document.getElementById("keterangan_required_proxy");
                        const errorText = document.getElementById("keterangan_error");
                        if (keteranganTextarea) {
                            keteranganTextarea.value = contents;
                            const isEmpty = !contents || contents.replace(/<[^>]*>/g, "").trim() === "";
                            if (isEmpty) {
                                keteranganTextarea.setCustomValidity("Keterangan wajib diisi.");
                                if (proxy) proxy.value = "";
                                if (errorText) errorText.style.display = "block";
                            } else {
                                keteranganTextarea.setCustomValidity("");
                                if (proxy) proxy.value = "ok";
                                if (errorText) errorText.style.display = "none";
                            }
                            // Update validation icon
                            const iconSpan = keteranganTextarea
                                .closest(".input-group")
                                ?.querySelector(".required-warning i");
                            if (iconSpan) {
                                if (!isEmpty) {
                                    iconSpan.className = "bi bi-check-circle-fill";
                                    iconSpan.parentElement.classList.remove("text-danger");
                                    iconSpan.parentElement.classList.add("text-success");
                                } else {
                                    iconSpan.className = "bi bi-exclamation-circle";
                                    iconSpan.parentElement.classList.remove("text-success");
                                    iconSpan.parentElement.classList.add("text-danger");
                                }
                            }
                        }
                    }
                }
            });
            
            keteranganEditor = $('#keterangan').data('summernote');
        } else {
            // Retry after a short delay if jQuery/Summernote not ready
            setTimeout(initSummernote, 100);
        }
    }
    
    // Show loading state immediately when DOM is ready
    function startInitialization() {
        showLoadingState();
        // Start initialization with delay to ensure DOM is ready
        setTimeout(initSummernote, 200);
    }
    
    // Check if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInitialization);
    } else {
        // DOM is already loaded
        startInitialization();
    }
})();

// Inisialisasi validasi untuk semua field yang sudah ada nilainya
function initializeValidation() {
    // Delay sedikit untuk memastikan DOM sudah selesai di-render
    setTimeout(() => {
        // Validasi untuk field keterangan
        const keteranganTextarea = document.querySelector("#keterangan");
        if (keteranganTextarea && keteranganTextarea.value.trim() !== "") {
            // Field sudah ada nilai, update icon menjadi success
            const iconSpan = keteranganTextarea
                .closest(".input-group")
                ?.querySelector(".required-warning i");
            if (iconSpan) {
                iconSpan.className = "bi bi-check-circle-fill";
                iconSpan.parentElement.classList.remove("text-danger");
                iconSpan.parentElement.classList.add("text-success");
            }
        }
    }, 500);
}

// Jalankan inisialisasi validasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', initializeValidation);
