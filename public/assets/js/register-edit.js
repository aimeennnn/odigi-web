// Register Edit JavaScript
// Form field management and validation for edit register form

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

// Fungsi validasi nomor identitas
function validateNomorIdentitas(value) {
    // Hanya angka 0-9, minimal 14 digit, maksimal 16 digit
    const numericRegex = /^[0-9]+$/;
    return numericRegex.test(value) && value.length >= 14 && value.length <= 16;
}

// Fungsi validasi jangka waktu
function validateJangkaWaktu(value) {
    // Hanya angka 0-9, minimal 1 digit, tanpa batasan maksimal
    const numericRegex = /^[0-9]+$/;
    return numericRegex.test(value) && value.length >= 1;
}

// Fungsi validasi universal untuk semua field
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
        case "nama_badan_usaha":
            isValid = value.length >= 2;
            break;
        case "jenis_entitas":
            isValid = value === "perorangan" || value === "badan_usaha";
            break;
        case "jns_kelamin":
            isValid = value === "laki_laki" || value === "perempuan";
            break;
        case "no_identitas":
            isValid = validateNomorIdentitas(value);
            break;
        case "nomor_legalitas_usaha":
            isValid = validateNomorIdentitas(value);
            break;
        case "jns_identitas":
            isValid = ["ktp", "sim", "paspor", "lainnya"].includes(value);
            break;
        case "jenis_dokumen_usaha":
            isValid = ["SIUP", "TDP", "NPWP", "AKTA", "SK", "LAINNYA"].includes(
                value
            );
            break;
        case "bidang_usaha":
            isValid = [
                "Perdagangan",
                "Jasa",
                "Manufaktur",
                "Konstruksi",
                "Pertanian",
                "Perikanan",
                "Pertambangan",
                "Teknologi",
                "Kesehatan",
                "Pendidikan",
                "LAINNYA",
            ].includes(value);
            break;
        case "alamat_usaha":
            isValid = value.length >= 10;
            break;
        case "pekerjaan":
            isValid = [
                "pns_asn",
                "tni_polri",
                "swasta",
                "wirausaha",
                "petani",
                "nelayan",
                "buruh",
                "guru",
                "medis",
                "pelajar",
                "irt",
                "pensiunan",
                "sopir",
                "pedagang",
                "lainnya",
            ].includes(value);
            break;
        case "alamat":
            isValid = value.length >= 10;
            break;
        case "tgl_pengajuan":
            isValid = value !== "";
            break;
        case "jns_pengajuan":
            isValid = ["1", "2", "3"].includes(value);
            break;
        case "nominal_pengajuan":
            const numericValue = value.replace(/[^\d]/g, "");
            isValid = numericValue !== "" && parseInt(numericValue) > 0;
            break;
        case "jw_pengajuan":
            isValid = validateJangkaWaktu(value.replace(/[^\d]/g, ""));
            break;
        case "jaminan":
            isValid = [
                "tanah",
                "bangunan",
                "kendaraan",
                "bpkb",
                "deposito",
                "tanpa_jaminan",
                "lainnya",
            ].includes(value);
            break;
        case "status":
            isValid = value === "1";
            break;
        default:
            isValid = value !== "";
    }

    updateValidationIcon(inputElement, isValid, isEmpty);
    return isValid;
}

