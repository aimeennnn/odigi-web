// Register Modal JavaScript
// Form validation and interaction handlers

document.addEventListener("DOMContentLoaded", function () {
    // Fungsi untuk validasi Nomor Identitas
    function validateNomorIdentitas(value) {
        // Hanya angka 0-9, minimal 14 digit, maksimal 16 digit
        const numericRegex = /^[0-9]+$/;
        return (
            numericRegex.test(value) && value.length >= 14 && value.length <= 16
        );
    }

    // Fungsi untuk validasi Jangka Waktu
    function validateJangkaWaktu(value) {
        // Hanya angka 0-9, minimal 1 digit, tanpa batasan maksimal
        const numericRegex = /^[0-9]+$/;
        return numericRegex.test(value) && value.length >= 1;
    }

    // Fungsi untuk mengupdate icon berdasarkan nilai field (global scope)
    window.updateFieldIcon = function (field) {
        const warningIcon = field.nextElementSibling;
        let isValid = false;

        // Validasi khusus untuk Nomor Identitas dan Nomor Legalitas Usaha
        if (
            field.id === "no_identitas" ||
            field.id === "nomor_legalitas_usaha"
        ) {
            isValid = validateNomorIdentitas(field.value.trim());
        }
        // Validasi khusus untuk Jangka Waktu
        else if (field.id === "jw_pengajuan") {
            // Ambil hanya digit agar nilai seperti "12 BULAN" tetap tervalidasi
            const digitsOnly = field.value.replace(/[^\d]/g, "");
            isValid = validateJangkaWaktu(digitsOnly);
        } else {
            // Validasi umum untuk field lainnya
            isValid = field.value.trim() !== "";
        }

        if (isValid) {
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
                // Set tanggal hari ini jika field kosong
                const tglPengajuanInput =
                    document.getElementById("tgl_pengajuan");
                if (tglPengajuanInput && !tglPengajuanInput.value) {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, "0");
                    const day = String(today.getDate()).padStart(2, "0");
                    tglPengajuanInput.value = `${year}-${month}-${day}`;
                }

                const inputs = form.querySelectorAll(
                    'input[type="text"], input[type="date"], select, textarea'
                );
                inputs.forEach((input) => {
                    window.updateFieldIcon(input);
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
                                'input[readonly], select, input[type="date"]'
                            );
                            if (
                                input &&
                                input.value &&
                                window.updateFieldIcon
                            ) {
                                window.updateFieldIcon(input);
                            }
                        }
                    });
                }, 100);
            });
        }

        form.addEventListener("submit", function (e) {
            console.log("Form submit event triggered");

            // Pastikan format jangka waktu benar
            const jwInput = form.querySelector('input[name="jw_pengajuan"]');
            if (jwInput && jwInput.value) {
                let val = jwInput.value.replace(/[^\d]/g, "");
                if (val && validateJangkaWaktu(val)) {
                    jwInput.value = val + " BULAN";
                }
            }

            // Ambil jenis entitas lebih dulu untuk memprioritaskan validasi perorangan
            const jenisEntitas = document.getElementById("jenis_entitas_input").value;

            // Prioritaskan validasi Jenis Kelamin untuk PERORANGAN agar tooltip muncul di dropdown itu dulu
            if (jenisEntitas === "perorangan") {
                const jnsKelaminEarly = form.querySelector('select[name="jns_kelamin"]');
                if (jnsKelaminEarly && jnsKelaminEarly.offsetParent !== null) {
                    if (!jnsKelaminEarly.hasAttribute('required')) jnsKelaminEarly.setAttribute('required', 'required');
                    if (jnsKelaminEarly.value.trim() === "") {
                        e.preventDefault();
                        try { jnsKelaminEarly.setCustomValidity('Mohon pilih jenis kelamin.'); } catch (e1) {}
                        try { jnsKelaminEarly.reportValidity(); } catch (e2) {}
                        try { jnsKelaminEarly.focus(); } catch (e3) {}
                        return false;
                    } else {
                        try { jnsKelaminEarly.setCustomValidity(''); } catch (eClear) {}
                    }
                }
            }

            // Jalankan validasi HTML5 bawaan untuk field lain
            if (!form.checkValidity()) {
                e.preventDefault();
                try { form.reportValidity(); } catch (err) {}
                return false;
            }

            // Pastikan tombol submit tidak dalam keadaan disabled dan tidak double-submit
            const btn = document.getElementById("btnSimpanRegister");
            if (btn) {
                btn.disabled = true;
                btn.style.pointerEvents = "none";
                setTimeout(() => { try { btn.disabled = false; btn.style.pointerEvents = "auto"; } catch (_) {} }, 2000);
            }

            // Validasi form untuk badan usaha
            console.log("Jenis entitas:", jenisEntitas);

            if (jenisEntitas === "badan_usaha") {
                // Validasi field badan usaha
                const namaBadanUsaha = form.querySelector(
                    'input[name="nama_badan_usaha"]'
                );
                const jenisDokumenUsaha = form.querySelector(
                    'select[name="jenis_dokumen_usaha"]'
                );
                const nomorLegalitasUsaha = form.querySelector(
                    'input[name="nomor_legalitas_usaha"]'
                );
                const bidangUsaha = form.querySelector(
                    'select[name="bidang_usaha"]'
                );
                const alamatUsaha = form.querySelector(
                    'textarea[name="alamat_usaha"]'
                );
                // Field sisi kanan yang wajib untuk semua entitas
                const jnsPengajuan = form.querySelector('select[name="jns_pengajuan"]');
                const nominalPengajuan = form.querySelector('input[name="nominal_pengajuan"]');
                const jwPengajuan = form.querySelector('input[name="jw_pengajuan"]');
                const jaminan = form.querySelector('select[name="jaminan"]');

                let isValid = true;
                let errorMessage = "";

                if (!namaBadanUsaha || namaBadanUsaha.value.trim() === "") {
                    isValid = false;
                    errorMessage = "Mohon isi nama badan usaha!";
                } else if (
                    !jenisDokumenUsaha ||
                    jenisDokumenUsaha.value.trim() === ""
                ) {
                    isValid = false;
                    errorMessage = "Mohon pilih jenis dokumen usaha!";
                } else if (
                    !nomorLegalitasUsaha ||
                    !validateNomorIdentitas(nomorLegalitasUsaha.value.trim())
                ) {
                    isValid = false;
                    errorMessage =
                        "Mohon isi nomor legalitas usaha dengan 14-16 digit angka!";
                } else if (!bidangUsaha || bidangUsaha.value.trim() === "") {
                    isValid = false;
                    errorMessage = "Mohon pilih bidang usaha!";
                } else if (!alamatUsaha || alamatUsaha.value.trim() === "") {
                    isValid = false;
                    errorMessage = "Mohon isi alamat usaha!";
                }

                if (!isValid) {
                    e.preventDefault();
                    console.log("Form validation failed:", errorMessage);
                    alert(errorMessage);
                    return false;
                }

                // Validasi tambahan: jenis pengajuan s.d. jaminan (runtut)
                // Jenis Pengajuan
                if (!jnsPengajuan || jnsPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon && jnsPengajuan) window.updateFieldIcon(jnsPengajuan);
                    alert("Mohon pilih jenis pengajuan!");
                    return false;
                }
                // Nominal Pengajuan > 0
                if (!nominalPengajuan || nominalPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon && nominalPengajuan) window.updateFieldIcon(nominalPengajuan);
                    alert("Mohon isi nominal pengajuan!");
                    return false;
                } else {
                    const nominalClean = nominalPengajuan.value.replace(/\./g, "").replace(/,/g, "").replace(/\D/g, "");
                    if (!nominalClean || parseInt(nominalClean, 10) <= 0) {
                        e.preventDefault();
                        if (window.updateFieldIcon) window.updateFieldIcon(nominalPengajuan);
                        alert("Nominal pengajuan harus lebih dari 0!");
                        return false;
                    }
                }
                // Jangka Waktu angka saja
                if (!jwPengajuan || jwPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon && jwPengajuan) window.updateFieldIcon(jwPengajuan);
                    alert("Mohon isi jangka waktu pengajuan!");
                    return false;
                } else {
                    const jwDigits = jwPengajuan.value.replace(/[^\d]/g, "");
                    if (!validateJangkaWaktu(jwDigits)) {
                        e.preventDefault();
                        if (window.updateFieldIcon) window.updateFieldIcon(jwPengajuan);
                        alert("Jangka waktu harus berupa angka yang valid!");
                        return false;
                    } else {
                        // Format as display value before submit
                        jwPengajuan.value = jwDigits + " BULAN";
                    }
                }
                // Jaminan
                if (!jaminan || jaminan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon && jaminan) window.updateFieldIcon(jaminan);
                    alert("Mohon pilih jenis jaminan!");
                    return false;
                }
            } else {
                // Validasi PERORANGAN (minimal: nama, jenis kelamin, no identitas, jenis identitas, pekerjaan, alamat)
                const namaPerorangan = form.querySelector('input[name="nama"]');
                const jnsKelamin = form.querySelector('select[name="jns_kelamin"]');
                const noIdentitas = form.querySelector('input[name="no_identitas"]');
                const jnsIdentitas = form.querySelector('select[name="jns_identitas"]');
                const pekerjaan = form.querySelector('select[name="pekerjaan"]');
                const alamat = form.querySelector('textarea[name="alamat"]');

                // Urutan peringatan seperti field lain
                if (!namaPerorangan || namaPerorangan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(namaPerorangan);
                    try {
                        if (namaPerorangan && typeof namaPerorangan.reportValidity === 'function') namaPerorangan.reportValidity();
                        // Fokus ke nama terlebih dulu lalu segera arahkan fokus ke jenis kelamin agar alur lanjut ke dropdown berikutnya
                        const jnsKelaminEl = jnsKelamin;
                        setTimeout(() => {
                            if (jnsKelaminEl && typeof jnsKelaminEl.focus === 'function') jnsKelaminEl.focus();
                        }, 0);
                    } catch (e2) {}
                    alert("Mohon isi nama lengkap!");
                    return false;
                }
                if (!jnsKelamin || jnsKelamin.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(jnsKelamin);
                    try {
                        if (jnsKelamin) {
                            if (!jnsKelamin.hasAttribute('required')) jnsKelamin.setAttribute('required', 'required');
                            // Force this field to be the first invalid by setting custom validity message
                            if (typeof jnsKelamin.setCustomValidity === 'function') jnsKelamin.setCustomValidity('Mohon pilih jenis kelamin.');
                            if (typeof jnsKelamin.reportValidity === 'function') jnsKelamin.reportValidity();
                            if (typeof jnsKelamin.focus === 'function') jnsKelamin.focus();
                        }
                    } catch (e2) {}
                    alert("Mohon pilih jenis kelamin!");
                    return false;
                }
                // Clear custom validity if already set
                try {
                    if (jnsKelamin && typeof jnsKelamin.setCustomValidity === 'function') jnsKelamin.setCustomValidity('');
                } catch (eClear) {}
                if (!noIdentitas || !validateNomorIdentitas(noIdentitas.value.trim())) {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(noIdentitas);
                    // Pastikan jenis kelamin sudah disorot dulu jika masih kosong
                    if (jnsKelamin && jnsKelamin.value.trim() === "") {
                        try {
                            if (!jnsKelamin.hasAttribute('required')) jnsKelamin.setAttribute('required', 'required');
                            if (typeof jnsKelamin.reportValidity === 'function') jnsKelamin.reportValidity();
                            if (typeof jnsKelamin.focus === 'function') jnsKelamin.focus();
                        } catch (e3) {}
                        alert("Mohon pilih jenis kelamin!");
                        return false;
                    }
                    alert("Mohon isi nomor identitas 14-16 digit angka!");
                    return false;
                }
                if (!jnsIdentitas || jnsIdentitas.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(jnsIdentitas);
                    alert("Mohon pilih jenis identitas!");
                    return false;
                }
                if (!pekerjaan || pekerjaan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(pekerjaan);
                    alert("Mohon pilih kelompok pekerjaan!");
                    return false;
                }
                if (!alamat || alamat.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(alamat);
                    alert("Mohon isi alamat lengkap!");
                    return false;
                }

                // Validasi sisi kanan (wajib untuk semua entitas): jenis pengajuan s.d. jaminan
                const jnsPengajuan = form.querySelector('select[name="jns_pengajuan"]');
                const nominalPengajuan = form.querySelector('input[name="nominal_pengajuan"]');
                const jwPengajuan = form.querySelector('input[name="jw_pengajuan"]');
                const jaminan = form.querySelector('select[name="jaminan"]');

                if (!jnsPengajuan || jnsPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(jnsPengajuan);
                    alert("Mohon pilih jenis pengajuan!");
                    return false;
                }
                if (!nominalPengajuan || nominalPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(nominalPengajuan);
                    alert("Mohon isi nominal pengajuan!");
                    return false;
                } else {
                    const nominalClean = nominalPengajuan.value.replace(/\./g, "").replace(/,/g, "").replace(/\D/g, "");
                    if (!nominalClean || parseInt(nominalClean, 10) <= 0) {
                        e.preventDefault();
                        if (window.updateFieldIcon) window.updateFieldIcon(nominalPengajuan);
                        alert("Nominal pengajuan harus lebih dari 0!");
                        return false;
                    }
                }
                if (!jwPengajuan || jwPengajuan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(jwPengajuan);
                    alert("Mohon isi jangka waktu pengajuan!");
                    return false;
                } else {
                    const jwDigits = jwPengajuan.value.replace(/[^\d]/g, "");
                    if (!validateJangkaWaktu(jwDigits)) {
                        e.preventDefault();
                        if (window.updateFieldIcon) window.updateFieldIcon(jwPengajuan);
                        alert("Jangka waktu harus berupa angka yang valid!");
                        return false;
                    } else {
                        jwPengajuan.value = jwDigits + " BULAN";
                    }
                }
                if (!jaminan || jaminan.value.trim() === "") {
                    e.preventDefault();
                    if (window.updateFieldIcon) window.updateFieldIcon(jaminan);
                    alert("Mohon pilih jenis jaminan!");
                    return false;
                }
            }

            console.log("Form validation passed, submitting...");

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
            window.updateFieldIcon(e.target);
        });

        // Tambahkan event listener untuk select dropdown dan date input
        form.addEventListener("change", function (e) {
            if (e.target.tagName === "SELECT" || e.target.type === "date") {
                // Bersihkan pesan custom validity saat user mengubah nilai
                if (e.target && e.target.name === "jns_kelamin") {
                    try { e.target.setCustomValidity(""); } catch (err) {}
                }
                window.updateFieldIcon(e.target);
            }
        });
    }

    // Validasi khusus untuk Nomor Identitas - hanya angka
    const noIdentitasInput = document.querySelector(
        '#modalTambahData input[name="no_identitas"]'
    );
    if (noIdentitasInput) {
        noIdentitasInput.addEventListener("input", function (e) {
            // Hanya izinkan angka 0-9
            this.value = this.value.replace(/[^0-9]/g, "");
            // Validasi real-time
            window.updateFieldIcon(this);
        });

        // Validasi saat blur (kehilangan fokus)
        noIdentitasInput.addEventListener("blur", function () {
            window.updateFieldIcon(this);
        });
    }

    // Validasi khusus untuk Nomor Legalitas Usaha - hanya angka
    const nomorLegalitasInput = document.querySelector(
        '#modalTambahData input[name="nomor_legalitas_usaha"]'
    );
    if (nomorLegalitasInput) {
        nomorLegalitasInput.addEventListener("input", function (e) {
            // Hanya izinkan angka 0-9
            this.value = this.value.replace(/[^0-9]/g, "");
            // Validasi real-time
            window.updateFieldIcon(this);
        });

        // Validasi saat blur (kehilangan fokus)
        nomorLegalitasInput.addEventListener("blur", function () {
            window.updateFieldIcon(this);
        });
    }

    // Validasi khusus untuk Jangka Waktu - hanya angka tanpa batasan digit
    const jwPengajuanInput = document.querySelector(
        '#modalTambahData input[name="jw_pengajuan"]'
    );
    if (jwPengajuanInput) {
        jwPengajuanInput.addEventListener("input", function (e) {
            // Hanya izinkan angka 0-9
            this.value = this.value.replace(/[^0-9]/g, "");
            // Validasi real-time
            window.updateFieldIcon(this);
        });

        // Validasi saat blur (kehilangan fokus)
        jwPengajuanInput.addEventListener("blur", function () {
            window.updateFieldIcon(this);
        });
    }

    // Auto uppercase
    document
        .querySelectorAll(
            '#modalTambahData input[type="text"], #modalTambahData textarea'
        )
        .forEach(function (el) {
            if (
                el.name !== "nominal_pengajuan" &&
                el.name !== "no_identitas" &&
                el.name !== "nomor_legalitas_usaha" &&
                el.name !== "jw_pengajuan"
            ) {
                el.addEventListener("input", function () {
                    this.value = this.value.toUpperCase();
                });
            }
        });

    // Format nominal_pengajuan
    var nominalInput = document.querySelector(
        '#modalTambahData input[name="nominal_pengajuan"]'
    );
    if (nominalInput) {
        nominalInput.addEventListener("input", function (e) {
            let val = this.value.replace(/\D/g, "");
            if (val) {
                this.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                this.value = "";
            }
        });

        // Saat submit, hapus titik
        const form = nominalInput.closest("form");
        if (form) {
            form.addEventListener("submit", function () {
                nominalInput.value = nominalInput.value.replace(/\./g, "");
            });
        }
    }

    // Format jangka waktu: otomatis tambah ' BULAN' di akhir (hanya untuk display, validasi tetap angka saja)
    var jwInput = document.querySelector(
        '#modalTambahData input[name="jw_pengajuan"]'
    );
    if (jwInput) {
        // Event listener untuk format display (tambahkan BULAN setelah validasi)
        jwInput.addEventListener("blur", function () {
            let val = this.value.replace(/[^\d]/g, "");
            if (val && validateJangkaWaktu(val)) {
                this.value = val + " BULAN";
            } else if (val) {
                // Jika tidak valid, tetap tampilkan angka saja
                this.value = val;
            }
        });

        // Pastikan format saat form submit
        jwInput.addEventListener("change", function () {
            if (this.value && !this.value.includes("BULAN")) {
                let val = this.value.replace(/[^\d]/g, "");
                if (val && validateJangkaWaktu(val)) {
                    this.value = val + " BULAN";
                }
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
                if (input && input.value && window.updateFieldIcon) {
                    window.updateFieldIcon(input);
                }
            }
        });
    }, 200);

    // Event listener khusus untuk tombol submit
    const submitBtn = document.getElementById("btnSimpanRegister");
    if (submitBtn) {
        submitBtn.addEventListener("click", function (e) {
            console.log("Submit button clicked");
            console.log("Button disabled:", this.disabled);

            // Pastikan tombol tidak disabled
            if (this.disabled) {
                e.preventDefault();
                console.log("Button is disabled");
                return false;
            }

            // Biarkan form submit secara normal
            console.log("Allowing form to submit normally");
        });

        // Pastikan tombol tidak disabled saat modal dibuka
        const modal = document.getElementById("modalTambahData");
        if (modal) {
            modal.addEventListener("shown.bs.modal", function () {
                submitBtn.disabled = false;
                submitBtn.style.pointerEvents = "auto";
                submitBtn.style.opacity = "1";
                console.log("Modal opened, submit button enabled");
            });
        }
    }

    // Fallback: hanya pastikan tombol aktif, tanpa bypass submit handler
    setTimeout(function () {
        const form = document.querySelector("#modalTambahData form");
        const submitBtn = document.getElementById("btnSimpanRegister");
        if (form && submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.pointerEvents = "auto";
            submitBtn.style.opacity = "1";
            console.log(
                "Form and submit button initialized with alternative method"
            );
        }
    }, 1000);
});
