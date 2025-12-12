// Manajemen Pengguna Modal Update JavaScript
// Form validation and interaction handlers for user update modals

document.addEventListener("DOMContentLoaded", function () {
    // Initialize all update modals on the page
    initializeUpdateModals();
});

// Initialize all update modals
function initializeUpdateModals() {
    // Find all update modals by pattern
    const updateModals = document.querySelectorAll('[id^="modalEditUser_"]');

    updateModals.forEach((modal) => {
        const userId = modal.id.replace("modalEditUser_", "");
        initializeUpdateModal(userId);
    });
}

// Initialize a specific update modal
function initializeUpdateModal(userId) {
    // Create validation functions for this specific user
    createValidationFunctions(userId);

    // Setup form event listeners
    setupFormEventListeners(userId);

    // Setup modal event listeners
    setupModalEventListeners(userId);
}

// Create validation functions for a specific user ID
function createValidationFunctions(userId) {
    // Fungsi validasi khusus untuk NIK
    window[`validateNIK_${userId}`] = function (input) {
        const nikValue = input.value;
        const nikIcon = document.getElementById(`nikIcon_${userId}`);
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

    // Fungsi validasi khusus untuk No. HP
    window[`validateNoHP_${userId}`] = function (input) {
        const noHpValue = input.value || '';
        const noHpIcon = document.getElementById(`noHpIcon_${userId}`);
        const icon = noHpIcon.querySelector("i");

        // Normalisasi ke format +62XXXXXXXXXXX
        let v = noHpValue.replace(/[^0-9+]/g, '');
        if (v.startsWith('+')) {
            // ok
        } else if (v.startsWith('62')) {
            v = '+' + v;
        } else if (v.startsWith('0')) {
            v = '+62' + v.substring(1);
        } else if (v.startsWith('8')) {
            v = '+62' + v;
        } else if (v === '') {
            v = '+62';
        } else {
            v = '+62' + v;
        }
        let digits = v.slice(3).replace(/\D/g, '');
        if (digits.length > 12) digits = digits.slice(0, 12);
        input.value = '+62' + digits;

        // Validasi No. HP: harus +62 diikuti 10-12 digit angka (standar Indonesia)
        const isValidNoHP = /^\+62[0-9]{10,12}$/.test(input.value);

        if (digits.length === 0) {
            // Kosong - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            noHpIcon.classList.remove("text-success");
            noHpIcon.classList.add("text-danger");
        } else if (isValidNoHP) {
            // Valid - tampilkan icon centang
            icon.className = "bi bi-check-circle-fill";
            noHpIcon.classList.remove("text-danger");
            noHpIcon.classList.add("text-success");
        } else {
            // Tidak valid - tampilkan icon warning
            icon.className = "bi bi-exclamation-circle";
            noHpIcon.classList.remove("text-success");
            noHpIcon.classList.add("text-danger");
        }
    };

    // Fungsi validasi khusus untuk Email
    window[`validateEmail_${userId}`] = function (input) {
        const emailValue = input.value;
        const emailIcon = document.getElementById(`emailIcon_${userId}`);
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

    // Fungsi untuk mengupdate icon berdasarkan nilai field (untuk field biasa)
    window[`updateFieldIcon_${userId}`] = function (field) {
        // Skip NIK, No. HP, Email, dan Username field karena sudah ada validasi khusus atau readonly
        if (
            field.id === `nikInput_${userId}` ||
            field.id === `noHpInput_${userId}` ||
            field.id === `emailInput_${userId}` ||
            field.id === `usernameInput_${userId}`
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

    // Fungsi validasi password
    window[`validatePassword_${userId}`] = function () {
        const passwordInput = document.getElementById(
            `passwordInput_${userId}`
        );
        const passwordConfirmationInput = document.getElementById(
            `passwordConfirmationInput_${userId}`
        );

        if (!passwordInput || !passwordConfirmationInput) return true;

        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;

        // Jika salah satu field diisi, keduanya harus diisi dan sama
        if (password || passwordConfirmation) {
            if (!password) {
                passwordInput.setCustomValidity(
                    "Password baru harus diisi jika ingin mengganti password"
                );
                return false;
            }
            if (!passwordConfirmation) {
                passwordConfirmationInput.setCustomValidity(
                    "Konfirmasi password harus diisi"
                );
                return false;
            }
            if (password !== passwordConfirmation) {
                passwordConfirmationInput.setCustomValidity(
                    "Konfirmasi password tidak sama dengan password baru"
                );
                return false;
            }
            if (password.length < 6) {
                passwordInput.setCustomValidity("Password minimal 6 karakter");
                return false;
            }
        }

        // Reset custom validity jika valid
        passwordInput.setCustomValidity("");
        passwordConfirmationInput.setCustomValidity("");
        return true;
    };
}

// Setup form event listeners for a specific user
function setupFormEventListeners(userId) {
    const form = document.querySelector(`#formEditUser_${userId}`);
    if (!form) return;

    form.addEventListener("submit", function (e) {
        // Validasi NIK sebelum submit
        const nikInput = document.getElementById(`nikInput_${userId}`);
        if (nikInput && !/^[0-9]{16}$/.test(nikInput.value)) {
            e.preventDefault();
            alert("NIK harus berisi 16 digit angka!");
            return false;
        }

        // Validasi No. HP sebelum submit
        const noHpInput = document.getElementById(`noHpInput_${userId}`);
        if (noHpInput && !/^\+62[0-9]{10,12}$/.test(noHpInput.value)) {
            e.preventDefault();
            alert("No. HP wajib format +62 diikuti 10-12 digit angka!");
            return false;
        }

        // Validasi Email sebelum submit
        const emailInput = document.getElementById(`emailInput_${userId}`);
        if (
            emailInput &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)
        ) {
            e.preventDefault();
            alert("Email harus berisi format yang valid!");
            return false;
        }

        // Validasi Password sebelum submit
        if (!window[`validatePassword_${userId}`]()) {
            e.preventDefault();
            return false;
        }
    });

    form.addEventListener("input", function (e) {
        // Skip password fields as they are optional
        if (e.target.type !== "password") {
            if (e.target.id === `nikInput_${userId}`) {
                window[`validateNIK_${userId}`](e.target);
            } else if (e.target.id === `noHpInput_${userId}`) {
                window[`validateNoHP_${userId}`](e.target);
            } else if (e.target.id === `emailInput_${userId}`) {
                window[`validateEmail_${userId}`](e.target);
            } else if (e.target.id !== `usernameInput_${userId}`) {
                // Skip username field karena readonly
                window[`updateFieldIcon_${userId}`](e.target);
            }
        }
    });

    // Tambahkan event listener untuk select dropdown
    form.addEventListener("change", function (e) {
        if (e.target.tagName === "SELECT") {
            window[`updateFieldIcon_${userId}`](e.target);
        }
    });
}

// Setup modal event listeners for a specific user
function setupModalEventListeners(userId) {
    const modal = document.getElementById(`modalEditUser_${userId}`);
    if (!modal) return;

    modal.addEventListener("shown.bs.modal", function () {
        // Validasi field khusus saat modal dibuka
        const nikInput = document.getElementById(`nikInput_${userId}`);
        const noHpInput = document.getElementById(`noHpInput_${userId}`);
        const emailInput = document.getElementById(`emailInput_${userId}`);

        if (nikInput) window[`validateNIK_${userId}`](nikInput);
        if (noHpInput) window[`validateNoHP_${userId}`](noHpInput);
        if (emailInput) window[`validateEmail_${userId}`](emailInput);

        // Validasi field biasa
        const form = document.querySelector(`#formEditUser_${userId}`);
        if (form) {
            const inputs = form.querySelectorAll('input[type="text"], select');
            inputs.forEach((input) => {
                if (
                    input.id !== `nikInput_${userId}` &&
                    input.id !== `noHpInput_${userId}` &&
                    input.id !== `emailInput_${userId}` &&
                    input.id !== `usernameInput_${userId}`
                ) {
                    window[`updateFieldIcon_${userId}`](input);
                }
            });
        }
    });
}

// Utility function to reset a specific update modal
function resetUpdateModal(userId) {
    const form = document.getElementById(`formEditUser_${userId}`);
    if (form) {
        // Reset all validation icons to success state (since this is update with existing data)
        const requiredWarnings = form.querySelectorAll(".required-warning");
        requiredWarnings.forEach((warning) => {
            const icon = warning.querySelector("i");
            if (icon) {
                icon.className = "bi bi-check-circle-fill";
                warning.classList.remove("text-danger");
                warning.classList.add("text-success");
            }
        });

        // Clear password fields
        const passwordInput = document.getElementById(
            `passwordInput_${userId}`
        );
        const passwordConfirmationInput = document.getElementById(
            `passwordConfirmationInput_${userId}`
        );
        if (passwordInput) passwordInput.value = "";
        if (passwordConfirmationInput) passwordConfirmationInput.value = "";

        // Remove error classes
        form.querySelectorAll(".is-invalid").forEach((input) => {
            input.classList.remove("is-invalid");
        });

        // Clear error messages
        form.querySelectorAll(".invalid-feedback").forEach((error) => {
            error.textContent = "";
        });

        // Reset custom validity
        const allInputs = form.querySelectorAll("input");
        allInputs.forEach((input) => {
            input.setCustomValidity("");
        });
    }
}

// Validate all fields in a specific update modal
function validateUpdateModal(userId) {
    const form = document.getElementById(`formEditUser_${userId}`);
    if (!form) return false;

    let isValid = true;

    // Validate NIK
    const nikInput = document.getElementById(`nikInput_${userId}`);
    if (nikInput) {
        window[`validateNIK_${userId}`](nikInput);
        if (!/^[0-9]{16}$/.test(nikInput.value)) {
            isValid = false;
        }
    }

    // Validate No. HP
    const noHpInput = document.getElementById(`noHpInput_${userId}`);
    if (noHpInput) {
        window[`validateNoHP_${userId}`](noHpInput);
        if (!/^\+62[0-9]{10,12}$/.test(noHpInput.value)) {
            isValid = false;
        }
    }

    // Validate Email
    const emailInput = document.getElementById(`emailInput_${userId}`);
    if (emailInput) {
        window[`validateEmail_${userId}`](emailInput);
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            isValid = false;
        }
    }

    // Validate Password if provided
    if (!window[`validatePassword_${userId}`]()) {
        isValid = false;
    }

    // Validate other required fields
    const requiredInputs = form.querySelectorAll(
        "input[required], select[required]"
    );
    requiredInputs.forEach((input) => {
        if (
            input.id !== `nikInput_${userId}` &&
            input.id !== `noHpInput_${userId}` &&
            input.id !== `emailInput_${userId}` &&
            input.id !== `usernameInput_${userId}`
        ) {
            window[`updateFieldIcon_${userId}`](input);
            if (!input.value.trim()) {
                isValid = false;
            }
        }
    });

    return isValid;
}

// Get all user IDs from update modals on the page
function getAllUserIds() {
    const updateModals = document.querySelectorAll('[id^="modalEditUser_"]');
    return Array.from(updateModals).map((modal) =>
        modal.id.replace("modalEditUser_", "")
    );
}

// Bulk validate all update modals
function validateAllUpdateModals() {
    const userIds = getAllUserIds();
    let allValid = true;

    userIds.forEach((userId) => {
        if (!validateUpdateModal(userId)) {
            allValid = false;
        }
    });

    return allValid;
}

// Export functions for external use
window.ManajemenPenggunaModalUpdate = {
    initializeUpdateModals,
    initializeUpdateModal,
    resetUpdateModal,
    validateUpdateModal,
    getAllUserIds,
    validateAllUpdateModals,
};