// Fungsi untuk toggle field berdasarkan jenis entitas
function toggleJenisKelamin(jenisEntitas) {
    const jenisKelaminContainer = document.getElementById(
        "jenis-kelamin-container"
    );
    const namaPeroranganContainer = document.getElementById(
        "nama-perorangan-container"
    );
    const namaBadanUsahaContainer = document.getElementById(
        "nama-badan-usaha-container"
    );
    const badanUsahaFields = document.getElementById("badan-usaha-fields");
    const peroranganFields = document.getElementById("perorangan-fields");
    const noIdentitasContainer = document.getElementById(
        "no-identitas-container"
    );
    const jenisIdentitasContainer = document.getElementById(
        "jenis-identitas-container"
    );

    if (jenisEntitas === "badan_usaha") {
        // Sembunyikan field perorangan
        if (peroranganFields) peroranganFields.style.display = "none";
        if (jenisKelaminContainer) jenisKelaminContainer.style.display = "none";
        if (namaPeroranganContainer)
            namaPeroranganContainer.style.display = "none";
        if (noIdentitasContainer) noIdentitasContainer.style.display = "none";
        if (jenisIdentitasContainer)
            jenisIdentitasContainer.style.display = "none";

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
        const namaBadanUsahaInput = document.querySelector(
            'input[name="nama_badan_usaha"]'
        );
        if (namaBadanUsahaInput) namaBadanUsahaInput.required = true;

        // Set nama perorangan tidak required
        const namaPeroranganInput =
            document.querySelector('input[name="nama"]');
        if (namaPeroranganInput) namaPeroranganInput.required = false;

        // Set field identitas tidak required untuk badan usaha
        const noIdentitasInput = document.querySelector(
            'input[name="no_identitas"]'
        );
        if (noIdentitasInput) noIdentitasInput.required = false;

        const jenisIdentitasInput = document.querySelector(
            'select[name="jns_identitas"]'
        );
        if (jenisIdentitasInput) jenisIdentitasInput.required = false;
    } else {
        // Tampilkan field perorangan
        if (peroranganFields) peroranganFields.style.display = "block";
        // Sembunyikan field badan usaha
        if (namaBadanUsahaContainer)
            namaBadanUsahaContainer.style.display = "none";
        if (badanUsahaFields) badanUsahaFields.style.display = "none";

        // Tampilkan field perorangan
        if (jenisKelaminContainer)
            jenisKelaminContainer.style.display = "block";
        if (namaPeroranganContainer)
            namaPeroranganContainer.style.display = "block";
        if (noIdentitasContainer) noIdentitasContainer.style.display = "block";
        if (jenisIdentitasContainer)
            jenisIdentitasContainer.style.display = "block";

        // Set required untuk field perorangan
        const namaPeroranganInput =
            document.querySelector('input[name="nama"]');
        if (namaPeroranganInput) namaPeroranganInput.required = true;

        const jenisKelaminField = document.querySelector(
            'select[name="jns_kelamin"]'
        );
        if (jenisKelaminField) jenisKelaminField.required = true;

        const noIdentitasInput = document.querySelector(
            'input[name="no_identitas"]'
        );
        if (noIdentitasInput) noIdentitasInput.required = true;

        const jenisIdentitasInput = document.querySelector(
            'select[name="jns_identitas"]'
        );
        if (jenisIdentitasInput) jenisIdentitasInput.required = true;

        // Set field badan usaha tidak required
        const badanUsahaInputs = badanUsahaFields
            ? badanUsahaFields.querySelectorAll("input, select, textarea")
            : [];
        badanUsahaInputs.forEach((input) => {
            input.required = false;
        });

        const namaBadanUsahaInput = document.querySelector(
            'input[name="nama_badan_usaha"]'
        );
        if (namaBadanUsahaInput) namaBadanUsahaInput.required = false;
    }

    // Validasi icon untuk semua field yang terpengaruh
    setTimeout(() => {
        const affectedInputs = document.querySelectorAll(
            'input[name="nama"], input[name="nama_badan_usaha"], select[name="jns_kelamin"], input[name="no_identitas"], select[name="jns_identitas"]'
        );
        affectedInputs.forEach((input) => {
            if (typeof validateField === "function") {
                validateField(input);
            }
        });
    }, 100);
}

