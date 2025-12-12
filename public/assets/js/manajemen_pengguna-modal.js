// Manajemen Pengguna Modal JavaScript
// Form validation and interaction handlers for user management modal

document.addEventListener("DOMContentLoaded", function () {
    // Fungsi validasi khusus untuk NIK
    window.validateNIK = function (input) {
        const nikValue = input.value;
        const nikIcon = document.getElementById("nikIcon");
        const icon = nikIcon.querySelector("i");

        // Hanya izinkan input angka
        const numericValue = nikValue.replace(/[^0-9]/g, "");
        if (nikValue !== numericValue) {
            input.value = numericValue;
        }

        // Validasi NIK: harus 16 digit angka
        const isValidNIK = /^[0-9]{16}$/.test(numericValue);

        if (numericValue.length === 0) {
            // Kosong - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            nikIcon.classList.remove("text-success");
            nikIcon.classList.add("text-danger");
        } else if (isValidNIK) {
            // Valid - tampilkan icon centang
            icon.className = "bi bi-check-circle-fill";
            nikIcon.classList.remove("text-danger");
            nikIcon.classList.add("text-success");
        } else {
            // Tidak valid - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            nikIcon.classList.remove("text-success");
            nikIcon.classList.add("text-danger");
        }
    };

    // Fungsi validasi khusus untuk No. HP: format +62 di awal
    window.validateNoHP = function (input) {
        const raw = input.value;
        const noHpIcon = document.getElementById("noHpIcon");
        const icon = noHpIcon ? noHpIcon.querySelector("i") : null;

        // Normalisasi: hilangkan spasi, tanda hubung, dan karakter non-digit kecuali tanda + di awal
        let normalized = raw
            .replace(/\s+/g, " ") // pertahankan satu spasi untuk keterbacaan opsional
            .replace(/-{1,}/g, "-");

        // Bangun tampilan: pastikan prefix +62, lalu angka setelahnya
        let digits = raw.replace(/[^0-9]/g, "");
        if (raw.startsWith("+")) {
            // sudah ada plus, gunakan apa adanya
        } else {
            // tambahkan +62 jika user mulai dengan 0 atau 62 atau 8
            if (/^0/.test(digits)) digits = digits.replace(/^0/, "62");
            else if (/^8/.test(digits)) digits = "62" + digits;
            else if (!/^62/.test(digits)) digits = "62" + digits; // fallback
        }

        // Tampilkan kembali dengan format +62XXXXXXXXXXX (tanpa spasi untuk penyimpanan input)
        input.value = "+" + digits;

        // Validasi: 10-12 digit setelah +62 (standar Indonesia)
        const afterCc = digits.replace(/^62/, "");
        const isValid = /^\d{10,12}$/.test(afterCc); // 62 + 10-12 -> total 12-14 char termasuk 62

        if (!icon) return;
        if (afterCc.length === 0) {
            icon.className = "bi bi-exclamation-circle";
            noHpIcon.classList.remove("text-success");
            noHpIcon.classList.add("text-danger");
        } else if (isValid) {
            icon.className = "bi bi-check-circle-fill";
            noHpIcon.classList.remove("text-danger");
            noHpIcon.classList.add("text-success");
        } else {
            icon.className = "bi bi-exclamation-circle";
            noHpIcon.classList.remove("text-success");
            noHpIcon.classList.add("text-danger");
        }
    };

    // Fungsi validasi khusus untuk Email
    window.validateEmail = function (input) {
        const emailValue = input.value;
        const emailIcon = document.getElementById("emailIcon");
        const icon = emailIcon.querySelector("i");

        // Validasi Email: harus mengandung @ dan format email valid
        const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);

        if (emailValue.length === 0) {
            // Kosong - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            emailIcon.classList.remove("text-success");
            emailIcon.classList.add("text-danger");
        } else if (isValidEmail) {
            // Valid - tampilkan icon centang
            icon.className = "bi bi-check-circle-fill";
            emailIcon.classList.remove("text-danger");
            emailIcon.classList.add("text-success");
        } else {
            // Tidak valid - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            emailIcon.classList.remove("text-success");
            emailIcon.classList.add("text-danger");
        }
    };

    // Fungsi untuk mengupdate icon berdasarkan nilai field (global scope)
    window.updateFieldIcon = function (field) {
        // Skip NIK, No. HP, dan Email field karena sudah ada validasi khusus
        if (
            field.id === "nikInput" ||
            field.id === "noHpInput" ||
            field.id === "emailInput"
        ) {
            return;
        }

        const warningIcon = field.nextElementSibling;
        if (field.value.trim() !== "") {
            const icon = warningIcon.querySelector("i");
            if (icon) {
                icon.className = "bi bi-check-circle-fill";
                warningIcon.classList.remove("text-danger");
                warningIcon.classList.add("text-success");
            }
        } else {
            const icon = warningIcon.querySelector("i");
            if (icon) {
                icon.className = "bi bi-exclamation-circle";
                warningIcon.classList.remove("text-success");
                warningIcon.classList.add("text-danger");
            }
        }
    };

    const form = document.querySelector("#modalTambahData form");
    if (form) {
        // Inisialisasi status icon saat modal dibuka
        const modal = document.getElementById("modalTambahData");
        if (modal) {
            modal.addEventListener("shown.bs.modal", function () {
                const inputs = form.querySelectorAll(
                    'input[type="text"], input[type="email"], input[type="password"], select'
                );
                inputs.forEach((input) => {
                    if (input.id === "nikInput") {
                        // Validasi khusus untuk NIK
                        window.validateNIK(input);
                    } else if (input.id === "noHpInput") {
                        // Validasi khusus untuk No. HP
                        window.validateNoHP(input);
                    } else if (input.id === "emailInput") {
                        // Validasi khusus untuk Email
                        window.validateEmail(input);
                    } else {
                        window.updateFieldIcon(input);
                    }
                });

                // Update icon untuk field yang sudah terisi otomatis
                setTimeout(function () {
                    const labels = document.querySelectorAll(
                        "#modalTambahData .form-label"
                    );
                    labels.forEach((label) => {
                        const inputGroup = label.nextElementSibling;
                        if (
                            inputGroup &&
                            inputGroup.classList.contains("input-group")
                        ) {
                            const input = inputGroup.querySelector(
                                "input[readonly], select"
                            );
                            if (input && input.value) {
                                if (input.id === "nikInput") {
                                    window.validateNIK(input);
                                } else if (input.id === "noHpInput") {
                                    window.validateNoHP(input);
                                } else if (input.id === "emailInput") {
                                    window.validateEmail(input);
                                } else if (window.updateFieldIcon) {
                                    window.updateFieldIcon(input);
                                }
                            }
                        }
                    });
                }, 100);
            });
        }

        form.addEventListener("submit", function (e) {
            // Validasi NIK sebelum submit
            const nikInput = document.getElementById("nikInput");
            if (nikInput && !/^[0-9]{16}$/.test(nikInput.value)) {
                e.preventDefault();
                alert("NIK harus berisi 16 digit angka!");
                return false;
            }

            // Validasi No. HP sebelum submit
            const noHpInput = document.getElementById("noHpInput");
            if (noHpInput) {
                // valid bila mulai dengan +62 dan memiliki 10-12 digit setelah 62
                const digits = noHpInput.value.replace(/[^0-9]/g, "");
                const after = digits.replace(/^62/, "");
                const ok = /^\d{10,12}$/.test(after);
                if (!ok) {
                    e.preventDefault();
                    alert("No. HP harus menggunakan awalan +62 dan 10-12 digit setelahnya.");
                    return false;
                }
            }

            // Validasi Email sebelum submit
            const emailInput = document.getElementById("emailInput");
            if (
                emailInput &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)
            ) {
                e.preventDefault();
                alert("Email harus berisi format yang valid!");
                return false;
            }

            const requiredFields =
                document.querySelectorAll(".required-warning");
            requiredFields.forEach((field) => {
                field.style.display = "block";
                // Reset ke icon warning saat submit
                const icon = field.querySelector("i");
                if (icon) {
                    icon.className = "bi bi-exclamation-circle";
                    field.classList.remove("text-success");
                    field.classList.add("text-danger");
                }
            });
        });

        form.addEventListener("input", function (e) {
            if (e.target.id === "nikInput") {
                // Validasi khusus untuk NIK
                window.validateNIK(e.target);
            } else if (e.target.id === "noHpInput") {
                // Validasi khusus untuk No. HP
                window.validateNoHP(e.target);
            } else if (e.target.id === "emailInput") {
                // Validasi khusus untuk Email
                window.validateEmail(e.target);
            } else {
                window.updateFieldIcon(e.target);
            }
        });

        // Tambahkan event listener untuk select dropdown
        form.addEventListener("change", function (e) {
            if (e.target.tagName === "SELECT") {
                window.updateFieldIcon(e.target);
            }
        });
    }

    // Update icon untuk field yang sudah terisi otomatis saat halaman dimuat
    setTimeout(function () {
        const labels = document.querySelectorAll(
            "#modalTambahData .form-label"
        );
        labels.forEach((label) => {
            const inputGroup = label.nextElementSibling;
            if (inputGroup && inputGroup.classList.contains("input-group")) {
                const input = inputGroup.querySelector(
                    "input[readonly], select"
                );
                if (input && input.value) {
                    if (input.id === "nikInput") {
                        window.validateNIK(input);
                    } else if (input.id === "noHpInput") {
                        window.validateNoHP(input);
                    } else if (input.id === "emailInput") {
                        window.validateEmail(input);
                    } else if (window.updateFieldIcon) {
                        window.updateFieldIcon(input);
                    }
                }
            }
        });
    }, 200);
});

