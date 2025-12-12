// SLIK Edit JavaScript
// Form field management and validation for edit SLIK form

// Fungsi untuk update icon validation
function updateValidationIcon(inputElement, isValid, isEmpty) {
    const group = inputElement.closest(".input-group");
    if (!group) return;
    const iconElement = group.querySelector(".required-warning i");
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

// Fungsi validasi nomor identitas
function validateNomorIdentitas(value) {
    // Hanya angka 0-9, minimal 14 digit, maksimal 16 digit
    const numericRegex = /^[0-9]+$/;
    return numericRegex.test(value) && value.length >= 14 && value.length <= 16;
}

// Fungsi validasi universal untuk semua field SLIK
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
        case "nama":
            isValid = value.length >= 2;
            break;
        case "no_identitas":
            isValid = validateNomorIdentitas(value);
            break;
        case "id_reg":
            isValid = value !== "";
            break;
        case "keterkaitan":
            isValid = [
                "Pribadi",
                "Pengurus",
                "Terkait",
                "Keluarga",
                "Lain-lain",
            ].includes(value);
            break;
        case "status":
            isValid = ["Proses", "Selesai", "Ditolak"].includes(value);
            break;
        case "tgl":
            isValid = value !== "";
            break;
        case "nomor":
            isValid = value.length >= 3; // Minimal 3 karakter untuk nomor SLIK
            break;
        default:
            isValid = value !== "";
    }

    updateValidationIcon(inputElement, isValid, isEmpty);
    return isValid;
}

// Fungsi untuk toggle field berdasarkan register yang dipilih
function updateRegisterFields() {
    const registerSelect = document.querySelector('select[name="id_reg"]');
    const namaInput = document.querySelector('input[name="nama"]');
    const noIdentitasInput = document.querySelector(
        'input[name="no_identitas"]'
    );

    if (registerSelect && namaInput && noIdentitasInput) {
        registerSelect.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                namaInput.value = selectedOption.dataset.nama || "";
                noIdentitasInput.value =
                    selectedOption.dataset.no_identitas || "";

                // Validasi field setelah update
                validateField(namaInput);
                validateField(noIdentitasInput);
                validateField(this);
            } else {
                namaInput.value = "";
                noIdentitasInput.value = "";

                // Validasi field setelah clear
                validateField(namaInput);
                validateField(noIdentitasInput);
                validateField(this);
            }
        });
    }
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
                if (input.name && input.name !== "nomor") {
                    // Skip nomor SLIK karena readonly
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
                if (input.name !== "nomor") {
                    input.addEventListener("input", function () {
                        if (input.name === "no_identitas") {
                            this.value = this.value.replace(/[^0-9]/g, "").slice(0, 16);
                        }
                        if (input.classList.contains("upper")) {
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
                }
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

        // Validasi untuk input date
        document.querySelectorAll('input[type="date"]').forEach((input) => {
            input.addEventListener("change", function () {
                validateField(this);
            });
            input.addEventListener("focus", function () {
                validateField(this);
            });
            input.addEventListener("blur", function () {
                validateField(this);
            });
        });
    }

    // Setup auto-update untuk field register
    updateRegisterFields();

    // Jalankan setup validasi terlebih dahulu
    setupRealTimeValidation();

    // Jalankan inisialisasi validasi setelah setup selesai
    initializeValidation();

    // Blokir submit jika No Identitas kurang dari 14 digit
    const form = document.querySelector('form[action*="slik/update"]');
    const noIdentitasInput = document.querySelector('input[name="no_identitas"]');
    if (form && noIdentitasInput) {
        form.addEventListener('submit', function(e) {
            if (!validateNomorIdentitas(noIdentitasInput.value)) {
                e.preventDefault();
                validateField(noIdentitasInput);
                noIdentitasInput.focus();
                alert('No Identitas harus diisi minimal 14 digit angka!');
            }
        });
    }

    // Auto-uppercase untuk input nama nasabah
    var namaInput = document.querySelector('input[name="nama"]');
    if (namaInput) {
        namaInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
});
