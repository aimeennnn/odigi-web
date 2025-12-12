// ====== JavaScript untuk fitur Detail Data pada Komite ======

/**
 * Toggle Detail Sidebar - Show/Hide sidebar detail
 */
function toggleDetailSidebar() {
    console.log("toggleDetailSidebar function called");

    const sidebar = document.getElementById("detailSidebar");
    const modalDialog = document.getElementById("modalDialog");

    if (!sidebar) {
        console.error("Detail sidebar element not found");
        alert(
            "Detail sidebar element not found. Please check if the sidebar HTML is properly loaded."
        );
        return;
    }

    console.log("Sidebar found:", sidebar);
    console.log("Current sidebar classes:", sidebar.className);

    if (sidebar.classList.contains("show")) {
        // Hide sidebar
        sidebar.classList.remove("show");
        if (modalDialog) {
            modalDialog.classList.remove("sidebar-open");
        }
        console.log("Detail sidebar hidden");
    } else {
        // Show sidebar
        sidebar.classList.add("show");
        if (modalDialog) {
            modalDialog.classList.add("sidebar-open");
        }
        console.log("Detail sidebar shown");
    }

    // Log final state
    console.log("Final sidebar classes:", sidebar.className);
}

/**
 * Switch Detail Tab - Navigate between different tabs in sidebar
 * @param {string} tabName - Name of the tab to switch to
 */
function switchDetailTab(tabName) {
    // Remove active class from all tabs
    const allTabs = document.querySelectorAll(".nav-tab");
    allTabs.forEach((tab) => {
        tab.classList.remove("active");
    });

    // Add active class to clicked tab
    const clickedTab = document.querySelector(`[data-tab="${tabName}"]`);
    if (clickedTab) {
        clickedTab.classList.add("active");
    }

    // Hide all tab contents
    const allTabContents = document.querySelectorAll(".tab-content");
    allTabContents.forEach((content) => {
        content.classList.remove("active");
    });

    // Show selected tab content
    const selectedTabContent = document.getElementById(`tab-${tabName}`);
    if (selectedTabContent) {
        selectedTabContent.classList.add("active");
    }

    console.log(`Switched to tab: ${tabName}`);
}

/**
 * Toggle Data Detail - Expand/Collapse data card details
 * @param {number} dataId - ID of the data to toggle
 */
function toggleDataDetail(dataId) {
    const detailElement = document.getElementById(`data-detail-${dataId}`);
    const cardElement = document.querySelector(`[data-data-id="${dataId}"]`);
    const indicator = cardElement
        ? cardElement.querySelector(".toggle-indicator i")
        : null;

    if (!detailElement) {
        console.error(`Data detail element not found for ID: ${dataId}`);
        return;
    }

    if (
        detailElement.style.display === "none" ||
        detailElement.style.display === ""
    ) {
        // Show detail
        detailElement.style.display = "block";
        if (cardElement) {
            cardElement.classList.add("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(180deg)";
        }
        console.log(`Data detail expanded for ID: ${dataId}`);
    } else {
        // Hide detail
        detailElement.style.display = "none";
        if (cardElement) {
            cardElement.classList.remove("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(0deg)";
        }
        console.log(`Data detail collapsed for ID: ${dataId}`);
    }
}

/**
 * Toggle Bank Detail - Expand/Collapse bank card details
 * @param {number} bankId - ID of the bank to toggle
 */
function toggleBankDetail(bankId) {
    const detailElement = document.getElementById(`bank-detail-${bankId}`);
    const cardElement = document.querySelector(`[data-bank-id="${bankId}"]`);
    const indicator = cardElement
        ? cardElement.querySelector(".toggle-indicator i")
        : null;

    if (!detailElement) {
        console.error(`Bank detail element not found for ID: ${bankId}`);
        return;
    }

    if (
        detailElement.style.display === "none" ||
        detailElement.style.display === ""
    ) {
        // Show detail
        detailElement.style.display = "block";
        if (cardElement) {
            cardElement.classList.add("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(180deg)";
        }
        console.log(`Bank detail expanded for ID: ${bankId}`);
    } else {
        // Hide detail
        detailElement.style.display = "none";
        if (cardElement) {
            cardElement.classList.remove("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(0deg)";
        }
        console.log(`Bank detail collapsed for ID: ${bankId}`);
    }
}

/**
 * Toggle SLIK Detail - Expand/Collapse SLIK card details
 * @param {number} slikId - ID of the SLIK to toggle
 */
function toggleSlikDetail(slikId) {
    const detailElement = document.getElementById(`slik-detail-${slikId}`);
    const cardElement = document.querySelector(`[data-slik-id="${slikId}"]`);
    const indicator = cardElement
        ? cardElement.querySelector(".toggle-indicator i")
        : null;

    if (!detailElement) {
        console.error(`SLIK detail element not found for ID: ${slikId}`);
        return;
    }

    if (
        detailElement.style.display === "none" ||
        detailElement.style.display === ""
    ) {
        // Show detail
        detailElement.style.display = "block";
        if (cardElement) {
            cardElement.classList.add("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(180deg)";
        }
        console.log(`SLIK detail expanded for ID: ${slikId}`);
    } else {
        // Hide detail
        detailElement.style.display = "none";
        if (cardElement) {
            cardElement.classList.remove("expanded");
        }
        if (indicator) {
            indicator.style.transform = "rotate(0deg)";
        }
        console.log(`SLIK detail collapsed for ID: ${slikId}`);
    }
}

/**
 * Initialize Detail Sidebar - Setup initial state
 */
function initializeDetailSidebar() {
    console.log("Initializing detail sidebar...");

    // Close sidebar when modal is closed
    const modal = document.getElementById("modalTambahKomite");
    if (modal) {
        modal.addEventListener("hidden.bs.modal", function () {
            console.log("Modal closed, hiding sidebar");
            const sidebar = document.getElementById("detailSidebar");
            const modalDialog = document.getElementById("modalDialog");

            if (sidebar) {
                sidebar.classList.remove("show");
            }
            if (modalDialog) {
                modalDialog.classList.remove("sidebar-open");
            }
        });
    } else {
        console.warn("Modal element not found");
    }

    // Add click event listener to detail button
    const detailButton = document.querySelector(
        '[onclick="toggleDetailSidebar()"]'
    );
    if (detailButton) {
        console.log("Detail button found, adding event listener");
        detailButton.addEventListener("click", function (event) {
            event.preventDefault();
            console.log("Detail button clicked via event listener");
            toggleDetailSidebar();
        });
    } else {
        console.warn("Detail button not found");
    }

    // Close sidebar when clicking outside (optional)
    document.addEventListener("click", function (event) {
        const sidebar = document.getElementById("detailSidebar");
        const detailButton = event.target.closest(
            '[onclick="toggleDetailSidebar()"]'
        );

        if (sidebar && sidebar.classList.contains("show") && !detailButton) {
            // Check if click is outside sidebar and modal
            const isInsideSidebar = sidebar.contains(event.target);
            const isInsideModal = document
                .querySelector(".modal-dialog")
                ?.contains(event.target);

            if (!isInsideSidebar && !isInsideModal) {
                toggleDetailSidebar();
            }
        }
    });

    console.log("Detail sidebar initialized successfully");
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    initializeDetailSidebar();
    console.log("Komite JavaScript functions loaded successfully");
});

// Export functions for global access (if needed)
window.toggleDetailSidebar = toggleDetailSidebar;
window.switchDetailTab = switchDetailTab;
window.toggleDataDetail = toggleDataDetail;
window.toggleBankDetail = toggleBankDetail;
window.toggleSlikDetail = toggleSlikDetail;
