<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\RoleHelper;

class RegisterController extends BaseController
{
    public function index()
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_register', 'view', request());
        if ($accessCheck) {
            return $accessCheck;
        }
        $query = Register::query();
        $sort = request('sort');
        $order = request('order', 'asc');
        $allowedSorts = [
            'nomor', 'nama', 'jenis_entitas', 'nama_badan_usaha', 'jns_kelamin',
            'pekerjaan', 'alamat', 'tgl_pengajuan', 'jns_pengajuan',
            'nominal_pengajuan', 'jw_pengajuan', 'jaminan', 'status', 'id_user'
        ];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderByDesc('nomor'); 
        }
        
        // Filter berdasarkan peran user
        $dataFilter = RoleHelper::getDataFilter();
        if (!empty($dataFilter)) {
            foreach ($dataFilter as $key => $value) {
                $query->where($key, $value);
            }
        }
        
        // FILTER
        if ($nama = request('filter_nama')) {
            $query->where('nama', 'like', '%' . $nama . '%');
        }
        if ($status = request('filter_status')) {
            $query->where('status', $status);
        }
        if ($jenis = request('filter_jns_pengajuan')) {
            $query->where('jns_pengajuan', $jenis);
        }
        // Handle per_page parameter
        $perPage = request('per_page', 5);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        $registers = $query->paginate($perPage)->withQueryString();
        $year = date('Y');
        // Cari nomor terakhir di tahun berjalan
        $lastThisYear = Register::where('nomor', 'like', '%/REG/' . $year)
            ->orderByDesc('id_reg')
            ->first();
        if ($lastThisYear && preg_match('/^(\d{3})\/REG\/' . $year . '$/', $lastThisYear->nomor, $m)) {
            $lastNum = (int)$m[1];
            $nextNumber = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }
        $nomor_registrasi = "$nextNumber/REG/$year";
        return view('register.index', compact('registers', 'nomor_registrasi'));
    }

    /**
     * Method untuk route publik (tanpa login)
     */
    public function publicIndex()
    {
        return view('register.public');
    }

    /**
     * Method untuk store data publik (tanpa login)
     */
    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'nomor' => 'required',
            'nama' => 'required_if:jenis_entitas,perorangan',
            'jenis_entitas' => 'required|in:perorangan,badan_usaha',
            'nama_badan_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jenis_dokumen_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'nomor_legalitas_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'bidang_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'alamat_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jns_kelamin' => 'required_if:jenis_entitas,perorangan',
            'no_identitas' => 'required_if:jenis_entitas,perorangan',
            'jns_identitas' => 'required_if:jenis_entitas,perorangan',
            'pekerjaan' => 'required_if:jenis_entitas,perorangan',
            'alamat' => 'required_if:jenis_entitas,perorangan',
            'tgl_pengajuan' => 'required|date',
            'jns_pengajuan' => 'required',
            'nominal_pengajuan' => 'required',
            'jw_pengajuan' => 'required',
            'jaminan' => 'required',
        ]);
        
        // Set status otomatis menjadi "Dalam Proses" (value = 1)
        $validated['status'] = '1';
        
        $validated['nominal_pengajuan'] = str_replace('.', '', $validated['nominal_pengajuan']);
        if (!is_numeric($validated['nominal_pengajuan'])) {
            return back()->withErrors(['nominal_pengajuan' => 'Nominal harus berupa angka.'])->withInput();
        }
        
        // Pastikan jw_pengajuan berakhiran ' BULAN'
        if (!empty($validated['jw_pengajuan'])) {
            $validated['jw_pengajuan'] = preg_replace('/\s*BULAN\s*$/i', '', $validated['jw_pengajuan']);
            $validated['jw_pengajuan'] = trim($validated['jw_pengajuan']) . ' BULAN';
        }
        
        // Set default value berdasarkan jenis entitas
        if ($validated['jenis_entitas'] === 'badan_usaha') {
            $validated['jns_kelamin'] = '-';
            $validated['nama'] = $validated['nama_badan_usaha'] ?? '-';
            $validated['no_identitas'] = null;
            $validated['jns_identitas'] = null;
            $validated['pekerjaan'] = null;
            $validated['alamat'] = null;
        } else {
            // Set default value untuk field badan usaha jika perorangan
            $validated['nama_badan_usaha'] = null;
            $validated['jenis_dokumen_usaha'] = null;
            $validated['nomor_legalitas_usaha'] = null;
            $validated['bidang_usaha'] = null;
            $validated['alamat_usaha'] = null;
        }
        
        // Untuk pendaftaran publik, gunakan user guest
        $guestUserId = DB::table('user')->where('username', 'Guest Registrant')->value('id');
        if (!$guestUserId) {
            $guestUserId = DB::table('user')->insertGetId([
                'username' => 'Guest Registrant',
                'email' => 'guest_'.uniqid().'@example.com',
                'password' => bcrypt(str()->random(16)),
                'nama' => 'Guest User',
                'nik' => '0000000000000000',
                'no_hp' => '000000000000',
                'online' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $validated['id_user'] = $guestUserId;
        $validated['status'] = $this->mapStatus($validated['status']);
        $validated['input_by'] = 'Public Registration';
        
        $this->createRegisterWithSequence($validated);
        return redirect()->route('register.public')->with('success', 'Pendaftaran berhasil! Data Anda sedang diproses.');
    }

    public function create()
    {
        // Arahkan ke index dan buka modal tambah data agar tidak perlu view create terpisah
        return redirect()->route('register.index')->with('open_modal', true);
    }

    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_register', 'create', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $validated = $request->validate([
            'nomor' => 'required',
            'nama' => 'required_if:jenis_entitas,perorangan',
            'jenis_entitas' => 'required|in:perorangan,badan_usaha',
            'nama_badan_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jenis_dokumen_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'nomor_legalitas_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'bidang_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'alamat_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jns_kelamin' => 'required_if:jenis_entitas,perorangan',
            'no_identitas' => 'required_if:jenis_entitas,perorangan',
            'jns_identitas' => 'required_if:jenis_entitas,perorangan',
            'pekerjaan' => 'required_if:jenis_entitas,perorangan',
            'alamat' => 'required_if:jenis_entitas,perorangan',
            'tgl_pengajuan' => 'required|date',
            'jns_pengajuan' => 'required',
            'nominal_pengajuan' => 'required',
            'jw_pengajuan' => 'required',
            'jaminan' => 'required', // sekarang wajib
        ]);
        
        // Set status otomatis menjadi "Dalam Proses" (value = 1)
        $validated['status'] = '1';
        
        $validated['nominal_pengajuan'] = str_replace('.', '', $validated['nominal_pengajuan']);
        if (!is_numeric($validated['nominal_pengajuan'])) {
            return back()->withErrors(['nominal_pengajuan' => 'Nominal harus berupa angka.'])->withInput();
        }
        // Pastikan jw_pengajuan berakhiran ' BULAN'
        if (!empty($validated['jw_pengajuan'])) {
            $validated['jw_pengajuan'] = preg_replace('/\s*BULAN\s*$/i', '', $validated['jw_pengajuan']);
            $validated['jw_pengajuan'] = trim($validated['jw_pengajuan']) . ' BULAN';
        }
        
        // Set default value berdasarkan jenis entitas
        if ($validated['jenis_entitas'] === 'badan_usaha') {
            $validated['jns_kelamin'] = '-';
            $validated['nama'] = $validated['nama_badan_usaha'] ?? '-';
            $validated['no_identitas'] = null;
            $validated['jns_identitas'] = null;
            $validated['pekerjaan'] = null;
            $validated['alamat'] = null;
        } else {
            // Set default value untuk field badan usaha jika perorangan
            $validated['nama_badan_usaha'] = null;
            $validated['jenis_dokumen_usaha'] = null;
            $validated['nomor_legalitas_usaha'] = null;
            $validated['bidang_usaha'] = null;
            $validated['alamat_usaha'] = null;
        }
        
        // Auto-increment id_user tanpa batas (tidak kembali ke 1)
        // $lastRegister = Register::orderBy('id_reg', 'desc')->first();
        // $nextUserId = $lastRegister ? $lastRegister->id_user + 1 : 1;
        // $validated['id_user'] = $nextUserId;
        // Tentukan id_user untuk penyimpanan publik maupun login
        $resolvedUserId = Auth::id();
        
        // Log untuk debugging
        Log::info('Creating register - Auth info', [
            'auth_id' => $resolvedUserId,
            'auth_user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'username' => Auth::user()->username,
                'nama' => Auth::user()->nama
            ] : 'Not authenticated',
            'session_id' => session()->getId()
        ]);
        
        // Pastikan id tersebut ada pada tabel 'user' yang menjadi FK
        $existsInUsers = $resolvedUserId ? DB::table('user')->where('id', $resolvedUserId)->exists() : false;
        if (!$existsInUsers) {
            // Ambil user pertama dari tabel 'user' sebagai fallback
            $fallbackId = DB::table('user')->orderBy('id')->value('id');
            if (!$fallbackId) {
                // Jika tabel 'user' kosong, buat satu akun placeholder
                $fallbackId = DB::table('user')->insertGetId([
                    'username' => 'Guest Registrant',
                    'email' => 'guest_'.uniqid().'@example.com',
                    'password' => bcrypt(str()->random(16)),
                    'nama' => 'Guest User',
                    'nik' => '0000000000000000',
                    'no_hp' => '000000000000',
                    'online' => false,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $resolvedUserId = $fallbackId;
        }
        $validated['id_user'] = $resolvedUserId;
        $validated['status'] = $this->mapStatus($validated['status']);
        
        // Tambahkan input_by untuk tracking siapa yang input data
        $user = Auth::user();
        $validated['input_by'] = $user ? $user->nama : 'System';
        
        $this->createRegisterWithSequence($validated);
        return redirect()->route('register.index')->with('success', 'Data berhasil ditambahkan!');
    }

    public function show($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa.');
        }
        $register = Register::findOrFail($id);
        // Simpan konteks register saat ini ke session untuk dipakai navigasi tanpa query param
        session(['current_register_id' => $register->id_reg]);
        // Kirim hanya register yang sedang dibuka ke view, agar dropdown di fitur lain hanya berisi register ini
        $registers = collect([$register]);
        return view('register.show', compact('register', 'registers'));
    }

    public function edit($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa.');
        }
        
        $register = Register::findOrFail($id);
        return view('register.edit', compact('register'));
    }

    /**
     * Render edit page using encrypted id on query string: /register/edit?id=<enc>
     */
    public function editSimple(Request $request)
    {
        $enc = (string) $request->query('id', '');
        if ($enc === '') {
            abort(404);
        }
        try {
            $decoded = strtr($enc, ['-' => '+', '_' => '/', '.' => '=']);
            $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
        } catch (\Throwable $e) {
            abort(404);
        }
        $register = Register::findOrFail($realId);
        return view('register.edit', compact('register'));
    }

    public function update(Request $request, $encrypted_id)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_register', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        try {
            $id = Crypt::decryptString($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa.');
        }
        
        $register = Register::findOrFail($id);
        $validated = $request->validate([
            'nomor' => 'required',
            'nama' => 'required_if:jenis_entitas,perorangan',
            'jenis_entitas' => 'required|in:perorangan,badan_usaha',
            'nama_badan_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jenis_dokumen_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'nomor_legalitas_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'bidang_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'alamat_usaha' => 'required_if:jenis_entitas,badan_usaha',
            'jns_kelamin' => 'required_if:jenis_entitas,perorangan',
            'no_identitas' => 'required_if:jenis_entitas,perorangan',
            'jns_identitas' => 'required_if:jenis_entitas,perorangan',
            'pekerjaan' => 'required_if:jenis_entitas,perorangan',
            'alamat' => 'required_if:jenis_entitas,perorangan',
            'tgl_pengajuan' => 'required|date',
            'jns_pengajuan' => 'required',
            'nominal_pengajuan' => 'required',
            'jw_pengajuan' => 'required',
            'jaminan' => 'required',
            'status' => 'required',
        ]);
        $validated['nominal_pengajuan'] = str_replace('.', '', $validated['nominal_pengajuan']);
        if (!is_numeric($validated['nominal_pengajuan'])) {
            return back()->withErrors(['nominal_pengajuan' => 'Nominal harus berupa angka.'])->withInput();
        }
        if (!empty($validated['jw_pengajuan'])) {
            $validated['jw_pengajuan'] = preg_replace('/\s*BULAN\s*$/i', '', $validated['jw_pengajuan']);
            $validated['jw_pengajuan'] = trim($validated['jw_pengajuan']) . ' BULAN';
        }
        
        // Set default value berdasarkan jenis entitas
        if ($validated['jenis_entitas'] === 'badan_usaha') {
            $validated['jns_kelamin'] = '-';
            $validated['nama'] = $validated['nama_badan_usaha'] ?? '-';
            $validated['no_identitas'] = null;
            $validated['jns_identitas'] = null;
            $validated['pekerjaan'] = null;
            $validated['alamat'] = null;
        } else {
            // Set default value untuk field badan usaha jika perorangan
            $validated['nama_badan_usaha'] = null;
            $validated['jenis_dokumen_usaha'] = null;
            $validated['nomor_legalitas_usaha'] = null;
            $validated['bidang_usaha'] = null;
            $validated['alamat_usaha'] = null;
        }
        
        // Kunci status selalu "Dalam Proses" (value = 1)
        $validated['status'] = '1';
        $register->update($validated);
        
        // Redirect dengan ID terenkripsi
        $encId = strtr(Crypt::encryptString($register->id_reg), ['+' => '-', '/' => '_', '=' => '.']);
        return redirect()->route('register.show', $encId)->with('success', 'Data berhasil diubah!');
    }

    /**
     * Aksi upload khusus admin update status, tanggal realisasi, nominal disetujui, dan upload file
     */
    public function aksiUpload(Request $request, $encrypted_id)
    {
        // Cek apakah user bisa upload realisasi
        if (!\App\Helpers\RoleHelper::canUploadRealisasi()) {
            return redirect()->route('login')->with('error', 'Akses ditolak. Hanya admin yang dapat upload realisasi.');
        }
        try {
            $id = \Illuminate\Support\Facades\Crypt::decryptString($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa.');
        }
        $register = Register::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|string',
            'tanggal_realisasi' => 'required|date',
            'nominal_disetujui' => 'required|numeric',
            'upload_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        if ($request->hasFile('upload_file')) {
            $path = $request->file('upload_file')->store('uploads', 'public');
            $validated['upload_file'] = $path;
        }
        $register->update($validated);
        return redirect()->back()->with('success', 'Perubahan status dan data berhasil disimpan!');
    }

    public function destroy($encrypted_id)
    {
        // Double check menu access dan permission untuk delete
        $accessCheck = $this->checkMenuAccess('menu_register', 'delete', request());
        if ($accessCheck) {
            return $accessCheck;
        }
        
        try {
            $id = Crypt::decryptString($encrypted_id);
        } catch (\Exception $e) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa.');
        }
        
        $register = Register::findOrFail($id);
        $register->delete();
        return redirect()->route('register.index')->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Mapping status register ke string konsisten
     */
    private function mapStatus($status)
    {
        $map = [
            '1' => 'Dalam Proses',
            'proses' => 'Dalam Proses',
            'dalam proses' => 'Dalam Proses',
            '2' => 'Menunggu Komite',
            'menunggu komite' => 'Menunggu Komite',
            '3' => 'Disetujui',
            'disetujui' => 'Disetujui',
            '4' => 'Ditolak',
            'ditolak' => 'Ditolak',
        ];
        return $map[strtolower($status)] ?? $status;
    }

    /**
     * Simpan register dengan nomor berurutan yang terkunci agar tidak duplikat.
     */
    private function createRegisterWithSequence(array $validated)
    {
        return DB::transaction(function () use ($validated) {
            $validated['nomor'] = $this->generateNextNomorRegistrasiLocked();
            return Register::create($validated);
        }, 3);
    }

    /**
     * Ambil nomor registrasi berikutnya dengan lock.
     */
    private function generateNextNomorRegistrasiLocked(): string
    {
        $year = date('Y');
        $lastThisYear = Register::where('nomor', 'like', '%/REG/' . $year)
            ->lockForUpdate()
            ->orderByDesc('id_reg')
            ->first();

        if ($lastThisYear && preg_match('/^(\d{3})\/REG\/' . $year . '$/', $lastThisYear->nomor, $matches)) {
            $nextNumber = str_pad(((int) $matches[1]) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "{$nextNumber}/REG/{$year}";
    }
}