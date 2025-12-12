// Manajemen Pengguna Setting JavaScript
// User settings modal functionality for role and menu authorization management

document.addEventListener("DOMContentLoaded", function () {
    // Initialize setting modals
    initializeSettingModals();
});

// Initialize all setting modals on the page
function initializeSettingModals() {
    // Find all setting modals by pattern
    const settingModals = document.querySelectorAll('[id^="modalSetting_"]');

    settingModals.forEach((modal) => {
        const userId = modal.id.replace("modalSetting_", "");
        initializeSettingModal(userId);
    });
}

// Initialize a specific setting modal
function initializeSettingModal(userId) {
    const modal = document.getElementById(`modalSetting_${userId}`);
    if (!modal) {
        console.error(`Modal not found for user ${userId}`);
        return;
    }

    console.log(`Initializing setting modal for user ${userId}`);

    // Setup toggle komite access functionality
    setupKomiteToggle(userId);

    // Setup form submission handling
    setupSettingFormHandlers(userId);

    // Setup modal event listeners
    setupSettingModalEventListeners(userId);
}

// Setup komite access toggle functionality
function setupKomiteToggle(userId) {
    // Create dynamic function for komite toggle
    window[`toggleKomiteAccess_${userId}`] = function () {
        const komiteCheckbox = document.getElementById(`menu_komite_${userId}`);
        const komiteRoleCheckbox = document.getElementById(`komite_${userId}`);

        if (komiteCheckbox && komiteRoleCheckbox) {
            // If komite menu is unchecked, also uncheck komite role
            if (!komiteCheckbox.checked) {
                komiteRoleCheckbox.checked = false;
                // Notification disabled per request – silently update state
            } else {
                // If komite menu is checked, do NOT prompt any confirmation.
                // Keep current role checkbox state; admin can toggle role explicitly if needed.
                // This change removes the browser confirm dialog as requested.
                return;
            }
        }
    };
}

// Setup form submission handlers for setting modal
function setupSettingFormHandlers(userId) {
    const form = document.querySelector(`#modalSetting_${userId} form`);
    if (!form) {
        console.error(`Form not found for user ${userId}`);
        return;
    }

    console.log(`Setting up form handlers for user ${userId}`, form);

    form.addEventListener("submit", function (e) {
        console.log("=== FORM SUBMIT DEBUG START ===");
        console.log("Form submit triggered for user:", userId);
        console.log("Form element:", form);

        // Wait a bit to ensure all DOM updates are complete
        setTimeout(() => {
            // Debug: Log all checkboxes in the form
            const allCheckboxes = form.querySelectorAll(
                'input[type="checkbox"]'
            );
            console.log("All checkboxes in form:", allCheckboxes.length);

            // Find all menu checkboxes using multiple selectors to be sure
            const menuCheckboxes1 = form.querySelectorAll(
                'input[name^="menu_"]'
            );
            const menuCheckboxes2 = document.querySelectorAll(
                `#modalSetting_${userId} input[name^="menu_"]`
            );
            const menuCheckboxes3 = document.querySelectorAll(
                `input[id^="menu_"][id$="_${userId}"]`
            );

            console.log("Menu checkboxes method 1:", menuCheckboxes1.length);
            console.log("Menu checkboxes method 2:", menuCheckboxes2.length);
            console.log("Menu checkboxes method 3:", menuCheckboxes3.length);

            // Use the most reliable method
            const menuCheckboxes =
                menuCheckboxes2.length > 0 ? menuCheckboxes2 : menuCheckboxes1;

            // Filter for checked menus
            const checkedMenus = Array.from(menuCheckboxes).filter((cb) => {
                console.log(
                    `Checkbox ${cb.name} (id: ${cb.id}): checked=${cb.checked}, disabled=${cb.disabled}, value=${cb.value}`
                );
                return cb.checked;
            });

            console.log("Checked menus count:", checkedMenus.length);
            console.log(
                "Checked menu details:",
                checkedMenus.map((cb) => ({
                    name: cb.name,
                    id: cb.id,
                    checked: cb.checked,
                }))
            );

            console.log("=== FORM SUBMIT DEBUG END ===");
        }, 10);

        // Temporary: Allow all submissions to pass for debugging
        console.log("Allowing form submission for debugging...");
        showNotification("Menyimpan pengaturan...", "info");

        // Comment out validation temporarily
        /*
        // Allow submission if at least one menu is selected
        if (checkedMenus.length === 0) {
            e.preventDefault();
            console.error('No menus selected - preventing form submission');
            alert("Setidaknya satu menu harus dipilih!");
            return false;
        }

        // Show confirmation for settings change
        const checkedMenuNames = checkedMenus.map((cb) => cb.name.replace("menu_", ""));
        const checkedRoles = Array.from(
            form.querySelectorAll('input[name^="petugas_"]:checked')
        ).map((cb) => cb.name.replace("petugas_", ""));

        console.log('About to show confirmation dialog');
        const confirmMessage = `Anda akan mengubah pengaturan untuk user ini:\n\nMenu: ${checkedMenuNames.join(
            ", "
        )}\nRole: ${checkedRoles.join(", ")}\n\nLanjutkan?`;

        if (!confirm(confirmMessage)) {
            e.preventDefault();
            console.log('User cancelled confirmation');
            return false;
        }

        console.log('Form submission approved, proceeding...');
        showNotification("Menyimpan pengaturan...", "info");
        */
    });
}

