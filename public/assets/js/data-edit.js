// Data Edit JavaScript
// Form field management and validation for edit data form

// Fungsi untuk update icon validation
function updateValidationIcon(inputElement, isValid, isEmpty) {
    const iconElement = inputElement
        .closest(".input-group")
        .querySelector(".required-warning i");
    if (!iconElement) return;

    if (isEmpty) {
        // Icon tanda seru untuk field kosong
        iconElement.className = "bi bi-exclamation-triangle-fill text-warning";
    } else if (isValid) {
        // Icon centang untuk field valid
        iconElement.className = "bi bi-check-circle-fill text-success";
    } else {
        // Icon X untuk field invalid
        iconElement.className = "bi bi-x-circle-fill text-danger";
    }
}

// Fungsi validasi universal untuk semua field Data
function validateField(inputElement) {
    const value = inputElement.value.trim();
    const fieldName = inputElement.name;
    let isValid = false;
    const isEmpty = value === "";

    // Jika field kosong, langsung return dengan status kosong
    if (isEmpty) {
        updateValidationIcon(inputElement, false, true);
        return false;
    }

    // Validasi berdasarkan jenis field
    switch (fieldName) {
        case "id_reg":
            isValid = value !== "";
            break;
        case "jenis_data":
            isValid = [
                "KTP",
                "e-KTP",
                "NIK",
                "KK",
                "AK (Akta Kelahiran)",
                "IJZ (Ijazah)",
                "PS (Paspor)",
                "SIM",
                "NPWP",
                "BPJS",
                "SKCK"
            ].includes(value);
            break;
        case "keterangan":
            isValid = value !== ""; // Wajib diisi minimal 1 karakter
            break;
        case "file":
            // File upload tidak perlu validasi khusus
            isValid = true;
            break;
        default:
            isValid = value !== "";
    }

    updateValidationIcon(inputElement, isValid, isEmpty);
    return isValid;
}

document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi validasi untuk semua field yang sudah ada nilainya
    function initializeValidation() {
        // Delay sedikit untuk memastikan DOM sudah selesai di-render
        setTimeout(() => {
            const allInputs = document.querySelectorAll(
                "input, select, textarea"
            );
            allInputs.forEach((input) => {
                if (input.name && input.name !== "file") {
                    // Skip file upload
                    validateField(input);
                }
            });
        }, 100);
    }

    // Event listener untuk validasi real-time
    function setupRealTimeValidation() {
        // Validasi untuk input text
        document
            .querySelectorAll('input[type="text"], textarea')
            .forEach((input) => {
                input.addEventListener("input", function () {
                    // Auto uppercase untuk field tertentu
                    if (this.classList.contains("upper")) {
                        this.value = this.value.toUpperCase();
                    }
                    validateField(this);
                });
                input.addEventListener("blur", function () {
                    validateField(this);
                });
                input.addEventListener("focus", function () {
                    validateField(this);
                });
            });

        // Validasi untuk select
        document.querySelectorAll("select").forEach((select) => {
            select.addEventListener("change", function () {
                validateField(this);
            });
            select.addEventListener("focus", function () {
                validateField(this);
            });
            select.addEventListener("blur", function () {
                validateField(this);
            });
        });

        // Validasi untuk file input
        document.querySelectorAll('input[type="file"]').forEach((input) => {
            input.addEventListener("change", function () {
                // File upload selalu valid jika ada file
                const isValid = this.files.length > 0;
                updateValidationIcon(this, isValid, this.files.length === 0);
            });
        });
    }

    // Jalankan setup validasi terlebih dahulu
    setupRealTimeValidation();

    // Jalankan inisialisasi validasi setelah setup selesai
    initializeValidation();
});
