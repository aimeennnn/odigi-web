// Manajemen Pengguna Index JavaScript
// User management functionality, form validation, search, and pagination

// Global variables
let hasErrors = false;

// Initialize page with error state
function initializeWithErrors(errorState) {
    hasErrors = errorState;
}

document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable (commented out but ready to use)
    const el = document.getElementById("user-table");
    if (el) {
        // window.jQuery(el).DataTable({
        //     pageLength: 10,
        //     lengthChange: true,
        //     order: [[1, 'asc']],
        // });
    }

    // Auto-open modal if there are validation errors
    if (hasErrors) {
        const modal = new bootstrap.Modal(
            document.getElementById("modalTambahData")
        );
        modal.show();
    }

    // Form validation
    const form = document.getElementById("formTambahUser");
    if (form) {
        form.addEventListener("submit", function (e) {
            const password = form.querySelector('input[name="password"]').value;
            const passwordConfirmation = form.querySelector(
                'input[name="password_confirmation"]'
            ).value;

            if (password !== passwordConfirmation) {
                e.preventDefault();
                alert("Password dan konfirmasi password tidak cocok!");
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert("Password minimal 6 karakter!");
                return false;
            }
        });
    }

    // Clear form when modal is closed
    const modalElement = document.getElementById("modalTambahData");
    if (modalElement && form) {
        modalElement.addEventListener("hidden.bs.modal", function () {
            form.reset();
            // Remove error classes
            form.querySelectorAll(".is-invalid").forEach((input) => {
                input.classList.remove("is-invalid");
            });
            // Clear error messages
            form.querySelectorAll(".invalid-feedback").forEach((error) => {
                error.textContent = "";
            });
        });
    }
});

// Initialize success notification auto-hide
function initSuccessNotification() {
    setTimeout(function () {
        const notification = document.querySelector(".alert.position-fixed");
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Pagination function
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set("per_page", perPage);
    url.searchParams.delete("page"); // Reset to first page
    window.location.href = url.toString();
}

// Search function with debouncing
function searchUsers(searchTerm) {
    // Debounce search to avoid too many requests
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(function () {
        const url = new URL(window.location);
        if (searchTerm.trim()) {
            url.searchParams.set("search", searchTerm.trim());
        } else {
            url.searchParams.delete("search");
        }
        url.searchParams.delete("page"); // Reset to first page
        window.location.href = url.toString();
    }, 500); // Wait 500ms after user stops typing
}

// Additional utility functions for user management
function toggleUserStatus(userId, currentStatus) {
    if (!confirm("Apakah Anda yakin ingin mengubah status user ini?")) {
        return;
    }

    // Implementation for status toggle
    console.log(`Toggling user ${userId} from ${currentStatus}`);
    // Add AJAX call here if needed
}

function refreshUserTable() {
    // Refresh the current page to update user data
    window.location.reload();
}

// Export functions for external use
window.ManajemenPengguna = {
    initializeWithErrors,
    initSuccessNotification,
    changePerPage,
    searchUsers,
    toggleUserStatus,
    refreshUserTable,
};