// Setup modal event listeners for setting modal
function setupSettingModalEventListeners(userId) {
    const modal = document.getElementById(`modalSetting_${userId}`);
    if (!modal) return;

    modal.addEventListener("shown.bs.modal", function () {
        // Focus on first checkbox when modal opens
        const firstCheckbox = modal.querySelector(
            'input[type="checkbox"]:not([disabled])'
        );
        if (firstCheckbox) {
            firstCheckbox.focus();
        }

        // Initialize current state
        updateSettingSummary(userId);
    });

    // Listen for checkbox changes to update summary
    modal.addEventListener("change", function (e) {
        if (e.target.type === "checkbox") {
            updateSettingSummary(userId);
        }
    });
}

// Update setting summary for a specific user
function updateSettingSummary(userId) {
    const modal = document.getElementById(`modalSetting_${userId}`);
    if (!modal) return;

    const checkedMenus = modal.querySelectorAll(
        'input[name^="menu_"]:checked'
    ).length;
    const checkedRoles = modal.querySelectorAll(
        'input[name^="petugas_"]:checked'
    ).length;

    console.log(
        `User ${userId} - Menus: ${checkedMenus}, Roles: ${checkedRoles}`
    );
}

// Global function for force check all checkboxes
window.forceCheckAll = function () {
    const allCheckboxes = document.querySelectorAll(
        'input[type="checkbox"]:not([disabled])'
    );
    allCheckboxes.forEach((checkbox) => {
        checkbox.checked = true;
        checkbox.setAttribute("checked", "checked");
        checkbox.setAttribute("aria-checked", "true");
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    });
    showNotification("Semua pengaturan telah dipilih!", "success");
};

// Global function for force uncheck all checkboxes
window.forceUncheckAll = function () {
    const allCheckboxes = document.querySelectorAll(
        'input[type="checkbox"]:not([disabled])'
    );
    allCheckboxes.forEach((checkbox) => {
        checkbox.checked = false;
        checkbox.removeAttribute("checked");
        checkbox.setAttribute("aria-checked", "false");
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    });
    showNotification("Semua pengaturan telah dihapus!", "info");
};

// Show notification function
function showNotification(message, type) {
    const existingNotifications = document.querySelectorAll(
        ".alert.position-fixed"
    );
    existingNotifications.forEach((notification) => notification.remove());

    const notification = document.createElement("div");
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText =
        "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    notification.innerHTML = `
        <i class="bi bi-${
            type === "success"
                ? "check-circle"
                : type === "danger"
                ? "exclamation-triangle"
                : "info-circle"
        } me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Create dynamic toggle function for komite access
function createToggleKomiteAccess() {
    // This will be called from HTML with specific user ID
    window.toggleKomiteAccess = function (userId) {
        if (window[`toggleKomiteAccess_${userId}`]) {
            window[`toggleKomiteAccess_${userId}`]();
        }
    };
}

// Utility function to get all user IDs from setting modals
function getAllSettingUserIds() {
    const settingModals = document.querySelectorAll('[id^="modalSetting_"]');
    return Array.from(settingModals).map((modal) =>
        modal.id.replace("modalSetting_", "")
    );
}

// Utility function to validate setting for a specific user
function validateUserSetting(userId) {
    const modal = document.getElementById(`modalSetting_${userId}`);
    if (!modal) return false;

    const menuCheckboxes = modal.querySelectorAll(
        'input[name^="menu_"]:checked'
    );
    return menuCheckboxes.length > 0;
}

// Utility function to reset setting modal for a specific user
function resetUserSetting(userId) {
    const modal = document.getElementById(`modalSetting_${userId}`);
    if (!modal) return;

    // Reset all checkboxes to unchecked (except disabled ones)
    const checkboxes = modal.querySelectorAll(
        'input[type="checkbox"]:not([disabled])'
    );
    checkboxes.forEach((checkbox) => {
        checkbox.checked = false;
        checkbox.removeAttribute("checked");
        checkbox.setAttribute("aria-checked", "false");
    });

    // Note: No default menu to check as all menus are optional based on the HTML structure

    updateSettingSummary(userId);
}

// Initialize dynamic functions
createToggleKomiteAccess();

// Export functions for external use
window.ManajemenPenggunaSetting = {
    initializeSettingModals,
    initializeSettingModal,
    getAllSettingUserIds,
    validateUserSetting,
    resetUserSetting,
    showNotification,
    forceCheckAll: window.forceCheckAll,
    forceUncheckAll: window.forceUncheckAll,
};
