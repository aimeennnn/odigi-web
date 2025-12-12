// Register Index JavaScript
// Modal management, bulk operations, search functionality, and form handling

// Fungsi untuk memilih entitas dan membuka modal form
function pilihEntitas(jenisEntitas) {
    // Tutup modal pilih entitas
    const modalPilihEntitas = bootstrap.Modal.getInstance(
        document.getElementById("modalPilihEntitas")
    );
    modalPilihEntitas.hide();

    // Set jenis entitas ke form
    const form = document.querySelector("#modalTambahData form");
    if (form) {
        // Set jenis entitas ke hidden input
        const jenisEntitasInput = document.getElementById(
            "jenis_entitas_input"
        );
        if (jenisEntitasInput) {
            jenisEntitasInput.value = jenisEntitas;
        }

        // Toggle field berdasarkan jenis entitas
        const jenisKelaminContainer = form.querySelector(
            "#jenis-kelamin-container"
        );
        const namaPeroranganContainer = form.querySelector(
            "#nama-perorangan-container"
        );
        const namaBadanUsahaContainer = form.querySelector(
            "#nama-badan-usaha-container"
        );
        const badanUsahaFields = form.querySelector("#badan-usaha-fields");
        const noIdentitasContainer = form.querySelector(
            "#no-identitas-container"
        );
        const jenisIdentitasContainer = form.querySelector(
            "#jenis-identitas-container"
        );
        const pekerjaanContainer = form.querySelector("#pekerjaan-container");
        const alamatContainer = form.querySelector("#alamat-container");

        if (jenisEntitas === "badan_usaha") {
            // Sembunyikan field perorangan
            if (jenisKelaminContainer)
                jenisKelaminContainer.style.display = "none";
            if (namaPeroranganContainer)
                namaPeroranganContainer.style.display = "none";
            if (noIdentitasContainer)
                noIdentitasContainer.style.display = "none";
            if (jenisIdentitasContainer)
                jenisIdentitasContainer.style.display = "none";
            if (pekerjaanContainer) pekerjaanContainer.style.display = "none";
            if (alamatContainer) alamatContainer.style.display = "none";

            // Tampilkan field badan usaha
            if (namaBadanUsahaContainer)
                namaBadanUsahaContainer.style.display = "block";
            if (badanUsahaFields) badanUsahaFields.style.display = "block";

            // Set required untuk field badan usaha
            const badanUsahaInputs = badanUsahaFields
                ? badanUsahaFields.querySelectorAll("input, select, textarea")
                : [];
            badanUsahaInputs.forEach((input) => {
                if (
                    input.name &&
                    (input.name.includes("badan_usaha") ||
                        input.name === "jenis_dokumen_usaha" ||
                        input.name === "nomor_legalitas_usaha" ||
                        input.name === "bidang_usaha" ||
                        input.name === "alamat_usaha")
                ) {
                    input.required = true;
                }
            });

            // Set nama_badan_usaha sebagai required
            const namaBadanUsahaInput = form.querySelector(
                'input[name="nama_badan_usaha"]'
            );
            if (namaBadanUsahaInput) namaBadanUsahaInput.required = true;

            // Set nama perorangan tidak required
            const namaPeroranganInput =
                form.querySelector('input[name="nama"]');
            if (namaPeroranganInput) namaPeroranganInput.required = false;

            // Set field identitas tidak required untuk badan usaha
            const noIdentitasInput = form.querySelector(
                'input[name="no_identitas"]'
            );
            if (noIdentitasInput) noIdentitasInput.required = false;

            const jenisIdentitasInput = form.querySelector(
                'select[name="jns_identitas"]'
            );
            if (jenisIdentitasInput) jenisIdentitasInput.required = false;

            const pekerjaanInput = form.querySelector(
                'select[name="pekerjaan"]'
            );
            if (pekerjaanInput) pekerjaanInput.required = false;

            const alamatInput = form.querySelector('textarea[name="alamat"]');
            if (alamatInput) alamatInput.required = false;

            // Set field perorangan yang disembunyikan tidak required
            const jenisKelaminFieldHidden = form.querySelector(
                'select[name="jns_kelamin"]'
            );
            if (
                jenisKelaminFieldHidden &&
                jenisKelaminFieldHidden.offsetParent === null
            ) {
                jenisKelaminFieldHidden.required = false;
            }
        } else {
            // Sembunyikan field badan usaha
            if (namaBadanUsahaContainer)
                namaBadanUsahaContainer.style.display = "none";
            if (badanUsahaFields) badanUsahaFields.style.display = "none";

            // Tampilkan field perorangan
            if (jenisKelaminContainer)
                jenisKelaminContainer.style.display = "block";
            if (namaPeroranganContainer)
                namaPeroranganContainer.style.display = "block";
            if (noIdentitasContainer)
                noIdentitasContainer.style.display = "block";
            if (jenisIdentitasContainer)
                jenisIdentitasContainer.style.display = "block";
            if (pekerjaanContainer) pekerjaanContainer.style.display = "block";
            if (alamatContainer) alamatContainer.style.display = "block";

            // Set required untuk field perorangan
            const namaPeroranganInput =
                form.querySelector('input[name="nama"]');
            if (namaPeroranganInput) namaPeroranganInput.required = true;

            const jenisKelaminField = form.querySelector(
                'select[name="jns_kelamin"]'
            );
            if (jenisKelaminField && jenisKelaminField.offsetParent !== null) {
                jenisKelaminField.required = true;
            }

            const noIdentitasInput = form.querySelector(
                'input[name="no_identitas"]'
            );
            if (noIdentitasInput) noIdentitasInput.required = true;

            const jenisIdentitasInput = form.querySelector(
                'select[name="jns_identitas"]'
            );
            if (jenisIdentitasInput) jenisIdentitasInput.required = true;

            const pekerjaanInput = form.querySelector(
                'select[name="pekerjaan"]'
            );
            if (pekerjaanInput) pekerjaanInput.required = true;

            const alamatInput = form.querySelector('textarea[name="alamat"]');
            if (alamatInput) alamatInput.required = true;

            // Set field badan usaha tidak required
            const badanUsahaInputs = badanUsahaFields
                ? badanUsahaFields.querySelectorAll("input, select, textarea")
                : [];
            badanUsahaInputs.forEach((input) => {
                input.required = false;
            });

            const namaBadanUsahaInput = form.querySelector(
                'input[name="nama_badan_usaha"]'
            );
            if (namaBadanUsahaInput) namaBadanUsahaInput.required = false;

            // Set field perorangan yang disembunyikan tidak required
            const jenisKelaminFieldHidden = form.querySelector(
                'select[name="jns_kelamin"]'
            );
            if (
                jenisKelaminFieldHidden &&
                jenisKelaminFieldHidden.offsetParent === null
            ) {
                jenisKelaminFieldHidden.required = false;
            }
        }

        // Update label modal berdasarkan jenis entitas
        const modalTitle = document.querySelector("#modalTambahDataLabel");
        if (modalTitle) {
            if (jenisEntitas === "badan_usaha") {
                modalTitle.innerHTML =
                    '<i class="bi bi-archive me-2"></i>Tambah Data Badan Usaha';
            } else {
                modalTitle.innerHTML =
                    '<i class="bi bi-archive me-2"></i>Tambah Data Perorangan';
            }
        }
    }

    // Buka modal form setelah delay singkat
    setTimeout(() => {
        const modalTambahData = new bootstrap.Modal(
            document.getElementById("modalTambahData")
        );
        modalTambahData.show();
    }, 300);
}