// Additional utility functions for user modal
function resetModalForm() {
    const form = document.getElementById("formTambahUser");
    if (form) {
        form.reset();

        // Reset all validation icons to warning state
        const requiredWarnings = form.querySelectorAll(".required-warning");
        requiredWarnings.forEach((warning) => {
            const icon = warning.querySelector("i");
            if (icon) {
                icon.className = "bi bi-exclamation-circle";
                warning.classList.remove("text-success");
                warning.classList.add("text-danger");
            }
        });

        // Remove error classes
        form.querySelectorAll(".is-invalid").forEach((input) => {
            input.classList.remove("is-invalid");
        });

        // Clear error messages
        form.querySelectorAll(".invalid-feedback").forEach((error) => {
            error.textContent = "";
        });
    }
}

// Validate all fields in the modal
function validateAllFields() {
    const form = document.getElementById("formTambahUser");
    if (!form) return false;

    let isValid = true;

    // Validate NIK
    const nikInput = document.getElementById("nikInput");
    if (nikInput) {
        window.validateNIK(nikInput);
        if (!/^[0-9]{16}$/.test(nikInput.value)) {
            isValid = false;
        }
    }

    // Validate No. HP
    const noHpInput = document.getElementById("noHpInput");
    if (noHpInput) {
        window.validateNoHP(noHpInput);
        if (!/^\+62[0-9]{10,12}$/.test(noHpInput.value)) {
            isValid = false;
        }
    }

    // Validate Email
    const emailInput = document.getElementById("emailInput");
    if (emailInput) {
        window.validateEmail(emailInput);
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            isValid = false;
        }
    }

    // Validate other required fields
    const requiredInputs = form.querySelectorAll(
        "input[required], select[required]"
    );
    requiredInputs.forEach((input) => {
        if (
            input.id !== "nikInput" &&
            input.id !== "noHpInput" &&
            input.id !== "emailInput"
        ) {
            window.updateFieldIcon(input);
            if (!input.value.trim()) {
                isValid = false;
            }
        }
    });

    return isValid;
}

// Export functions for external use
window.ManajemenPenggunaModal = {
    validateNIK: window.validateNIK,
    validateNoHP: window.validateNoHP,
    validateEmail: window.validateEmail,
    updateFieldIcon: window.updateFieldIcon,
    resetModalForm,
    validateAllFields,
};
