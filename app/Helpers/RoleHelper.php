<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Cek apakah user adalah SUPERADMIN
     */
    public static function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && $user->username === 'SUPERADMIN';
    }

    /**
     * Cek apakah user memiliki role tertentu
     */
    public static function hasRole($role)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $userRoles = $user->getRolesArray();
        return isset($userRoles[$role]) && $userRoles[$role];
    }

    /**
     * Cek apakah user adalah Akses Register
     */
    public static function isAksesRegister()
    {
        return self::hasRole('petugas_register');
    }

    /**
     * Cek apakah user adalah Akses SLIK
     */
    public static function isAksesSlik()
    {
        return self::hasRole('petugas_slik');
    }

    /**
     * Cek apakah user adalah Akses Data
     */
    public static function isAksesData()
    {
        return self::hasRole('petugas_data');
    }

    /**
     * Cek apakah user adalah Akses Komite (sekarang disebut Akses Admin)
     */
    public static function isAksesKomite()
    {
        return self::hasRole('petugas_komite');
    }

    /**
     * Cek apakah user adalah Akses Admin (alias untuk isAksesKomite)
     */
    public static function isAksesAdmin()
    {
        return self::isAksesKomite();
    }

    /**
     * Cek apakah user bisa upload di register
     */
    public static function canUploadRegister()
    {
        // Gunakan method canUploadRealisasi untuk konsistensi
        return self::canUploadRealisasi();
    }

    /**
     * Cek apakah user bisa lihat detail di register
     */
    public static function canViewDetailRegister()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Akses Admin bisa lihat detail
        if (self::isAksesAdmin()) {
            return true;
        }

        // Akses Register bisa lihat detail
        if (self::isAksesRegister()) {
            return true;
        }

        // Akses Data bisa lihat detail register
        if (self::isAksesData()) {
            return true;
        }

        // Akses SLIK bisa lihat detail register
        if (self::isAksesSlik()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa lihat detail di data
     */
    public static function canViewDetailData()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Akses Admin bisa lihat detail
        if (self::isAksesAdmin()) {
            return true;
        }

        // Akses Data bisa lihat detail
        if (self::isAksesData()) {
            return true;
        }

        // Akses SLIK juga boleh lihat detail data
        if (self::isAksesSlik()) {
            return true;
        }

        // Akses Register bisa lihat detail
        if (self::isAksesRegister()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa lihat detail di bank
     */
    public static function canViewDetailBank()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Akses Admin bisa lihat detail
        if (self::isAksesAdmin()) {
            return true;
        }

        // Akses Data bisa lihat detail
        if (self::isAksesData()) {
            return true;
        }

        // Akses SLIK juga boleh lihat detail bank
        if (self::isAksesSlik()) {
            return true;
        }

        // Akses Register bisa lihat detail
        if (self::isAksesRegister()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa lihat detail di SLIK
     */
    public static function canViewDetailSlik()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Akses Admin bisa lihat detail
        if (self::isAksesAdmin()) {
            return true;
        }

        // Akses Data bisa lihat detail
        if (self::isAksesData()) {
            return true;
        }

        // Akses SLIK bisa lihat detail
        if (self::isAksesSlik()) {
            return true;
        }

        // Akses Register bisa lihat detail
        if (self::isAksesRegister()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user memiliki akses menu tertentu
     */
    public static function hasMenuAccess($menu)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $authorizedMenus = $user->getAuthorizedMenusArray();
        return in_array($menu, $authorizedMenus);
    }

    /**
     * Cek apakah user bisa menambah data
     */
    public static function canCreate($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Akses Admin bisa create di register untuk upload realisasi
        // if (self::isAksesAdmin() && $feature === 'register') {
        //     return false;
        // }

        // Level 1-4 (komite) hanya bisa create di komite, selain itu read-only
        if (in_array($user->level, ['1', '2', '3', '4'])) {
            if ($feature === 'komite') {
                return self::canFillKomiteColumn('rekomendasi_manager') || 
                       self::canFillKomiteColumn('opini_direktur_kepatuhan') || 
                       self::canFillKomiteColumn('keputusan_direktur_utama') || 
                       self::canFillKomiteColumn('mengetahui_komisaris');
            }
            return false; // Level 1-4 tidak bisa create di fitur lain
        }

        // Level 0 (bukan komite) - logic lama
        // Level 1-4 (komite) di data, bank, slik hanya read-only
        if (!self::canModifyData($feature)) {
            return false;
        }

        // Kasus khusus: Menu manajemen hanya untuk SUPERADMIN atau user dengan akses manajemen
        if ($feature === 'manajemen') {
            return self::hasMenuAccess('menu_manajemen');
        }

        // Kasus khusus: Akses Register dengan akses Komite hanya bisa read-only
        if (self::isAksesRegister() && $feature === 'komite' && self::hasMenuAccess('menu_komite')) {
            return false; // Akses Register + Komite = read-only
        }

        // PRIORITAS: Jika user memiliki Akses Register, ia boleh create pada fitur selain Komite
        if (self::isAksesRegister()) {
            return true; // Override pembatasan lain kecuali komite di atas
        }


        // Kasus khusus: Akses SLIK dengan akses menu lain hanya bisa read-only
        if (self::isAksesSlik() && $feature !== 'slik' && self::hasMenuAccess('menu_' . $feature)) {
            return false; // Akses SLIK + menu lain = read-only
        }

        // Kasus khusus: Akses SLIK di SLIK hanya bisa upload hasil, tidak bisa tambah data
        if (self::isAksesSlik() && $feature === 'slik') {
            return false; // Akses SLIK tidak bisa menambah data SLIK, hanya upload hasil
        }

        if (self::isAksesKomite() && $feature === 'komite') {
            return true; // Akses Komite hanya bisa menambah di komite
        }

        return false;
    }

    /**
     * Cek apakah user bisa mengedit data
     */
    public static function canEdit($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        // Jika user Akses Admin, bisa edit register untuk upload realisasi
        // if (self::isAksesAdmin() && $feature === 'register') {
        //     return false;
        // }

        // Level 1-4 (komite) hanya bisa edit di komite, selain itu read-only
        if (in_array($user->level, ['1', '2', '3', '4'])) {
            if ($feature === 'komite') {
                return self::canFillKomiteColumn('rekomendasi_manager') || 
                       self::canFillKomiteColumn('opini_direktur_kepatuhan') || 
                       self::canFillKomiteColumn('keputusan_direktur_utama') || 
                       self::canFillKomiteColumn('mengetahui_komisaris');
            }
            return false; // Level 1-4 tidak bisa edit di fitur lain
        }

        // Level 0 (bukan komite) - logic lama
        // Level 1-4 (komite) di data, bank, slik hanya read-only
        if (!self::canModifyData($feature)) {
            return false;
        }

        // Kasus khusus: Menu manajemen hanya untuk SUPERADMIN atau user dengan akses manajemen
        if ($feature === 'manajemen') {
            return self::hasMenuAccess('menu_manajemen');
        }

        // Kasus khusus: Akses Register dengan akses Komite hanya bisa read-only
        if (self::isAksesRegister() && $feature === 'komite' && self::hasMenuAccess('menu_komite')) {
            return false; // Akses Register + Komite = read-only
        }

        // PRIORITAS: Jika user memiliki Akses Register, ia boleh edit (semua fitur kecuali Komite)
        if (self::isAksesRegister()) {
            return true; 
        }

        // Kasus khusus: Akses SLIK dengan akses menu lain hanya bisa read-only
        if (self::isAksesSlik() && $feature !== 'slik' && self::hasMenuAccess('menu_' . $feature)) {
            return false; // Akses SLIK + menu lain = read-only
        }

        // Kasus khusus: Akses SLIK di SLIK hanya bisa upload hasil, tidak bisa edit
        if (self::isAksesSlik() && $feature === 'slik') {
            return false; // Akses SLIK hanya bisa upload hasil, tidak bisa mengedit data SLIK
        }

        if (self::isAksesKomite() && $feature === 'komite') {
            return true; // Akses Komite hanya bisa mengedit di komite
        }

        return false;
    }

    /**
     * Cek apakah user bisa menghapus data
     */
    public static function canDelete($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Level 1-4 (komite) hanya bisa delete di komite, selain itu read-only
        if (in_array($user->level, ['1', '2', '3', '4'])) {
            if ($feature === 'komite') {
                return self::canFillKomiteColumn('rekomendasi_manager') || 
                       self::canFillKomiteColumn('opini_direktur_kepatuhan') || 
                       self::canFillKomiteColumn('keputusan_direktur_utama') || 
                       self::canFillKomiteColumn('mengetahui_komisaris');
            }
            return false; // Level 1-4 tidak bisa delete di fitur lain
        }

        // Level 0 (bukan komite) - logic lama
        // Level 1-4 (komite) di data, bank, slik hanya read-only
        if (!self::canModifyData($feature)) {
            return false;
        }

        // Kasus khusus: Menu manajemen hanya untuk SUPERADMIN atau user dengan akses manajemen
        if ($feature === 'manajemen') {
            return self::hasMenuAccess('menu_manajemen');
        }

        // Kasus khusus: Akses Register dengan akses Komite hanya bisa read-only
        if (self::isAksesRegister() && $feature === 'komite' && self::hasMenuAccess('menu_komite')) {
            return false; // Akses Register + Komite = read-only
        }

        // PERUBAHAN: Akses Register TIDAK bisa delete di register, bank, slik, data
        if (self::isAksesRegister() && in_array($feature, ['register', 'bank', 'slik', 'data'])) {
            return false; // Akses Register tidak bisa hapus di register, bank, slik, data
        }

        // PERUBAHAN: Akses Admin bisa delete di register, bank, slik, data
        if (self::isAksesAdmin() && in_array($feature, ['register', 'bank', 'slik', 'data'])) {
            return true; // Akses Admin bisa hapus di register, bank, slik, data
        }

        // Kasus khusus: Akses SLIK tidak bisa menghapus data (hanya bisa lihat dan upload)
        if (self::isAksesSlik()) {
            return false; // Akses SLIK tidak bisa menghapus data di fitur manapun
        }

        if (self::isAksesKomite() && $feature === 'komite') {
            return true; // Akses Komite hanya bisa menghapus di komite
        }

        return false;
    }

    /**
     * Cek apakah user bisa melihat data
     */
    public static function canView($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Kasus khusus: Menu manajemen hanya untuk SUPERADMIN atau user dengan akses manajemen
        if ($feature === 'manajemen') {
            return self::hasMenuAccess('menu_manajemen');
        }

        if (self::isAksesRegister() || self::isAksesData() || self::isAksesKomite() || self::isAksesSlik()) {
            return true; // Semua akses bisa melihat data
        }

        return false;
    }

    /**
     * Cek apakah user bisa upload hasil SLIK
     */
    public static function canUploadSlik()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        if (self::isAksesSlik()) {
            return true; // Akses SLIK bisa upload hasil hanya di SLIK
        }

        return false;
    }

    /**
     * Cek apakah user bisa upload hasil di fitur tertentu
     */
    public static function canUploadResult($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Akses SLIK hanya bisa upload hasil di fitur SLIK
        if (self::isAksesSlik() && $feature === 'slik') {
            return true;
        }

        // Untuk fitur lain (Bank, dll), Akses SLIK tidak bisa upload
        if (self::isAksesSlik() && $feature !== 'slik') {
            return false;
        }

        return false;
    }

    /**
     * Cek apakah user bisa upload realisasi di register
     * Hanya SUPERADMIN yang bisa upload realisasi
     * User level 1-4 (komite) tidak bisa upload realisasi
     */
    public static function canUploadRealisasi()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // User level 1-4 (komite) tidak bisa upload realisasi
        if (in_array($user->level, ['1', '2', '3', '4'])) {
            return false;
        }

        // Hanya user level 0 dengan akses admin yang bisa upload realisasi
        if ($user->level === '0' && self::isAksesAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa hapus data sekarang (untuk akses admin)
     */
    public static function canDeleteNow($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        // Hanya Akses Admin yang bisa hapus data sekarang
        if (self::isAksesAdmin() && in_array($feature, ['register', 'bank', 'slik', 'data'])) {
            return true;
        }

        return false;
    }

    /**
     * Dapatkan filter data berdasarkan peran user
     */
    public static function getDataFilter()
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // SUPERADMIN melihat semua data
        if (self::isSuperAdmin()) {
            return [];
        }

        // Level 1-4 (komite) melihat semua data
        if (in_array($user->level, ['1', '2', '3', '4'])) {
            return [];
        }

        // Akses Register (level 0) hanya melihat data yang mereka input
        if (self::isAksesRegister()) {
            return ['input_by' => $user->nama];
        }

        // Akses Data dan Komite melihat semua data
        if (self::isAksesData() || self::isAksesKomite()) {
            return [];
        }

        return [];
    }

    /**
     * Cek apakah user adalah komite berdasarkan level
     */
    public static function isKomite()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $level = $user->level;
        // Cukup cek apakah level ada di daftar komite
        return in_array($level, ['1', '2', '3', '4']);
    }

    /**
     * Cek apakah user bisa mengakses setting pengguna
     * Level 0 (bukan komite) bisa akses setting
     * Level 1-4 (komite) tidak bisa akses setting
     */
    public static function canAccessUserSettings()
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }
        // Level 0 bisa akses setting, Level 1-4 tidak bisa
        return $user->level === '0';
    }

    /**
     * Cek apakah user bisa mengedit data berdasarkan level
     */
    public static function canModifyData($feature = null)
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Level 1-4 di data, bank, slik hanya read-only
        if (in_array($user->level, ['1', '2', '3', '4']) && in_array($feature, ['data', 'bank', 'slik'])) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan role komite berdasarkan level
     */
    public static function getKomiteRole()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        $level = $user->level;
        $roleMap = [
            '1' => 'direktur_utama',
            '2' => 'komisaris',
            '3' => 'direktur_kepatuhan',
            '4' => 'manager'
        ];
        return isset($roleMap[$level]) ? $roleMap[$level] : null;
    }

    /**
     * Tentukan kolom komite mana yang harus ditampilkan berdasarkan role user
     */
    public static function getVisibleKomiteColumns()
    {
        if (self::isSuperAdmin()) {
            return ['rekomendasi_manager', 'opini_direktur_kepatuhan', 'keputusan_direktur_utama', 'mengetahui_komisaris'];
        }
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $level = $user->level;
        // Level 0 (bukan komite) melihat semua kolom
        if ($level === '0') {
            return ['rekomendasi_manager', 'opini_direktur_kepatuhan', 'keputusan_direktur_utama', 'mengetahui_komisaris'];
        }
        // Level 1-4 melihat semua kolom komite
        if (in_array($level, ['1', '2', '3', '4'])) {
            return ['rekomendasi_manager', 'opini_direktur_kepatuhan', 'keputusan_direktur_utama', 'mengetahui_komisaris'];
        }
        return [];
    }

    /**
     * Cek apakah user bisa mengisi kolom komite tertentu berdasarkan level
     */
    public static function canFillKomiteColumn($column)
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $level = $user->level;
        if ($level === '0') {
            return false;
        }
        // Map kolom dengan level yang sesuai
        $levelToColumn = [
            '1' => 'keputusan_direktur_utama',
            '2' => 'mengetahui_komisaris',
            '3' => 'opini_direktur_kepatuhan',
            '4' => 'rekomendasi_manager',
        ];
        return (isset($levelToColumn[$level]) && $levelToColumn[$level] === $column);
    }

    /**
     * Cek apakah user bisa mengedit data komite tertentu (tidak mempertimbangkan jabatan)
     */
    public static function canEditKomiteData($komiteData)
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        // Jika user adalah pembuat data, boleh edit
        if ($komiteData && $komiteData->input_by === $user->nama) {
            return true;
        }
        // Jika tidak ada data, tidak bisa edit (harus create dulu)
        if (!$komiteData) {
            return false;
        }
        // Ambil level user
        $userLevel = $user->level;
        $existingUserLevel = null;
        if ($komiteData && $komiteData->input_by) {
            $existingUser = \App\Models\User::where('nama', $komiteData->input_by)->first();
            if ($existingUser) {
                $existingUserLevel = $existingUser->level;
            }
        }
        // Jika user level yang sama sudah ada yang mengisi, tidak boleh edit
        if ($existingUserLevel && $existingUserLevel === $userLevel) {
            return false;
        }
        // Jika data sudah ada dan user bukan pembuatnya, tidak boleh edit
        if ($komiteData && $komiteData->input_by !== $user->nama) {
            return false;
        }
        return true;
    }
}