// Bank Modal JavaScript (Drag & Drop Upload + Validation)
(function () {
    "use strict";

    // Global variables for file upload
    let pending = [];
    let updateFileUploadIcon;

    // No Rekening validation function
    function validateNoRekening(value) {
        // Hanya angka 0-9, minimal 10 digit, maksimal 16 digit
        const numericRegex = /^[0-9]+$/;
        return (
            numericRegex.test(value) && value.length >= 10 && value.length <= 16
        );
    }

    // Auto uppercase functionality
    function initAutoUppercase() {
        document.querySelectorAll(".upper").forEach(function (field) {
            field.addEventListener("input", function () {
                this.value = this.value.toUpperCase();
            });
        });
    }

    // No Rekening validation
    function initNoRekeningValidation() {
        const noRekeningInput = document.querySelector(
            '#modalTambahBank input[name="no_rekening"]'
        );
        if (noRekeningInput) {
            const iconWrapper = document.getElementById("no_rekening_icon");
            const icon = iconWrapper ? iconWrapper.querySelector("i") : null;

            function updateIcon() {
                const isValid = validateNoRekening(
                    noRekeningInput.value.trim()
                );
                if (icon) {
                    if (isValid) {
                        icon.className = "bi bi-check-circle-fill";
                        iconWrapper.classList.remove("text-danger");
                        iconWrapper.classList.add("text-success");
                    } else {
                        icon.className = "bi bi-exclamation-circle";
                        iconWrapper.classList.remove("text-success");
                        iconWrapper.classList.add("text-danger");
                    }
                }
            }

            noRekeningInput.addEventListener("input", function () {
                // Hanya izinkan angka 0-9
                this.value = this.value.replace(/[^0-9]/g, "");
                // Batasi panjang maksimal 16 (HTML sudah ada maxlength, namun jaga-jaga)
                if (this.value.length > 16) {
                    this.value = this.value.slice(0, 16);
                }
                updateIcon();
            });

            // Set ikon awal saat halaman/modal dibuka
            updateIcon();
        }
    }

    // Auto open modal if validation errors (handled by PHP in push scripts)

    // Drag & Drop File Upload
    function initDragDropUpload() {
        const dz = document.getElementById("bankDropZoneInline");
        const choose = document.getElementById("bankChooseFiles");
        const input = document.getElementById("bankFileInputMulti");
        const list = document.getElementById("bankFileList");
        const tempHolder = document.getElementById("bankTempPathsHolder");

        if (!dz || !choose || !input || !list || !tempHolder) return;

        // Utility functions
        function bytes(x) {
            if (!x) return "0 B";
            const k = 1024,
                s = ["B", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(x) / Math.log(k));
            return (x / Math.pow(k, i)).toFixed(2) + " " + s[i];
        }

        function addProgressRow(name, tempPath = null) {
            const row = document.createElement("div");
            row.className =
                "p-2 border rounded bg-white d-flex align-items-center justify-content-between gap-2 mb-2";
            row.innerHTML = `
                <div class="d-flex align-items-center flex-grow-1 gap-2" style="min-width:0;">
                    <i class="bi bi-file-earmark me-1"></i>
                    <span class="file-name text-truncate" style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; min-width:0;">${name}</span>
                    <small class="pct text-primary ms-2">0%</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="progress mt-0" style="height:6px; width:60px;"><div class="progress-bar" style="width:0%"></div></div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-upload" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            `;
            list.appendChild(row);

            // Remove file from pending/temp_paths and DOM
            row.querySelector(".btn-remove-upload").addEventListener(
                "click",
                function () {
                    console.log("Remove button clicked, tempPath:", tempPath);
                    console.log("Pending before removal:", pending);

                    if (tempPath) {
                        pending = pending.filter((p) => p !== tempPath);
                        console.log("Pending after removal:", pending);
                        syncHiddenInputs();
                    } else {
                        // Jika tidak ada tempPath, hapus semua pending
                        pending = [];
                        syncHiddenInputs();
                    }
                    row.remove();

                    // Force update icon after file is removed
                    setTimeout(() => {
                        updateFileUploadIcon();
                    }, 100);

                    console.log(
                        "File removed, final pending files:",
                        pending.length
                    );
                }
            );

            return {
                bar: row.querySelector(".progress-bar"),
                pct: row.querySelector(".pct"),
                row,
                setTempPath: (tp) => {
                    row.dataset.tempPath = tp;
                    tempPath = tp; // Update local tempPath variable
                    console.log("TempPath set for row:", tp);
                },
            };
        }

        // Event listeners
        choose.addEventListener("click", function (e) {
            e.preventDefault();
            input.click();
        });

        // Drag and drop events
        ["dragenter", "dragover", "dragleave", "drop"].forEach((n) =>
            dz.addEventListener(n, (e) => {
                e.preventDefault();
                e.stopPropagation();
            })
        );

        dz.addEventListener("dragover", () =>
            dz.classList.add("border-primary")
        );
        dz.addEventListener("dragleave", () =>
            dz.classList.remove("border-primary")
        );
        dz.addEventListener("drop", (e) => {
            dz.classList.remove("border-primary");
            const files = e.dataTransfer.files;
            if (files && files.length) {
                startTempUpload([files[0]]); // only one file
            }
        });

        input.addEventListener("change", function () {
            if (this.files && this.files.length) {
                startTempUpload([this.files[0]]); // only one file
                this.value = "";
            }
        });

        function startTempUpload(files) {
            // Remove previous file preview if any
            if (list) list.innerHTML = "";
            pending = [];
            console.log(
                "Starting temp upload, pending reset to:",
                pending.length
            );

            const allowed = [
                "application/pdf",
                "image/jpeg",
                "image/jpg",
                "image/png",
            ];
            const arr = Array.from(files || []);
            if (arr.length > 1) arr.length = 1; // ensure only one file

            const form = new FormData();
            for (const f of arr) {
                if (!allowed.includes(f.type)) {
                    alert("Format tidak didukung");
                    return;
                }
                if (f.size > 5 * 1024 * 1024) {
                    alert("Maks 2MB per file");
                    return;
                }
                form.append("files[]", f);
            }

            const rowCtl = addProgressRow(arr[0].name);
            const xhr = new XMLHttpRequest();

            // Use the correct upload route for bank
            const uploadUrl = "/bank/upload-temp-new";
            xhr.open("POST", uploadUrl);

            const token = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
            if (token) xhr.setRequestHeader("X-CSRF-TOKEN", token);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("Accept", "application/json");

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const p = Math.round((e.loaded / e.total) * 100);
                    rowCtl.bar.style.width = p + "%";
                    rowCtl.pct.textContent = p + "%";
                }
            };

            xhr.onload = () => {
                try {
                    const res = JSON.parse(xhr.responseText || "{}");
                    if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                        let newPaths = res.temp_paths || [];
                        if (!Array.isArray(newPaths)) newPaths = [newPaths];
                        pending = pending.concat(newPaths);
                        rowCtl.bar.style.width = "100%";
                        rowCtl.bar.classList.add("bg-success");
                        rowCtl.pct.textContent = "100%";
                        if (newPaths.length) {
                            rowCtl.setTempPath(newPaths[0]);
                            console.log(
                                "File uploaded successfully, tempPath set:",
                                newPaths[0]
                            );
                        }
                        syncHiddenInputs();
                        updateFileUploadIcon(); // Update icon setelah upload berhasil

                        // Pastikan tombol submit enabled setelah upload berhasil
                        const submitBtn =
                            document.getElementById("btnSimpanBank");
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.pointerEvents = "auto";
                            submitBtn.style.opacity = "1";
                            submitBtn.style.cursor = "pointer";
                        }

                        console.log(
                            "Upload completed, pending files:",
                            pending.length
                        );
                    } else {
                        alert(res.message || "Upload gagal");
                    }
                } catch {
                    alert("Upload gagal");
                }
            };

            xhr.onerror = () => alert("Kesalahan jaringan saat upload");
            xhr.send(form);
        }

        function syncHiddenInputs() {
            const parent = tempHolder.parentElement;
            parent
                .querySelectorAll('input[name="temp_paths[]"]')
                .forEach((el, idx) => {
                    if (el !== tempHolder) el.remove();
                });
            if (!pending.length) {
                tempHolder.value = "";
                updateFileUploadIcon(); // Update icon ketika tidak ada file
                console.log("No pending files, tempHolder cleared");
                return;
            }

            tempHolder.value = pending[0] || "";
            for (let i = 1; i < pending.length; i++) {
                const inp = document.createElement("input");
                inp.type = "hidden";
                inp.name = "temp_paths[]";
                inp.value = pending[i];
                parent.appendChild(inp);
            }
            updateFileUploadIcon(); // Update icon setelah sync
            console.log(
                "Hidden inputs synced, tempHolder value:",
                tempHolder.value
            );
            console.log(
                "Total temp_paths inputs:",
                parent.querySelectorAll('input[name="temp_paths[]"]').length
            );
        }

        updateFileUploadIcon = function () {
            const iconSpan = document.querySelector("#bankFileUploadIcon i");
            if (iconSpan) {
                console.log(
                    "Updating bank file upload icon, pending files:",
                    pending.length
                );
                if (pending.length > 0) {
                    iconSpan.className = "bi bi-check-circle-fill";
                    iconSpan.parentElement.classList.remove("text-danger");
                    iconSpan.parentElement.classList.add("text-success");
                    console.log("Icon changed to success (check)");
                } else {
                    iconSpan.className = "bi bi-exclamation-circle";
                    iconSpan.parentElement.classList.remove("text-success");
                    iconSpan.parentElement.classList.add("text-danger");
                    console.log("Icon changed to warning (exclamation)");
                }
            } else {
                console.log("Bank icon element not found");
            }
        };

        // Make functions available globally for the upload process
        window.updateBankFileUploadIcon = updateFileUploadIcon;
    }

    // Initialize form validation
    function initFormValidation() {
        // For changing warning icon → check
        var requiredFields = document.querySelectorAll(
            ".form-control, .form-select, textarea"
        );

        requiredFields.forEach(function (field) {
            field.addEventListener("input", function () {
                let iconSpan = this.closest(".input-group")?.querySelector(
                    ".required-warning i"
                );
                if (iconSpan) {
                    // Khusus no_rekening gunakan aturan 10-16 digit angka
                    const isValid =
                        this.name === "no_rekening"
                            ? validateNoRekening(this.value.trim())
                            : this.value.trim() !== "";

                    if (isValid) {
                        iconSpan.className = "bi bi-check-circle-fill";
                        iconSpan.parentElement.classList.remove("text-danger");
                        iconSpan.parentElement.classList.add("text-success");
                    } else {
                        iconSpan.className = "bi bi-exclamation-circle";
                        iconSpan.parentElement.classList.remove("text-success");
                        iconSpan.parentElement.classList.add("text-danger");
                    }
                }
            });

            // Trigger once on load to auto-check if there are default values
            field.dispatchEvent(new Event("input"));
        });

        // Form submit handler
        const form = document.querySelector("#modalTambahBank form");
        if (form) {
            form.addEventListener("submit", function (e) {
                console.log(
                    "Bank form submit event triggered, pending files:",
                    pending.length
                );

                // Debug: Log all form data
                const formData = new FormData(form);
                console.log("Bank form data being submitted:");
                for (let [key, value] of formData.entries()) {
                    console.log(key + ":", value);
                }

                // Check if temp_paths are set
                const tempInputs = form.querySelectorAll(
                    'input[name="temp_paths[]"]'
                );
                console.log("Bank temp paths inputs found:", tempInputs.length);
                tempInputs.forEach((input, index) => {
                    console.log(`temp_paths[${index}]:`, input.value);
                });
            });
        }

        // Button state management
        const submitBtn = document.getElementById("btnSimpanBank");
        if (submitBtn) {
            // Pastikan tombol selalu enabled dan clickable
            submitBtn.disabled = false;
            submitBtn.style.pointerEvents = "auto";
            submitBtn.style.opacity = "1";
            submitBtn.style.cursor = "pointer";

            // Enable submit button when modal is shown
            const modal = document.getElementById("modalTambahBank");
            if (modal) {
                modal.addEventListener("shown.bs.modal", function () {
                    // Reset pending files dan update icon saat modal dibuka
                    pending = [];
                    const list = document.getElementById("bankFileList");
                    if (list) list.innerHTML = "";

                    // Reset form fields
                    const form = document.querySelector(
                        "#modalTambahBank form"
                    );
                    if (form) {
                        form.reset();

                        // Remove required attribute dari file input untuk mencegah error browser
                        const fileInput =
                            document.getElementById("bankFileInputMulti");
                        if (fileInput) {
                            fileInput.removeAttribute("required");
                        }
                    }

                    // Pastikan tombol submit selalu enabled
                    submitBtn.disabled = false;
                    submitBtn.style.pointerEvents = "auto";
                    submitBtn.style.opacity = "1";
                    submitBtn.style.cursor = "pointer";

                    // Pastikan icon upload file di-reset ke state awal (warning)
                    setTimeout(() => {
                        updateFileUploadIcon();
                        console.log(
                            "Bank modal reset completed, pending files:",
                            pending.length
                        );
                    }, 100);

                    console.log(
                        "Bank modal opened, submit button enabled, files reset"
                    );
                });

                // Juga pastikan tombol enabled saat modal akan ditutup dan dibuka lagi
                modal.addEventListener("hidden.bs.modal", function () {
                    pending = [];
                    console.log("Bank modal closed, pending reset");
                });
            }

            // Tambahan event listener untuk memastikan tombol bisa diklik
            submitBtn.addEventListener("click", function (e) {
                console.log("Bank submit button clicked directly");
                console.log("Button disabled status:", this.disabled);
                console.log("Pending files:", pending.length);

                // Jika tombol disabled, enable dulu
                if (this.disabled) {
                    this.disabled = false;
                    this.style.pointerEvents = "auto";
                    this.style.opacity = "1";
                    console.log("Button was disabled, now enabled");
                }

                // Validasi langsung di click handler
                const form = document.querySelector("#modalTambahBank form");
                const namaBank = form?.querySelector(
                    'select[name="nama_bank"]'
                );
                const noRekening = form?.querySelector(
                    'input[name="no_rekening"]'
                );

                if (!namaBank || namaBank.value.trim() === "") {
                    e.preventDefault();
                    alert("Mohon pilih nama bank!");
                    return false;
                }

                if (
                    !noRekening ||
                    !validateNoRekening(noRekening.value.trim())
                ) {
                    e.preventDefault();
                    alert("Mohon isi nomor rekening dengan 10-16 digit angka!");
                    return false;
                }

                if (pending.length === 0) {
                    e.preventDefault();
                    alert("Mohon upload file terlebih dahulu!");
                    return false;
                }

                console.log("All bank validation passed, submitting form...");

                // Set file input value dari temp_paths untuk form submission
                const fileInput = document.getElementById("bankFileInputMulti");
                const tempPathsInput = document.getElementById(
                    "bankTempPathsHolder"
                );

                if (fileInput && tempPathsInput && tempPathsInput.value) {
                    // Remove required attribute to prevent browser validation error
                    fileInput.removeAttribute("required");
                    console.log("Bank file input prepared for submission");
                }

                // Jangan prevent default, biarkan form submit normal
            });
        }
    }

    // Initialize all functions
    function init() {
        initAutoUppercase();
        initNoRekeningValidation();
        initDragDropUpload();
        initFormValidation();

        // Inisialisasi icon upload file ke state awal
        setTimeout(() => {
            if (typeof updateFileUploadIcon === "function") {
                updateFileUploadIcon();
                console.log("Initial bank file upload icon set");
            }

            // Debug: Check button state
            const submitBtn = document.getElementById("btnSimpanBank");
            if (submitBtn) {
                console.log("Bank submit button found:", {
                    disabled: submitBtn.disabled,
                    pointerEvents: submitBtn.style.pointerEvents,
                    opacity: submitBtn.style.opacity,
                    display: getComputedStyle(submitBtn).display,
                });
            }
        }, 500);
    }

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();