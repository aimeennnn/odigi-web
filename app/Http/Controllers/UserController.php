<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'view', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $allowedSorts = [
            'username' => 'username',
            'nama' => 'nama',
            'nik' => 'nik',
            'email' => 'email',
            'no_hp' => 'no_hp',
            'status' => 'status',
            'online' => 'online',
            'created_at' => 'created_at',
        ];

        $sortParam = $request->get('sort');
        $orderParam = strtolower($request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        $usersQuery = User::select('id', 'username', 'nama', 'nik', 'email', 'no_hp', 'online', 'status', 'jabatan', 'level', 'authorized_menus', 'roles', 'created_at')
            ->where('status', 'active'); // Hanya tampilkan user dengan status active

        // Handle search functionality
        $searchTerm = $request->get('search');
        if ($searchTerm) {
            $usersQuery->where(function($query) use ($searchTerm) {
                $query->where('username', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nama', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nik', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('no_hp', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($sortParam && isset($allowedSorts[$sortParam])) {
            $usersQuery->orderBy($allowedSorts[$sortParam], $orderParam);
        } else {
             $usersQuery->orderBy('id', 'asc');
        }

        // Handle per_page parameter
        $perPage = $request->get('per_page', 10);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $users = $usersQuery->paginate($perPage);

        return view('manajemen_pengguna.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'create', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        try {
            // Log input data untuk debugging
            Log::info('Attempting to create user', [
                'username' => $request->username,
                'email' => $request->email,
                'nik' => $request->nik,
                'has_password' => !empty($request->password),
                'has_password_confirmation' => !empty($request->password_confirmation),
            ]);

            $validated = $request->validate([
                'username' => 'required|string|max:255|unique:user,username',
                'password' => 'required|string|min:6|confirmed',
                'nama' => 'required|string|max:255',
                'nik' => 'required|string|size:16|unique:user,nik|regex:/^[0-9]{16}$/',
                'email' => 'required|email|max:255|unique:user,email',
                // No HP persis +62 diikuti 10-12 digit (standar Indonesia)
                'no_hp' => ['required','string','regex:/^\+62[0-9]{10,12}$/'],
                'status' => 'required|in:active,inactive,suspended',
                'jabatan' => 'required|string|max:255',
                'level' => 'required|string|in:0,1,2,3,4',
            ], [
                'nik.regex' => 'NIK harus berisi 16 digit angka.',
                'nik.size' => 'NIK harus berisi 16 digit.',
                'no_hp.regex' => 'No. HP wajib awalan +62 dan diikuti 10-12 digit angka. Maksimal hanya 12 angka setelah +62, tanpa spasi/tanda lain.',
                'jabatan.required' => 'Jabatan harus diisi.',
                'level.required' => 'Level harus dipilih.',
                'level.in' => 'Level harus berupa 0, 1, 2, 3, atau 4.',
            ]);

            // Log validated data
            Log::info('Validation passed', $validated);

            // Normalisasi nomor HP: simpan sebagai +62XXXXXXXXXX
            $normalizedHp = $validated['no_hp'];
            // Pastikan format +62XXXXXXXXXX
            if (str_starts_with($normalizedHp, '+62')) {
                // Sudah benar format +62
                $normalizedHp = $normalizedHp;
            } elseif (str_starts_with($normalizedHp, '62')) {
                $normalizedHp = '+' . $normalizedHp;
            } elseif (str_starts_with($normalizedHp, '0')) {
                $normalizedHp = '+62' . substr($normalizedHp, 1);
            } elseif (str_starts_with($normalizedHp, '8')) {
                $normalizedHp = '+62' . $normalizedHp;
            } else {
                $normalizedHp = '+62' . $normalizedHp;
            }

            // Tentukan default akses menu & roles agar user baru tidak langsung ditolak middleware
            $defaultMenus = ['menu_register'];
            if (in_array($validated['level'], ['1','2','3','4'])) {
                // Samakan dengan template user komite (seperti Hernando):
                $defaultMenus = ['menu_register','menu_data','menu_bank','menu_slik','menu_komite'];
            }
            $defaultRoles = [
                'petugas_register' => true,
                'petugas_slik' => false,
                'petugas_data' => false,
                'petugas_komite' => in_array($validated['level'], ['1','2','3','4'])
            ];

            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'email' => $validated['email'],
                'no_hp' => $normalizedHp,
                'status' => $validated['status'],
                'jabatan' => $validated['jabatan'],
                'level' => $validated['level'],
                'online' => false,
                'authorized_menus' => $defaultMenus,
                'roles' => $defaultRoles,
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return back()->withInput()->withErrors($e->errors());
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error while creating user', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return back()->withInput()->withErrors([
                'general' => 'Gagal menyimpan ke database. Pastikan semua data valid dan tidak ada duplikasi.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while creating user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->withErrors([
                'general' => 'Terjadi kesalahan tidak terduga. Coba lagi atau hubungi admin.',
            ]);
        }
    }

    public function destroy(User $user)
    {
        // Double check menu access dan permission untuk delete
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'delete', request());
        if ($accessCheck) {
            return $accessCheck;
        }
        // Jangan izinkan menghapus SUPERADMIN
        if ($user->username === 'SUPERADMIN') {
            return redirect()->route('users.index')->with('error', 'SUPERADMIN tidak dapat dinonaktifkan');
        }
        
        // Ubah status menjadi inactive alih-alih menghapus (soft delete)
        $user->status = 'inactive';
        $user->save();
        
        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan. Data dan audit trail tetap tersimpan.');
    }


    public function edit(User $user)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'edit', request());
        if ($accessCheck) {
            return $accessCheck;
        }
        return view('manajemen_pengguna.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        try {
            // Validasi dasar
            $validated = $request->validate([
                'username' => 'required|string|max:255|unique:user,username,' . $user->id,
                'nama' => 'required|string|max:255',
                'nik' => 'required|string|size:16|unique:user,nik,' . $user->id,
                'email' => 'required|email|max:255|unique:user,email,' . $user->id,
                'no_hp' => ['required','string','regex:/^\+62[0-9]{10,12}$/'],
                'status' => 'required|in:active,inactive,suspended',
                'jabatan' => 'required|string|max:255',
                'level' => 'required|string|in:0,1,2,3,4',
                'password' => 'nullable|string|min:6',
                'password_confirmation' => 'nullable|string|min:6',
            ]);

            // Validasi khusus untuk password
            if (!empty($validated['password']) || !empty($request->password_confirmation)) {
                // Jika salah satu field password diisi, keduanya harus diisi
                if (empty($validated['password'])) {
                    return back()->withInput()->withErrors([
                        'password' => 'Password baru harus diisi jika ingin mengganti password.',
                    ]);
                }
                
                if (empty($request->password_confirmation)) {
                    return back()->withInput()->withErrors([
                        'password_confirmation' => 'Konfirmasi password harus diisi.',
                    ]);
                }
                
                // Cek apakah password dan konfirmasi password sama
                if ($validated['password'] !== $request->password_confirmation) {
                    return back()->withInput()->withErrors([
                        'password_confirmation' => 'Konfirmasi password tidak sama dengan password baru.',
                    ]);
                }
                
                // Hash password jika valid
                $validated['password'] = Hash::make($validated['password']);
            } else {
                // Jika tidak ada password yang diisi, hapus dari array
                unset($validated['password']);
            }

            // Auto-aturan berdasarkan level
            if (in_array($validated['level'], ['1','2','3','4'])) {
                // Non-editable settings for committee levels: mirror standard komite (Hernando)
                $autoMenus = ['menu_register','menu_data','menu_bank','menu_slik','menu_komite'];
                $autoRoles = [
                    'petugas_register' => true,
                    'petugas_slik' => false,
                    'petugas_data' => false,
                    'petugas_komite' => true,
                ];
                $validated['authorized_menus'] = $autoMenus;
                $validated['roles'] = $autoRoles;
            } else {
                // Level 0: pastikan minimal menu_register tetap ada
                $currentMenus = $user->getAuthorizedMenusArray();
                if (!in_array('menu_register', $currentMenus)) {
                    $currentMenus[] = 'menu_register';
                }
                $validated['authorized_menus'] = $currentMenus;
            }

            // Normalisasi nomor HP: simpan sebagai +62XXXXXXXXXX
            $normalizedHp = $validated['no_hp'];
            // Pastikan format +62XXXXXXXXXX
            if (str_starts_with($normalizedHp, '+62')) {
                // Sudah benar format +62
                $normalizedHp = $normalizedHp;
            } elseif (str_starts_with($normalizedHp, '62')) {
                $normalizedHp = '+' . $normalizedHp;
            } elseif (str_starts_with($normalizedHp, '0')) {
                $normalizedHp = '+62' . substr($normalizedHp, 1);
            } elseif (str_starts_with($normalizedHp, '8')) {
                $normalizedHp = '+62' . $normalizedHp;
            } else {
                $normalizedHp = '+62' . $normalizedHp;
            }
            $validated['no_hp'] = $normalizedHp;

            $user->update($validated);

            return redirect()->route('users.index')->with('success', 'User berhasil diubah');
        } catch (\Throwable $e) {
            Log::error('Gagal mengubah user', [
                'message' => $e->getMessage(),
            ]);
            return back()->withInput()->withErrors([
                'general' => 'Terjadi kesalahan saat mengubah data. Coba lagi atau hubungi admin.',
            ]);
        }
    }

    public function updateSettings(Request $request, User $user)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_manajemen', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        try {
            // Log input data untuk debugging
            Log::info('Updating user settings', [
                'user_id' => $user->id,
                'username' => $user->username,
                'request_data' => $request->all(),
                'has_menu_data' => $request->has('menu_data'),
                'has_menu_slik' => $request->has('menu_slik'),
                'has_menu_komite' => $request->has('menu_komite'),
                'has_menu_bank' => $request->has('menu_bank'),
                'has_menu_manajemen' => $request->has('menu_manajemen'),
            ]);

            // SUPERADMIN: selalu punya semua akses, tidak bisa diubah
            if ($user->username === 'SUPERADMIN') {
                $user->authorized_menus = [
                    'menu_register',
                    'menu_data',
                    'menu_slik',
                    'menu_komite',
                    'menu_bank',
                    'menu_manajemen'
                ];
                $user->roles = [
                    'petugas_register' => true,
                    'petugas_slik' => true,
                    'petugas_data' => true,
                    'petugas_komite' => true,
                ];
                $user->save();
                return redirect()->route('users.index')->with('success', 'SUPERADMIN selalu memiliki semua akses. Pengaturan tidak dapat diubah.');
            }

            // Simpan peran (role) untuk user biasa
            $roles = [
                'petugas_register' => $request->has('petugas_register'),
                'petugas_slik' => $request->has('petugas_slik'),
                'petugas_data' => $request->has('petugas_data'),
                'petugas_komite' => $request->has('petugas_komite'),
            ];
            $user->roles = $roles;

            // Simpan menu yang diotorisasi berdasarkan checkbox menu yang dipilih
            $authorizedMenus = ['menu_register']; // Register selalu wajib ada
            
            // Cek checkbox menu lainnya
            if ($request->has('menu_data')) {
                $authorizedMenus[] = 'menu_data';
            }
            if ($request->has('menu_slik')) {
                $authorizedMenus[] = 'menu_slik';
            }
            if ($request->has('menu_komite')) {
                $authorizedMenus[] = 'menu_komite';
            }
            if ($request->has('menu_bank')) {
                $authorizedMenus[] = 'menu_bank';
            }
            if ($request->has('menu_manajemen')) {
                $authorizedMenus[] = 'menu_manajemen';
            }
            
            $user->authorized_menus = $authorizedMenus;

            // Log data yang akan disimpan
            Log::info('Saving user settings', [
                'user_id' => $user->id,
                'roles' => $roles,
                'authorized_menus' => $authorizedMenus,
            ]);

            $user->save();

            // Log hasil penyimpanan
            Log::info('User settings saved successfully', [
                'user_id' => $user->id,
                'saved_roles' => $user->roles,
                'saved_authorized_menus' => $user->authorized_menus,
            ]);

            return redirect()->route('users.index')->with('success', 'Setting pengguna berhasil disimpan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan setting user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors([
                'general' => 'Terjadi kesalahan saat menyimpan setting. Coba lagi atau hubungi admin.',
            ]);
        }
    }

}