// Toggle & bulk delete for Register
(function () {
    function init() {
        var btn = document.getElementById("btnBulkRegister");
        if (!btn) return;
        var icon = '<i class="bi bi-trash me-1"></i>';
        var label = "Hapus";

        function count() {
            var n = 0;
            document
                .querySelectorAll('tbody input[type="checkbox"]')
                .forEach(function (b) {
                    if (b.checked) n++;
                });
            btn.disabled = n === 0;
            btn.innerHTML = icon + label + (n > 0 ? " (" + n + ")" : "");
        }

        document.addEventListener("change", function (e) {
            if (e.target && e.target.matches('tbody input[type="checkbox"]'))
                count();
        });

        count();

        btn.addEventListener("click", async function (e) {
            if (e) e.preventDefault();
            if (btn.disabled) return;

            var ids = [];
            document
                .querySelectorAll('tbody input[type="checkbox"]:checked')
                .forEach(function (b) {
                    if (b.value) ids.push(b.value);
                });

            if (!ids.length) return;
            if (!confirm("Hapus " + ids.length + " data Register terpilih?"))
                return;

            try {
                const token = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");
                for (const id of ids) {
                    const body = new URLSearchParams();
                    body.append("_method", "DELETE");
                    await fetch(`/register/${encodeURIComponent(id)}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": token || "",
                            "X-Requested-With": "XMLHttpRequest",
                            Accept: "text/html,application/json",
                            "Content-Type":
                                "application/x-www-form-urlencoded; charset=UTF-8",
                        },
                        body: body.toString(),
                    });
                }
                window.location.href = window.location.href;
            } catch (err) {
                console.error("Register bulk delete failed", err);
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();

// Initialize success notification auto-hide
function initSuccessNotification() {
    // This will be called from the blade template if session success exists
    setTimeout(function () {
        const notification = document.querySelector(".alert.position-fixed");
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Real-time Search Functionality
document.addEventListener("DOMContentLoaded", function () {
    const searchRegister = document.getElementById("searchRegister");
    const tableBody = document.querySelector("table tbody");
    const allRows = Array.from(tableBody.querySelectorAll("tr"));

    if (searchRegister) {
        let searchTimeout;

        // Auto uppercase and real-time search
        searchRegister.addEventListener("input", function () {
            // Convert to uppercase
            const currentCursor = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(currentCursor, currentCursor);

            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase().trim();

            // Show loading state
            this.style.background = "#f8f9fa";

            searchTimeout = setTimeout(() => {
                // Remove loading state
                this.style.background = "";

                // Filter rows based on search term
                filterTableRows(searchTerm);

                // Update results counter
                updateResultsCounter();
            }, 200); // 200ms delay for better UX
        });

        // Handle keypress to ensure uppercase
        searchRegister.addEventListener("keypress", function (e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });

        // Clear search functionality
        searchRegister.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                this.value = "";
                filterTableRows("");
                updateResultsCounter();
            }
        });
    }

    // Auto caps for filter_nama
    const filterNama = document.getElementById("filter_nama");
    if (filterNama) {
        filterNama.addEventListener("input", function () {
            const currentCursor = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(currentCursor, currentCursor);
        });

        filterNama.addEventListener("keypress", function (e) {
            setTimeout(() => {
                const currentCursor = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(currentCursor, currentCursor);
            }, 0);
        });
    }

    function filterTableRows(searchTerm) {
        let visibleCount = 0;

        allRows.forEach((row, index) => {
            // Skip empty row or header row
            if (row.querySelector("td[colspan]") || row.querySelector("th")) {
                return;
            }

            const rowData = row.textContent.toLowerCase();
            const shouldShow =
                searchTerm === "" || rowData.includes(searchTerm);

            if (shouldShow) {
                row.style.display = "";
                visibleCount++;

                // Add highlight effect for matching text
                if (searchTerm !== "") {
                    row.style.backgroundColor = "#f8f9fa";
                    setTimeout(() => {
                        row.style.backgroundColor = "";
                    }, 300);
                }
            } else {
                row.style.display = "none";
            }
        });

        // Show "no results" message if no rows match
        showNoResultsMessage(visibleCount === 0 && searchTerm !== "");
    }

    function updateResultsCounter() {
        const visibleRows = allRows.filter(
            (row) =>
                !row.querySelector("td[colspan]") &&
                !row.querySelector("th") &&
                row.style.display !== "none"
        );

        // You can add a counter display here if needed
        console.log(
            `Showing ${visibleRows.length} of ${allRows.length} entries`
        );
    }

    function showNoResultsMessage(show) {
        let noResultsRow = document.getElementById("no-results-row");

        if (show && !noResultsRow) {
            noResultsRow = document.createElement("tr");
            noResultsRow.id = "no-results-row";
            noResultsRow.innerHTML = `
                <td colspan="100%" class="text-center py-4" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                    <i class="bi bi-search me-2"></i>
                    <strong>Tidak ada data yang ditemukan</strong>
                    <br>
                    <small class="text-muted">Coba gunakan kata kunci lain</small>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        } else if (!show && noResultsRow) {
            noResultsRow.remove();
        }
    }

    // Pagination function
    window.changePerPage = function (perPage) {
        console.log("changePerPage called with:", perPage);
        const url = new URL(window.location);
        url.searchParams.set("per_page", perPage);
        url.searchParams.delete("page"); // Reset to first page
        console.log("Redirecting to:", url.toString());
        window.location.href = url.toString();
    };
});
