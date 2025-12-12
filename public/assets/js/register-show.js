// Register Show JavaScript
// Detail view functionality and interactions

document.addEventListener("DOMContentLoaded", function () {
    // Initialize page functionality
    initializeShowPage();

    // Initialize tab functionality if tabs exist
    initializeTabNavigation();

    // Initialize copy to clipboard functionality
    initializeCopyFunctionality();

    // Initialize success notification auto-hide if exists
    initializeNotifications();
});

// Initialize basic show page functionality
function initializeShowPage() {
    console.log("Register show page initialized");

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });
}

// Initialize tab navigation functionality
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    const tabPanes = document.querySelectorAll(".tab-pane");

    if (tabButtons.length > 0) {
        tabButtons.forEach((button) => {
            button.addEventListener("click", function (e) {
                e.preventDefault();

                // Remove active class from all buttons and panes
                tabButtons.forEach((btn) => btn.classList.remove("active"));
                tabPanes.forEach((pane) => {
                    pane.classList.remove("show", "active");
                });

                // Add active class to clicked button
                this.classList.add("active");

                // Show corresponding tab pane
                const targetId =
                    this.getAttribute("href") ||
                    this.getAttribute("data-bs-target");
                if (targetId) {
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add("show", "active");

                        // Trigger custom event for tab change
                        window.dispatchEvent(
                            new CustomEvent("tabChanged", {
                                detail: {
                                    tabId: targetId.replace("#", ""),
                                    tabElement: targetPane,
                                },
                            })
                        );
                    }
                }
            });
        });
    }
}

// Initialize copy to clipboard functionality
function initializeCopyFunctionality() {
    // Add copy buttons to important data fields
    const importantFields = document.querySelectorAll(
        ".info-value.highlight-value"
    );

    importantFields.forEach((field) => {
        // Create copy button
        const copyBtn = document.createElement("button");
        copyBtn.className = "btn btn-sm btn-outline-secondary ms-2";
        copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
        copyBtn.title = "Copy to clipboard";
        copyBtn.style.cssText = "font-size: 0.75rem; padding: 0.25rem 0.5rem;";

        copyBtn.addEventListener("click", function () {
            const textToCopy = field.textContent.trim();

            // Modern clipboard API
            if (navigator.clipboard) {
                navigator.clipboard
                    .writeText(textToCopy)
                    .then(() => {
                        showCopySuccess(copyBtn);
                    })
                    .catch(() => {
                        fallbackCopyTextToClipboard(textToCopy, copyBtn);
                    });
            } else {
                fallbackCopyTextToClipboard(textToCopy, copyBtn);
            }
        });

        // Add copy button to the field
        field.style.position = "relative";
        field.appendChild(copyBtn);
    });
}

// Fallback copy function for older browsers
function fallbackCopyTextToClipboard(text, button) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand("copy");
        showCopySuccess(button);
    } catch (err) {
        console.error("Fallback: Could not copy text: ", err);
        showCopyError(button);
    }

    document.body.removeChild(textArea);
}

// Show copy success feedback
function showCopySuccess(button) {
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.remove("btn-outline-secondary");
    button.classList.add("btn-success");

    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove("btn-success");
        button.classList.add("btn-outline-secondary");
    }, 2000);
}

// Show copy error feedback
function showCopyError(button) {
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-x"></i>';
    button.classList.remove("btn-outline-secondary");
    button.classList.add("btn-danger");

    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove("btn-danger");
        button.classList.add("btn-outline-secondary");
    }, 2000);
}

// Initialize print functionality
function initializePrintFunctionality() {
    // Add print button if it doesn't exist
    const actionButtons = document.querySelector(".mb-3.d-flex.gap-2");
    if (actionButtons && !document.querySelector(".btn-print")) {
        const printBtn = document.createElement("button");
        printBtn.className = "btn btn-info btn-print";
        printBtn.innerHTML = '<i class="bi bi-printer me-1"></i>Print';
        printBtn.addEventListener("click", function () {
            window.print();
        });

        // Insert before the first button
        actionButtons.insertBefore(printBtn, actionButtons.firstChild);
    }

    // Handle print styles
    const printStyles = `
        @media print {
            .btn, .mb-3.d-flex.gap-2, nav, .navbar {
                display: none !important;
            }
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
            .info-card {
                margin-bottom: 1rem !important;
            }
            .info-value button {
                display: none !important;
            }
        }
    `;

    // Add print styles to head
    if (!document.querySelector("#print-styles")) {
        const style = document.createElement("style");
        style.id = "print-styles";
        style.textContent = printStyles;
        document.head.appendChild(style);
    }
}

// Initialize notification functionality
function initializeNotifications() {
    // Auto-hide success/error notifications
    const notifications = document.querySelectorAll(".alert.position-fixed");
    notifications.forEach((notification) => {
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.style.transition = "opacity 0.3s ease";
                notification.style.opacity = "0";
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, 3000);
    });
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
}

// Utility function to format date
function formatDate(dateString, options = {}) {
    const defaultOptions = {
        year: "numeric",
        month: "long",
        day: "numeric",
    };

    const formatOptions = { ...defaultOptions, ...options };

    return new Date(dateString).toLocaleDateString("id-ID", formatOptions);
}

// Export functions for potential external use
window.RegisterShow = {
    initializeShowPage,
    initializeTabNavigation,
    initializeCopyFunctionality,
    initializePrintFunctionality,
    initializeNotifications,
    formatCurrency,
    formatDate,
};