document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi tampilan berdasarkan jenis entitas yang sudah ada
    const jenisEntitasSelect = document.querySelector(
        'select[name="jenis_entitas"]'
    );
    if (jenisEntitasSelect) {
        toggleJenisKelamin(jenisEntitasSelect.value);
        // Validasi icon untuk jenis_entitas
        validateField(jenisEntitasSelect);
    }

    // Auto uppercase
    document
        .querySelectorAll('input[type="text"], textarea')
        .forEach(function (el) {
            if (el.name !== "nominal_pengajuan") {
                el.addEventListener("input", function () {
                    this.value = this.value.toUpperCase();
                });
            }
        });

    // Format nominal_pengajuan dengan AutoNumeric
    var nominalInput = document.querySelector(
        'input[name="nominal_pengajuan"]'
    );
    if (nominalInput) {
        // Inisialisasi AutoNumeric
        window.nominalAutoNumeric = new AutoNumeric(nominalInput, {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            minimumValue: '0',
            modifyValueOnWheel: false,
            unformatOnSubmit: true // agar saat submit, value jadi angka tanpa titik
        });
    }

    // Format jangka waktu sudah diintegrasikan ke dalam setupRealTimeValidation()

    // Validasi nomor identitas dan nomor legalitas usaha - sama seperti form tambah

    // Validasi nomor identitas sudah diintegrasikan ke dalam setupRealTimeValidation()

    // Validasi nomor legalitas usaha sudah diintegrasikan ke dalam setupRealTimeValidation()

    // Validasi jangka waktu sudah diintegrasikan di atas

    // Inisialisasi validasi untuk semua field yang sudah ada nilainya
    function initializeValidation() {
        // Delay sedikit untuk memastikan DOM sudah selesai di-render
        setTimeout(() => {
            const allInputs = document.querySelectorAll(
                "input, select, textarea"
            );
            allInputs.forEach((input) => {
                if (input.name && input.name !== "nomor") {
                    // Skip nomor registrasi karena readonly
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
                if (
                    input.name !== "nominal_pengajuan" &&
                    input.name !== "jw_pengajuan" &&
                    input.name !== "no_identitas" &&
                    input.name !== "nomor_legalitas_usaha"
                ) {
                    input.addEventListener("input", function () {
                        validateField(this);
                    });
                    input.addEventListener("blur", function () {
                        validateField(this);
                    });
                }
            });

        // Validasi untuk select
        document.querySelectorAll("select").forEach((select) => {
            select.addEventListener("change", function () {
                validateField(this);
            });
        });

        // Validasi untuk input date
        document.querySelectorAll('input[type="date"]').forEach((input) => {
            input.addEventListener("change", function () {
                validateField(this);
            });
        });

        // Validasi khusus untuk nominal_pengajuan
        const nominalInput = document.querySelector(
            'input[name="nominal_pengajuan"]'
        );
        if (nominalInput) {
            nominalInput.addEventListener("input", function () {
                validateField(this);
            });
            nominalInput.addEventListener("blur", function () {
                validateField(this);
            });
        }

        // Validasi khusus untuk jw_pengajuan
        const jwInput = document.querySelector('input[name="jw_pengajuan"]');
        if (jwInput) {
            jwInput.addEventListener("input", function () {
                let val = this.value.replace(/[^\d]/g, "");
                if (val) {
                    this.value = val + " BULAN";
                } else {
                    this.value = "";
                }
                validateField(this);
            });
            jwInput.addEventListener("blur", function () {
                const numericValue = this.value.replace(/[^\d]/g, "");
                if (numericValue) {
                    this.value = numericValue + " BULAN";
                } else {
                    this.value = "";
                }
                validateField(this);
            });
        }

        // Validasi khusus untuk no_identitas
        const noIdentitasInput = document.querySelector(
            'input[name="no_identitas"]'
        );
        if (noIdentitasInput) {
            noIdentitasInput.addEventListener("input", function () {
                // Hanya izinkan angka
                this.value = this.value.replace(/[^0-9]/g, "");
                validateField(this);
            });
            noIdentitasInput.addEventListener("blur", function () {
                validateField(this);
            });
        }

        // Validasi khusus untuk nomor_legalitas_usaha
        const nomorLegalitasInput = document.querySelector(
            'input[name="nomor_legalitas_usaha"]'
        );
        if (nomorLegalitasInput) {
            nomorLegalitasInput.addEventListener("input", function () {
                // Hanya izinkan angka
                this.value = this.value.replace(/[^0-9]/g, "");
                validateField(this);
            });
            nomorLegalitasInput.addEventListener("blur", function () {
                validateField(this);
            });
        }
    }

    // Jalankan setup validasi terlebih dahulu
    setupRealTimeValidation();

    // Jalankan inisialisasi validasi setelah setup selesai
    initializeValidation();
});
