<link rel="stylesheet" href="{{ asset('assets/css/pengguna_style.css') }}">
<div class="modal fade" id="modalSetting_{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <!-- Header -->
            @php
                $headerInitial = strtoupper(substr($user->name ?? $user->username ?? $user->email ?? 'N', 0, 1));
            @endphp
            <div class="modal-header border-0" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
                <div class="d-flex align-items-center w-100">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 48px; height: 48px; font-size: 1.5rem; font-weight: bold; background: linear-gradient(135deg, #36d1c4 0%, #5b86e5 100%); color: #fff;">
                        {{ $headerInitial }}
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-1 fw-bold">Pengaturan Pengguna</h5>
                        <small class="text-white-50">Atur hak akses dan otorisasi menu</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @php 
                $isSuperAdmin = ($user->username === 'SUPERADMIN');
                
                // Ambil roles dan authorized_menus menggunakan helper methods
                $userRoles = $user->getRolesArray();
                $authorizedMenus = $user->getAuthorizedMenusArray();
                
                // Set default values jika null atau empty
                if (empty($userRoles)) {
                    $userRoles = [
                        'petugas_register' => false,
                        'petugas_slik' => false,
                        'petugas_data' => false,
                        'petugas_komite' => false,
                    ];
                }
                
                if (empty($authorizedMenus)) {
                    $authorizedMenus = ['menu_register']; // Register selalu wajib
                }
            @endphp
            @if($isSuperAdmin)
                <div class="alert alert-info mb-3">
                    Pengaturan SUPERADMIN tidak dapat diubah. Semua akses selalu aktif.
                </div>
            @endif
            <form method="POST" action="{{ route('users.settings', $user->id) }}">
                @csrf
                <div class="modal-body p-4">
                    <!-- User Info -->
                    @php
                        $displayName = $user->name ?? $user->username ?? $user->email ?? 'Nama Tidak Tersedia';
                        $displayInitial = strtoupper(substr($user->name ?? $user->username ?? $user->email ?? 'N', 0, 1));
                        $displayEmail = $user->email ?? '-';
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex align-items-center p-3 rounded-3" style="background: #f5f6fa;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px; font-size: 2rem; font-weight: bold; background: linear-gradient(135deg, #36d1c4 0%, #5b86e5 100%); color: #fff;">
                                {{ $displayInitial }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 1.2rem;">{{ $displayName }}</div>
                                <div class="text-muted" style="font-size: 0.95rem;">{{ $displayEmail }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- END User Info -->
                    <div class="row g-4">
                        <!-- Role Assignment -->
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold text-primary mb-3">Penugasan Akses</h6>
                                <div class="d-flex flex-column gap-3">
                                    <!-- Akses Register -->
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%);">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3 bg-white" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-person-plus text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold text-white">Akses Register</h6>
                                                <small class="text-white-50">User dapat melakukan penambahan data</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="petugas_register" id="register_{{ $user->id }}" value="1" style="width: 3rem; height: 1.5rem;" {{ ($isSuperAdmin || ($userRoles['petugas_register'] ?? false)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <!-- Akses SLIK -->
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%);">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3 bg-white" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-file-earmark-text text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold text-white">Akses SLIK</h6>
                                                <small class="text-white-50">User dapat melakukan upload data SLIK</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="petugas_slik" id="slik_{{ $user->id }}" value="1" style="width: 3rem; height: 1.5rem;" {{ ($isSuperAdmin || ($userRoles['petugas_slik'] ?? false)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <!-- Akses Data -->
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%);">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3 bg-white" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-database text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold text-white">Akses Data</h6>
                                                <small class="text-white-50">User dapat melihat semua data</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="petugas_data" id="data_{{ $user->id }}" value="1" style="width: 3rem; height: 1.5rem;" {{ ($isSuperAdmin || ($userRoles['petugas_data'] ?? false)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <!-- Akses Admin -->
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: linear-gradient(135deg, #1dd1a1 0%, #49bbca 100%);">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3 bg-white" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-shield-check text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold text-white">Akses Admin</h6>
                                                <small class="text-white-50">User dapat mengelola sistem</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="petugas_komite" id="komite_{{ $user->id }}" value="1" style="width: 3rem; height: 1.5rem;" {{ ($isSuperAdmin || ($userRoles['petugas_komite'] ?? false)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Menu Authorization -->
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold text-primary mb-3">Otorisasi Menu</h6>
                                <div class="d-flex flex-column gap-2">
                                    <!-- Fitur Utama -->
                                    <div class="mt-3 mb-2">
                                        <h6 class="fw-semibold text-secondary mb-2">
                                            <i class="bi bi-star me-1"></i>Fitur Utama
                                        </h6>
                                    </div>
                                    
                                    <!-- Data -->
                                    <div class="d-flex align-items-center p-3 rounded-3 border bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="menu_data" id="menu_data_{{ $user->id }}" value="1" {{ ($isSuperAdmin || in_array('menu_data', $authorizedMenus)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="menu_data_{{ $user->id }}">
                                                <i class="bi bi-database me-2 text-success"></i>Data
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Bank -->
                                    <div class="d-flex align-items-center p-3 rounded-3 border bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="menu_bank" id="menu_bank_{{ $user->id }}" value="1" {{ ($isSuperAdmin || in_array('menu_bank', $authorizedMenus)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="menu_bank_{{ $user->id }}">
                                                <i class="bi bi-bank me-2 text-info"></i>Bank
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- SLIK -->
                                    <div class="d-flex align-items-center p-3 rounded-3 border bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="menu_slik" id="menu_slik_{{ $user->id }}" value="1" {{ ($isSuperAdmin || in_array('menu_slik', $authorizedMenus)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="menu_slik_{{ $user->id }}">
                                                <i class="bi bi-file-earmark-text me-2 text-warning"></i>SLIK
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Komite -->
                                    <div class="d-flex align-items-center p-3 rounded-3 border bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="menu_komite" id="menu_komite_{{ $user->id }}" value="1" {{ ($isSuperAdmin || in_array('menu_komite', $authorizedMenus)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="menu_komite_{{ $user->id }}">
                                                <i class="bi bi-diagram-3 me-2 text-secondary"></i>Komite
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Pemisah -->
                                    <hr class="my-3">
                                    
                                    <!-- Fitur Khusus -->
                                    <div class="mb-2">
                                        <h6 class="fw-semibold text-secondary mb-2">
                                            <i class="bi bi-gear me-1"></i>Fitur Khusus
                                        </h6>
                                    </div>
                                    
                                    <!-- Manajemen Pengguna -->
                                    <div class="d-flex align-items-center p-3 rounded-3 border bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="menu_manajemen" id="menu_manajemen_{{ $user->id }}" value="1" {{ ($isSuperAdmin || in_array('menu_manajemen', $authorizedMenus)) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="menu_manajemen_{{ $user->id }}">
                                                <i class="bi bi-people me-2 text-danger"></i>Manajemen Pengguna
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Aksi Cepat</h6>
                                        <small class="text-muted">Atur semua pengaturan sekaligus</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if(!\App\Helpers\RoleHelper::isAksesAdmin())
                                            <button type="button" class="btn btn-sm" onclick="forceCheckAll()" style="background: #1dd1a1; color: white; border: none; padding: 8px 16px;">
                                                <i class="bi bi-check-all me-1"></i>Pilih Semua
                                            </button>
                                            <button type="button" class="btn btn-sm" onclick="forceUncheckAll()" style="background: #49bbca; color: white; border: none; padding: 8px 16px;">
                                                <i class="bi bi-x-circle me-1"></i>Hapus Semua
                                            </button>
                                        @else
                                            <span class="text-muted small">Pengaturan read-only untuk Akses Admin</span>
                                        @endif
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex align-items-center">
                            <small class="text-muted">Perubahan akan disimpan setelah Anda menekan tombol Simpan</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn px-4 fw-semibold" style="background: #1dd1a1; color: white;">
                                <i class="bi bi-check-circle me-1"></i>Simpan Pengaturan
                            </button>
                            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript telah dipindahkan ke public/assets/js/manajemen_pengguna-setting.js -->
<!-- Script akan dimuat di halaman parent untuk optimasi caching -->
