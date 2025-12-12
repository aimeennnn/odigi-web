// Komite Modal JavaScript with Summernote
(function () {
    "use strict";

    let keteranganEditor;

    // Toggle Detail Sidebar
    window.toggleDetailSidebar = function () {
        const sidebar = document.getElementById("detailSidebar");
        const modalDialog = document.getElementById("modalDialog");

        if (sidebar.classList.contains("show")) {
            sidebar.classList.remove("show");
            modalDialog.classList.remove("sidebar-open");
        } else {
            sidebar.classList.add("show");
            modalDialog.classList.add("sidebar-open");
        }
    };

    // Switch Detail Tab
    window.switchDetailTab = function (tabName) {
        // Remove active class from all tabs
        document.querySelectorAll(".nav-tab").forEach((tab) => {
            tab.classList.remove("active");
        });

        // Hide all tab contents
        document.querySelectorAll(".tab-content").forEach((content) => {
            content.classList.remove("active");
        });

        // Add active class to clicked tab
        document
            .querySelector(`[data-tab="${tabName}"]`)
            .classList.add("active");

        // Show corresponding tab content
        document.getElementById(`tab-${tabName}`).classList.add("active");
    };

    // Toggle Data Detail
    window.toggleDataDetail = function (dataId) {
        const dataCard = document.querySelector(`[data-data-id="${dataId}"]`);
        const detailElement = document.getElementById(`data-detail-${dataId}`);

        if (dataCard && detailElement) {
            if (
                detailElement.style.display === "none" ||
                detailElement.style.display === ""
            ) {
                // Show detail
                detailElement.style.display = "block";
                dataCard.classList.add("expanded");
            } else {
                // Hide detail
                detailElement.style.display = "none";
                dataCard.classList.remove("expanded");
            }
        }
    };

    // Toggle Bank Detail
    window.toggleBankDetail = function (bankId) {
        const bankCard = document.querySelector(`[data-bank-id="${bankId}"]`);
        const detailElement = document.getElementById(`bank-detail-${bankId}`);

        if (bankCard && detailElement) {
            if (
                detailElement.style.display === "none" ||
                detailElement.style.display === ""
            ) {
                // Show detail
                detailElement.style.display = "block";
                bankCard.classList.add("expanded");
            } else {
                // Hide detail
                detailElement.style.display = "none";
                bankCard.classList.remove("expanded");
            }
        }
    };

    // Toggle SLIK Detail
    window.toggleSlikDetail = function (slikId) {
        const slikCard = document.querySelector(`[data-slik-id="${slikId}"]`);
        const detailElement = document.getElementById(`slik-detail-${slikId}`);

        if (slikCard && detailElement) {
            if (
                detailElement.style.display === "none" ||
                detailElement.style.display === ""
            ) {
                // Show detail
                detailElement.style.display = "block";
                slikCard.classList.add("expanded");
            } else {
                // Hide detail
                detailElement.style.display = "none";
                slikCard.classList.remove("expanded");
            }
        }
    };

    // Initialize Summernote
    function initSummernote() {
        const modal = document.getElementById("modalTambahKomite");
        if (!modal) return;

        // Initialize Summernote when modal is shown
        modal.addEventListener("shown.bs.modal", function () {
            // Initialize field validation icons first
            setTimeout(() => {
                if (typeof window.validateAllFieldsKomite === "function") {
                    window.validateAllFieldsKomite();
                }
            }, 100);

            if (!keteranganEditor) {
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
                    popover: {
                        image: [
                            ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                            ['float', ['floatLeft', 'floatRight', 'floatNone']],
                            ['remove', ['removeMedia']]
                        ],
                        link: [
                            ['link', ['linkDialogShow', 'unlink']]
                        ],
                        table: [
                            ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                            ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                        ],
                        air: [
                            ['color', ['color']],
                            ['font', ['bold', 'underline', 'clear']]
                        ]
                    },
                    callbacks: {
                        onInit: function() {
                            console.log('Summernote initialized successfully');
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
                            }, 200);
                        },
                        onImageUpload: function(files) {
                            console.log('Image upload started:', files[0]);
                            
                            // Validate file type
                            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
                            if (!allowedTypes.includes(files[0].type)) {
                                alert('Format file tidak didukung. Gunakan JPEG, PNG, GIF, atau SVG.');
                                return;
                            }
                            
                            // Validate file size (2MB max)
                            if (files[0].size > 2 * 1024 * 1024) {
                                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                                return;
                            }
                            
                            // Upload image using existing upload endpoint
                            const formData = new FormData();
                            formData.append('file', files[0]);
                            
                            // Show loading indicator
                            const editor = $(this);
                            editor.summernote('showProgress');
                            
                            // Get CSRF token
                            const csrfToken = document.querySelector('meta[name="csrf-token"]');
                            if (!csrfToken) {
                                console.error('CSRF token not found');
                                alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
                                return;
                            }
                            
                            console.log('CSRF token:', csrfToken.getAttribute('content'));
                            
                            fetch('/upload-image', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => {
                                console.log('Upload response status:', response.status);
                                return response.json();
                            })
                            .then(result => {
                                console.log('Upload result:', result);
                                editor.summernote('hideProgress');
                                
                                if (result.location) {
                                    editor.summernote('insertImage', result.location);
                                    console.log('Image inserted successfully:', result.location);
                                } else {
                                    console.error('Upload failed - no location returned:', result);
                                    alert('Upload gagal: ' + (result.error || 'Tidak ada URL gambar yang dikembalikan'));
                                }
                            })
                            .catch(error => {
                                console.error('Upload error:', error);
                                editor.summernote('hideProgress');
                                alert('Upload gagal: ' + error.message);
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
            }
        });

        // Close sidebar when modal is closed
        modal.addEventListener("hidden.bs.modal", function () {
            const sidebar = document.getElementById("detailSidebar");
            const modalDialog = document.getElementById("modalDialog");
            sidebar.classList.remove("show");
            modalDialog.classList.remove("sidebar-open");

            // Destroy Summernote instance when modal is closed
            if (keteranganEditor) {
                $('#keterangan').summernote('destroy');
                keteranganEditor = null;
            }
        });
    }

    // Initialize form validation
    function initFormValidation() {
        // Function to update field validation icon
        function updateFieldIcon(field) {
            let iconSpan;
            // All fields now use the same selector
            iconSpan = field
                .closest(".input-group")
                ?.querySelector(".required-warning i");
            if (iconSpan) {
                console.log(
                    `Validating field: ${
                        field.name || field.tagName
                    }, value: "${field.value}"`
                );
                if (field.value && field.value.trim() !== "") {
                    iconSpan.className = "bi bi-check-circle-fill";
                    iconSpan.parentElement.classList.remove("text-danger");
                    iconSpan.parentElement.classList.add("text-success");
                    console.log(
                        `✓ Field ${field.name || field.tagName} set to SUCCESS`
                    );
                } else {
                    iconSpan.className = "bi bi-exclamation-circle";
                    iconSpan.parentElement.classList.remove("text-success");
                    iconSpan.parentElement.classList.add("text-danger");
                    console.log(
                        `⚠ Field ${field.name || field.tagName} set to WARNING`
                    );
                }
            } else {
                console.log(
                    `No icon found for field: ${field.name || field.tagName}`
                );
            }
        }

        // Function to validate all fields in modal
        function validateAllFields() {
            const modal = document.getElementById("modalTambahKomite");
            if (!modal) return;

            const requiredFields = modal.querySelectorAll(
                ".form-control, .form-select, textarea"
            );

            console.log(`Found ${requiredFields.length} fields to validate`);

            requiredFields.forEach(function (field) {
                updateFieldIcon(field);
            });
        }

        // For uppercase (only for fields that are not Summernote)
        var upperFields = document.querySelectorAll(".upper");
        upperFields.forEach(function (field) {
            field.addEventListener("input", function () {
                this.value = this.value.toUpperCase();
            });
        });

        // Form validation with icon updates
        var requiredFields = document.querySelectorAll(
            ".form-control, .form-select, textarea"
        );
        requiredFields.forEach(function (field) {
            // Listen for input events
            field.addEventListener("input", function () {
                updateFieldIcon(this);
            });

            // Listen for change events (for select fields)
            field.addEventListener("change", function () {
                updateFieldIcon(this);
            });

            // Listen for blur events
            field.addEventListener("blur", function () {
                updateFieldIcon(this);
            });

            // Trigger validation immediately for current values
            setTimeout(() => {
                updateFieldIcon(field);
            }, 50);
        });

        // Special handling for register field (auto-filled)
        const registerField = document.querySelector('input[name="id_reg"]');
        if (registerField) {
            // Always show success icon for register field since it's auto-filled
            const registerInputGroup = registerField.closest(".input-group");
            if (registerInputGroup) {
                const registerIcon = registerInputGroup.querySelector(
                    ".required-warning i"
                );
                if (registerIcon) {
                    registerIcon.className = "bi bi-check-circle-fill";
                    registerIcon.parentElement.classList.remove("text-danger");
                    registerIcon.parentElement.classList.add("text-success");
                }
            }
        }

        // Make validateAllFields available globally
        window.validateAllFieldsKomite = validateAllFields;

        // Form submit validation to prevent saving if required fields are empty
        const form = document.querySelector("#komiteForm");
        if (form) {
            form.addEventListener("submit", function (e) {
                e.preventDefault(); // Always prevent default for full control

                let isValid = true;
                let errorMessage = "";

                // Validate regular fields (not Summernote)
                const requiredFields = form.querySelectorAll("[required]");
                requiredFields.forEach(function (field) {
                    if (field.id !== "keterangan") {
                        // Skip keterangan as it will be validated separately
                        if (field.value.trim() === "") {
                            isValid = false;
                            errorMessage =
                                "Mohon lengkapi semua field yang wajib diisi!";
                        }
                    }
                });

                // Validate Summernote keterangan
                if (keteranganEditor) {
                    const editorContent = $('#keterangan').summernote('code');
                    const keteranganField = document.querySelector("#keterangan");
                    const proxy = document.getElementById("keterangan_required_proxy");
                    const errorText = document.getElementById("keterangan_error");
                    if (keteranganField) {
                        keteranganField.value = editorContent;
                        const isEmpty = !editorContent || editorContent.replace(/<[^>]*>/g, "").trim() === "";
                        if (isEmpty) {
                            keteranganField.setCustomValidity("Keterangan wajib diisi.");
                            // Tampilkan tooltip native pada proxy (required input tak terlihat)
                            if (proxy) {
                                try { proxy.reportValidity(); } catch (err) {}
                            } else {
                                try { keteranganField.reportValidity(); } catch (err2) {}
                            }
                            if (errorText) errorText.style.display = "block";
                            // Fokuskan ke area editor agar pengguna langsung mengetik
                            try { $('#keterangan').summernote('focus'); } catch (_) {}
                            return false;
                        } else {
                            keteranganField.setCustomValidity("");
                            if (proxy) proxy.value = "ok";
                            if (errorText) errorText.style.display = "none";
                        }
                    }
                }

                // Jika ada field yang invalid, munculkan bubble peringatan
                if (!form.checkValidity()) {
                    // Pastikan proxy berada di posisi visible dan mendapat fokus sejenak
                    const proxy = document.getElementById("keterangan_required_proxy");
                    if (proxy && proxy.value === "") {
                        try { proxy.focus({ preventScroll: true }); } catch (_) {}
                    }
                    try { form.reportValidity(); } catch (err) {}
                    return false;
                }

                // Submit form manually
                form.submit();
            });
        }
    }

    // Initialize all functions
    function init() {
        initSummernote();
        initFormValidation();

        // Force validation for all fields on page load
        setTimeout(() => {
            if (typeof window.validateAllFieldsKomite === "function") {
                window.validateAllFieldsKomite();
            }
        }, 500);
    }

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    // Additional event listener for modal shown to ensure validation works
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("modalTambahKomite");
        if (modal) {
            modal.addEventListener("shown.bs.modal", function () {
                // Force validation after modal is shown
                setTimeout(() => {
                    if (typeof window.validateAllFieldsKomite === "function") {
                        console.log(
                            "Komite modal shown - running validation..."
                        );
                        window.validateAllFieldsKomite();
                    }
                }, 300);
            });
        }
    });
})();
