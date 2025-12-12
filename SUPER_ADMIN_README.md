# Sistem Super Admin - Dokumentasi

## Overview
Sistem ini telah dikonfigurasi dengan super admin yang tidak bisa dihapus atau diubah untuk keamanan aplikasi.

## Fitur Super Admin

### 1. Akun Default
- **Username**: `SUPERADMIN`
- **Password**: `123456`
- **Email**: `superadmin@system.com`
- **Status**: `active` (tidak bisa diubah)

### 2. Proteksi Otomatis
- Super admin **TIDAK BISA DIHAPUS** dari database
- Super admin **TIDAK BISA DIUBAH** datanya
- Tombol edit dan hapus akan **disabled** untuk super admin
- Badge "Super Admin" akan ditampilkan di tabel

## Cara Penggunaan

### A. Setup Awal (Setelah Migrate)
```bash
# Jalankan migrate dan seeder
php artisan migrate:fresh --seed

# Atau jika sudah ada data
php artisan migrate
php artisan db:seed
```

### B. Login dengan Super Admin
1. Buka halaman `/sign_in`
2. Masukkan:
   - Username: `SUPERADMIN`
   - Password: `123456`
3. Klik Login

### C. Membuat Super Admin Baru (Jika Diperlukan)
```bash
# Dengan username dan password default
php artisan make:superadmin

# Dengan username dan password custom
php artisan make:superadmin --username=ADMINISTRATOR --password=password123
```

## Struktur Database

### Tabel `user`
- `id` - Primary key
- `username` - Username unik (SUPERADMIN untuk super admin)
- `password` - Password terenkripsi
- `nama` - Nama lengkap
- `nik` - NIK (16 digit)
- `email` - Email unik
- `no_hp` - Nomor HP
- `online` - Status online (boolean)
- `status` - Status user (active/inactive/suspended)
- `otp` - Kode OTP (nullable)
- `time_otp` - Waktu OTP (nullable)
- `timestamps` - created_at, updated_at

## Keamanan

### 1. Middleware Protection
- Route `PUT /users/{user}` dan `DELETE /users/{user}` dilindungi middleware `protect.superadmin`
- Middleware akan memblokir perubahan pada user dengan username `SUPERADMIN`

### 2. View Protection
- Tombol edit dan hapus disabled untuk super admin
- Badge khusus menandakan user adalah super admin
- Pesan error jika mencoba mengubah super admin

### 3. Controller Protection
- Validasi di level controller untuk mencegah perubahan super admin
- Redirect dengan pesan error jika ada percobaan perubahan

## Troubleshooting

### Q: Super admin tidak bisa login
**A**: Pastikan:
1. Database sudah di-migrate dan di-seed
2. Tabel `user` ada dan berisi data super admin
3. Password yang dimasukkan benar: `123456`

### Q: Tombol edit/hapus tidak disabled
**A**: Pastikan:
1. Username user adalah `SUPERADMIN` (case sensitive)
2. View sudah di-update dengan kondisi `@if($user->username === 'SUPERADMIN')`

### Q: Middleware tidak berfungsi
**A**: Pastikan:
1. Middleware sudah didaftarkan di `bootstrap/app.php`
2. Route sudah menggunakan middleware `protect.superadmin`

## File yang Dimodifikasi

1. **`database/seeders/SuperAdminSeeder.php`** - Seeder untuk super admin
2. **`database/seeders/DatabaseSeeder.php`** - Memanggil SuperAdminSeeder
3. **`app/Http/Middleware/ProtectSuperAdmin.php`** - Middleware proteksi
4. **`bootstrap/app.php`** - Registrasi middleware
5. **`routes/web.php`** - Penerapan middleware ke route
6. **`resources/views/manajemen_pengguna/index.blade.php`** - UI protection
7. **`app/Console/Commands/CreateSuperAdmin.php`** - Command artisan

## Catatan Penting

⚠️ **JANGAN PERNAH MENGHAPUS ATAU MENGUBAH USER DENGAN USERNAME `SUPERADMIN`** ⚠️

User ini adalah akun root yang diperlukan untuk:
- Login pertama kali ke sistem
- Akses ke semua fitur admin
- Recovery jika ada masalah dengan user lain
- Backup access untuk maintenance

Jika super admin terhapus, Anda harus:
1. Restore dari backup database, atau
2. Jalankan `php artisan make:superadmin` untuk membuat ulang
