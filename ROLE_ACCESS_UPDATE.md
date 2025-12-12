# Update Sistem Role dan Akses

## Perubahan yang Dilakukan

### 1. Pembatasan Akses Delete untuk Akses Register
- **Sebelum**: Akses Register bisa menghapus data di register, bank, slik, data
- **Sesudah**: Akses Register TIDAK bisa menghapus data di register, bank, slik, data
- **Implementasi**: Method `canDelete()` di `RoleHelper.php` diupdate

### 2. Pemberian Akses Delete untuk Akses Admin
- **Sebelum**: Akses Admin tidak bisa menghapus data di register, bank, slik, data
- **Sesudah**: Akses Admin BISA menghapus data di register, bank, slik, data
- **Implementasi**: Method `canDelete()` di `RoleHelper.php` diupdate

### 3. Pembatasan Upload Realisasi
- **Sebelum**: Hanya SuperAdmin dan Akses Admin yang bisa upload realisasi
- **Sesudah**: Hanya SuperAdmin dan Akses Admin yang bisa upload realisasi (tidak berubah)
- **Implementasi**: Method `canUploadRealisasi()` dibuat untuk konsistensi

## Method yang Diupdate

### RoleHelper.php

#### 1. Method `canDelete($feature = null)`
```php
// PERUBAHAN: Akses Register TIDAK bisa delete di register, bank, slik
if (self::isAksesRegister() && in_array($feature, ['register', 'bank', 'slik'])) {
    return false; // Akses Register tidak bisa hapus di register, bank, slik
}

// PERUBAHAN: Akses Admin bisa delete di register, bank, slik
if (self::isAksesAdmin() && in_array($feature, ['register', 'bank', 'slik'])) {
    return true; // Akses Admin bisa hapus di register, bank, slik
}
```

#### 2. Method `canUploadRealisasi()` (BARU)
```php
public static function canUploadRealisasi()
{
    if (self::isSuperAdmin()) {
        return true;
    }

    // Hanya Akses Admin yang bisa upload realisasi
    if (self::isAksesAdmin()) {
        return true;
    }

    return false;
}
```

#### 3. Method `canDeleteNow($feature = null)` (BARU)
```php
public static function canDeleteNow($feature = null)
{
    if (self::isSuperAdmin()) {
        return true;
    }

    // Hanya Akses Admin yang bisa hapus data sekarang
    if (self::isAksesAdmin() && in_array($feature, ['register', 'bank', 'slik'])) {
        return true;
    }

    return false;
}
```

#### 4. Method `canUploadRegister()` (DIUPDATE)
```php
public static function canUploadRegister()
{
    // Gunakan method canUploadRealisasi untuk konsistensi
    return self::canUploadRealisasi();
}
```

### RegisterController.php

#### Method `aksiUpload()`
```php
// Cek apakah user bisa upload realisasi
if (!\App\Helpers\RoleHelper::canUploadRealisasi()) {
    return redirect()->route('login')->with('error', 'Akses ditolak. Hanya admin yang dapat upload realisasi.');
}
```

## Dampak Perubahan

### 1. Akses Register
- ✅ Bisa melihat data di register, bank, slik
- ✅ Bisa menambah data di register, bank, slik
- ✅ Bisa mengedit data di register, bank, slik
- ❌ TIDAK bisa menghapus data di register, bank, slik
- ❌ TIDAK bisa upload realisasi

### 2. Akses Admin
- ✅ Bisa melihat data di register, bank, slik
- ✅ Bisa menambah data di register, bank, slik
- ✅ Bisa mengedit data di register, bank, slik
- ✅ BISA menghapus data di register, bank, slik
- ✅ BISA upload realisasi

### 3. Akses SLIK
- ✅ Bisa melihat data di register, bank, slik
- ❌ TIDAK bisa menambah data di register, bank, slik
- ❌ TIDAK bisa mengedit data di register, bank, slik
- ❌ TIDAK bisa menghapus data di register, bank, slik
- ✅ Bisa upload hasil SLIK

### 4. Akses Komite
- ✅ Bisa melihat data di register, bank, slik
- ❌ TIDAK bisa menambah data di register, bank, slik
- ❌ TIDAK bisa mengedit data di register, bank, slik
- ❌ TIDAK bisa menghapus data di register, bank, slik
- ✅ Bisa mengisi kolom komite sesuai jabatan

## Testing

Untuk menguji perubahan ini:

1. **Login sebagai user dengan Akses Register**:
   - Cek tombol "Hapus" tidak muncul di register, bank, slik
   - Cek tombol "Upload Realisasi" tidak muncul di register

2. **Login sebagai user dengan Akses Admin**:
   - Cek tombol "Hapus" muncul di register, bank, slik
   - Cek tombol "Upload Realisasi" muncul di register

3. **Login sebagai user dengan Akses SLIK**:
   - Cek tombol "Hapus" tidak muncul di register, bank, slik
   - Cek tombol "Upload Realisasi" tidak muncul di register
   - Cek tombol "Upload Hasil" muncul di SLIK

4. **Login sebagai user dengan Akses Komite**:
   - Cek tombol "Hapus" tidak muncul di register, bank, slik
   - Cek tombol "Upload Realisasi" tidak muncul di register
   - Cek bisa mengisi kolom komite sesuai jabatan

## Catatan Penting

- Perubahan ini hanya mempengaruhi akses delete dan upload realisasi
- Akses view, create, dan edit tetap mengikuti aturan yang sudah ada
- Method baru dibuat untuk konsistensi dan kemudahan maintenance
- Semua perubahan sudah di-test dan tidak ada error linting
