<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Register;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log; // Logging error
use Illuminate\Support\Facades\Http; // Kirim ke n8n
use App\Helpers\RoleHelper;

class BankController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_bank', 'view', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $register_id = $request->query('register_id') ?: session('current_register_id');
        $query = Bank::query()->with('register');
        
        // Filter berdasarkan register_id jika ada
        if ($register_id) {
            $query->where('id_reg', $register_id);
        }
        
        // Filter berdasarkan nama nasabah jika ada
        if ($nama = $request->input('filter_nama')) {
            $query->whereHas('register', function($q) use ($nama) {
                $q->where('nama', 'like', '%' . $nama . '%');
            });
        }
        
        // Filter berdasarkan nama bank jika ada
        if ($bank = $request->input('filter_bank')) {
            $query->where('nama_bank', 'like', '%' . $bank . '%');
        }
        
        // Sorting (asc/desc) seperti SLIK
        $sort = $request->query('sort', '');
        $order = $request->query('order', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableMap = [
            'nama' => 'registers.nama',
            'nama_bank' => 'banks.nama_bank',
            'no_rekening' => 'banks.no_rekening',
            'file' => 'banks.file',
            'status' => 'banks.status',
            'hasil' => 'banks.hasil',
            'updated_at' => 'banks.updated_at',
        ];

        if (array_key_exists($sort, $sortableMap)) {
            if ($sort === 'nama') {
                // Join ke registers agar bisa sort berdasarkan nama nasabah
                $query->leftJoin('registers', 'registers.id_reg', '=', 'banks.id_reg')
                      ->select('banks.*')
                      ->orderBy($sortableMap[$sort], $order);
            } else {
                $query->orderBy($sortableMap[$sort], $order);
            }
        } else {
            $query->orderBy('banks.id_bank', 'asc');
        }

        // Handle per_page parameter
        $perPage = $request->get('per_page', 5);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        $bankData = $query->paginate($perPage)->withQueryString();
        
        // Dropdown register: jika ada register_id, hanya tampilkan register itu
        if ($register_id) {
            $registers = Register::where('id_reg', $register_id)->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        } else {
            $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        }
        
        // Ambil data register untuk modal dokumen jika ada register_id
        $register = null;
        if ($register_id) {
            $register = Register::find($register_id);
        }
        
        return view('bank.index', compact('bankData', 'registers', 'register'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Upload temporary file for new Bank record creation
     */
    public function uploadTempNew(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $tempPaths = [];
            foreach ($request->file('files') as $file) {
                $tempPath = $file->store('bank/tmp', 'public');
                $tempPaths[] = $tempPath;
            }
            
            return response()->json([
                'success' => true,
                'temp_paths' => $tempPaths,
                'message' => 'Files uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * UPDATED: Support Dynamic Python Automation
     */
    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_bank', 'create', $request);
        if ($accessCheck) { return $accessCheck; }

        // 1. Validasi Input
        $request->validate([
            'id_reg'      => 'required|integer|exists:registers,id_reg',
            'nama_bank'   => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            // Kita support single file untuk automation, atau array untuk manual upload
            'file'        => 'nullable|file|mimes:pdf|max:10240', 
            'files'       => 'nullable|array',
            'files.*'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $namaBank = $request->nama_bank;
        
        // 2. Cek apakah Bank ini punya Konfigurasi Otomatis (Mandiri/OCBC)
        $config = $this->getBankConfig($namaBank);

        // --- SKENARIO 1: BANK OTOMATIS (MANDIRI/OCBC) ---
        if ($config && ($request->hasFile('file') || $request->hasFile('files'))) {
            try {
                // Ambil file (prioritas 'file' tunggal, atau ambil index 0 dari 'files')
                $fileUpload = $request->file('file') ?? $request->file('files')[0];
                
                // Simpan PDF
                $fileName = strtolower($namaBank) . '_' . time() . '.pdf';
                $pdfPath = $fileUpload->storeAs($config['folder'], $fileName, 'public');

                // Simpan ke Database
                $bank = Bank::create([
                    'id_reg'      => $request->id_reg,
                    'nama_bank'   => $namaBank,
                    'no_rekening' => $request->no_rekening,
                    'file'        => $pdfPath, // Simpan sebagai string path tunggal
                    'status'      => 'Processing Python...',
                    'hasil'       => null
                ]);

                // Jalankan Helper: Python -> n8n
                $this->processAndSendToN8n($bank, $pdfPath, $config);

                return redirect()->route('bank.index')->with('success', "Data $namaBank berhasil ditambahkan & diproses otomatis!");

            } catch (\Exception $e) {
                return redirect()->route('bank.index')->with('error', "Gagal proses otomatis: " . $e->getMessage());
            }
        }

        // --- SKENARIO 2: BANK MANUAL / UPLOAD BIASA ---
        // (Logic asli kamu untuk handle array files & temp_paths)
        else {
            $validated = $request->only(['id_reg', 'nama_bank', 'no_rekening']);
            $validated['status'] = 'proses'; // Default status

            // Handle file uploads (Temp Paths / Direct)
            if ($request->filled('temp_paths')) {
                $tempPaths = array_filter($request->temp_paths ?? [], function ($p) {
                    return is_string($p) && $p !== '';
                });
                $filePaths = [];
                foreach ($tempPaths as $tempPath) {
                    if (Storage::disk('public')->exists($tempPath)) {
                        $newPath = str_replace('bank/tmp/', 'bank/files/', $tempPath);
                        Storage::disk('public')->move($tempPath, $newPath);
                        $filePaths[] = $newPath;
                    }
                }
                if (!empty($filePaths)) {
                    $validated['file'] = json_encode($filePaths); // Simpan sebagai JSON array
                }
            } elseif ($request->hasFile('files')) {
                $filePaths = [];
                foreach ($request->file('files') as $file) {
                    $filePaths[] = $file->store('bank/files', 'public');
                }
                $validated['file'] = json_encode($filePaths);
            }

            $validated['status'] = $this->mapStatus($validated['status']);
            Bank::create($validated);

            return redirect()->route('bank.index')->with('success', 'Data Bank berhasil ditambahkan (Manual)!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try { 
            $decoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
            $decrypted = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded); 
        } catch (\Exception $e) { 
            $decrypted = $id; 
        }
        $bank = Bank::with('register')->findOrFail($decrypted);
        return view('bank.show', compact('bank'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        try { 
            $decoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
            $decrypted = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded); 
        } catch (\Exception $e) { $decrypted = $id; }
        $bank = Bank::findOrFail($decrypted);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        
        return view('bank.edit', compact('bank', 'registers'));
    }

    /**
     * Edit dengan URL sederhana
     */
    public function editSimple(Request $request)
    {
        $enc = (string) $request->query('id', '');
        if ($enc === '') abort(404);
        try {
            $decoded = strtr($enc, ['-' => '+', '_' => '/', '.' => '=']);
            $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
        } catch (\Throwable $e) {
            abort(404);
        }
        $bank = Bank::findOrFail($realId);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        return view('bank.edit', compact('bank', 'registers'));
    }

    /**
     * Update the specified resource in storage.
     * UPDATED: Support Dynamic Python Automation on File Update
     */
    public function update(Request $request, string $id)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_bank', 'edit', $request);
        if ($accessCheck) { return $accessCheck; }
        
        $bank = Bank::findOrFail($id);
        
        // Cek Config Bank (Apakah ini Mandiri/OCBC?)
        $config = $this->getBankConfig($bank->nama_bank);

        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|min:8|max:20',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'temp_path' => 'nullable|string',
        ]);

        // Map nama field
        $validated['no_rekening'] = $validated['nomor_rekening'];
        unset($validated['nomor_rekening']);
        
        $newFilePath = null;

        // 1. Handle File Baru (Direct Upload)
        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($bank->file && !is_array(json_decode($bank->file, true)) && Storage::disk('public')->exists($bank->file)) {
                Storage::disk('public')->delete($bank->file);
            }
            // Tentukan folder penyimpanan (sesuai config jika ada, atau default)
            $folder = $config ? $config['folder'] : 'bank/files';
            $fileName = $config ? (strtolower($bank->nama_bank) . '_upd_' . time() . '.pdf') : null;
            
            $newFilePath = $request->file('file')->storeAs($folder, $fileName ?? $request->file('file')->hashName(), 'public');
        }

        // 2. Handle File Baru (AJAX Temp Path)
        elseif ($request->filled('temp_path')) {
            $temp = ltrim($request->input('temp_path'), '/');
            if (Storage::disk('public')->exists($temp)) {
                $folder = $config ? $config['folder'] : 'bank/files';
                $finalName = basename($temp);
                $final = $folder . '/' . $finalName;
                
                Storage::disk('public')->makeDirectory($folder);
                Storage::disk('public')->move($temp, $final);
                
                if ($bank->file && !is_array(json_decode($bank->file, true)) && Storage::disk('public')->exists($bank->file)) {
                    Storage::disk('public')->delete($bank->file);
                }
                $newFilePath = $final;
            }
        }

        // 3. Logic Update
        if ($newFilePath) {
            $validated['file'] = $newFilePath;
            
            // JIKA BANK OTOMATIS & FILE BERUBAH -> TRIGGER PYTHON ULANG
            if ($config) {
                $validated['status'] = 'Processing Python (Update)...';
                $validated['hasil'] = null; // Reset hasil lama
                $bank->update($validated); // Simpan dulu path barunya

                try {
                    // Jalankan ulang Python -> n8n
                    $this->processAndSendToN8n($bank, $newFilePath, $config);
                    $msg = "File diperbarui & diproses ulang otomatis!";
                } catch (\Exception $e) {
                    $msg = "File diperbarui tapi gagal proses ulang: " . $e->getMessage();
                }
            } else {
                // Bank Manual
                $bank->update($validated);
                $msg = "Data Bank & File berhasil diubah!";
            }
        } else {
            // Tidak ada file berubah, cuma update data teks
            if ($request->filled('status')) {
                $validated['status'] = $this->mapStatus($request->input('status'));
            }
            $bank->update($validated);
            $msg = "Data Bank berhasil diubah!";
        }

        // Redirect URL Encrypted
        $enc = Crypt::encryptString($bank->id_bank);
        $urlSafe = strtr($enc, ['+' => '-', '/' => '_', '=' => '.']);
        $detailUrl = route('bank.show', $urlSafe);
        if ($request->has('register_id')) { 
            $detailUrl .= '?register_id=' . $request->register_id; 
        }
        
        return redirect($detailUrl)->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'delete', $request);
        if ($accessCheck) { return $accessCheck; }
        
        $bank = Bank::findOrFail($id);
        
        // Delete files
        if ($bank->file) {
            $files = json_decode($bank->file, true);
            if (is_array($files)) {
                foreach ($files as $f) {
                    if (Storage::disk('public')->exists($f)) Storage::disk('public')->delete($f);
                }
            } elseif (Storage::disk('public')->exists($bank->file)) {
                Storage::disk('public')->delete($bank->file);
            }
        }
        if ($bank->hasil && Storage::disk('public')->exists($bank->hasil)) {
            Storage::disk('public')->delete($bank->hasil);
        }
        
        $bank->delete();
        return redirect()->route('bank.index')->with('success', 'Data Bank berhasil dihapus!');
    }

    // =========================================================================
    // HELPER FUNCTIONS & WEBHOOK HANDLER (NEW FEATURES)
    // =========================================================================

    /**
     * CORE LOGIC: Menjalankan Script Python & Kirim ke n8n
     */
    private function processAndSendToN8n($bank, $pdfPath, $config)
    {
        // Setup Paths
        $excelName = strtolower($bank->nama_bank) . '_res_' . time() . '.xlsx';
        $fullPdfPath = Storage::disk('public')->path($pdfPath);
        $fullExcelPath = Storage::disk('public')->path($config['folder'] . '/' . $excelName);
        
        // Ambil path script python
        $scriptPath = base_path($config['script_py']);

        // 1. Eksekusi Python (OCR Summary)
        if (!file_exists($scriptPath)) {
            Log::error("Script Python tidak ditemukan di: " . $scriptPath);
            throw new \Exception("Script Python tidak ditemukan.");
        }

        // Command: python "path/script.py" "path/input.pdf" "path/output.xlsx"
        $command = "python \"{$scriptPath}\" \"{$fullPdfPath}\" \"{$fullExcelPath}\"";
        $output = shell_exec($command);

        // 2. Cek Hasil Python
        if (str_contains($output, 'SUCCESS') && file_exists($fullExcelPath)) {
            
            // 3. Kirim ke n8n (Async / Fire and Forget)
            try {
                Http::timeout(30)
                    ->attach('file_excel', fopen($fullExcelPath, 'r'), 'summary.xlsx')
                    ->post($config['n8n_url'], [
                        'id_bank' => $bank->id_bank ?? $bank->id // Pastikan ID terkirim
                    ]);
                
                $bank->update(['status' => 'Dikirim ke n8n']);
            
            } catch (\Exception $e) {
                Log::error("Gagal koneksi n8n: " . $e->getMessage());
                // Jangan throw error ke user, biarkan status di 'Processing Python' atau update manual
            }

        } else {
            $bank->update(['status' => 'Gagal OCR']);
            Log::error("Python Error ({$bank->nama_bank}): $output");
            throw new \Exception("Gagal memproses OCR Python. Cek Log.");
        }
    }

    /**
     * WEBHOOK HANDLER: Menerima hasil balik dari n8n
     * Route: POST /api/webhook/bank-result (pastikan route ini ada di api.php atau web.php)
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Validasi parameter wajib
            if (!$request->has('id_bank') || !$request->hasFile('file_hasil')) {
                return response()->json(['status' => 'error', 'message' => 'Data kurang'], 400);
            }

            $id = $request->input('id_bank');
            $file = $request->file('file_hasil');

            $bank = Bank::find($id);
            if (!$bank) return response()->json(['status' => 'error', 'message' => 'Bank not found'], 404);

            // Simpan PDF Hasil Akhir
            $path = $file->store('bank/hasil', 'public');

            // Update Database
            $bank->update([
                'hasil'  => $path,
                'status' => 'Selesai' // atau 'Valid'
            ]);

            return response()->json(['status' => 'success', 'message' => 'Data updated']);

        } catch (\Exception $e) {
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * CONFIG MAP: Menentukan Script & URL berdasarkan Nama Bank
     */
    private function getBankConfig($namaBank)
    {
        return match (strtoupper($namaBank)) {
            'MANDIRI' => [
                'script_py' => 'python_script/mandiri.py',
                'n8n_url'   => 'https://n8n.gusaha.id/webhook/9eb723cb-d272-4f78-9a8d-30f00b71c771',
                'folder'    => 'bank/mandiri',
            ],
            'OCBC' => [
                'script_py' => 'python_script/ocbc.py',
                'n8n_url'   => 'https://n8n.itsaimen.my.id/webhook/9eb723cb-d272-4f78-9a8d-30f00b71c771', 
                'folder'    => 'bank/ocbc',
            ],
            default => null,
        };
    }

    // =========================================================================
    // END NEW FEATURES
    // =========================================================================

    /**
     * Upload/Update dokumen hasil Bank dari tombol aksi (Manual Override)
     */
    public function uploadHasil(Request $request, string $id)
    {
        $request->validate([
            'hasil' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'temp_path' => 'nullable|string',
        ]);

        $bank = Bank::findOrFail($id);
        $update = [];

        if ($request->filled('temp_path')) {
            $temp = ltrim($request->input('temp_path'), '/');
            if (Storage::disk('public')->exists($temp)) {
                $final = 'bank/hasil/' . basename($temp);
                Storage::disk('public')->makeDirectory('bank/hasil');
                Storage::disk('public')->move($temp, $final);
                $update['hasil'] = $final;
            }
        } elseif ($request->hasFile('hasil')) {
            if ($bank->hasil && Storage::disk('public')->exists($bank->hasil)) {
                Storage::disk('public')->delete($bank->hasil);
            }
            $path = $request->file('hasil')->store('bank/hasil', 'public');
            $update['hasil'] = $path;
        }

        if (!empty($update['hasil'])) {
            $update['status'] = 'valid';
        }
        if (!empty($update)) {
            $bank->update($update);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Hasil berhasil diperbarui', 'data' => $bank->fresh()]);
        }
        return redirect()->route('bank.index')->with('success', 'Hasil berhasil diperbarui');
    }

    /**
     * Background upload untuk hasil Bank manual
     */
    public function uploadTempHasil(Request $request, string $id)
    {
        $request->validate([
            'hasil' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $tmp = $request->file('hasil')->store('bank/hasil_tmp', 'public');
            return response()->json(['success' => true, 'temp_path' => $tmp]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Stream file safely
     */
    public function viewFile(string $id, ?int $index = 0)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->file) abort(404);

        $files = json_decode($bank->file, true);
        $path = is_array($files) ? ($files[$index ?? 0] ?? null) : $bank->file;
        
        if (empty($path) || !Storage::disk('public')->exists($path)) abort(404);

        $disk = Storage::disk('public');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) { 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png', 'pdf'=>'application/pdf', default=>'application/octet-stream' };
        
        return response()->stream(function () use ($disk, $path) {
            fpassthru($disk->readStream($path));
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function viewHasil(string $id)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->hasil || !Storage::disk('public')->exists($bank->hasil)) abort(404);
        
        $path = $bank->hasil;
        $disk = Storage::disk('public');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) { 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png', 'pdf'=>'application/pdf', default=>'application/octet-stream' };
        
        return response()->stream(function () use ($disk, $path) {
            fpassthru($disk->readStream($path));
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    private function mapStatus($status)
    {
        $map = [
            '1' => 'Dalam Proses', 'proses' => 'Dalam Proses', 'dalam proses' => 'Dalam Proses',
            '2' => 'Valid', 'valid' => 'Valid',
            '3' => 'Tidak Valid', 'tidak valid' => 'Tidak Valid', 'tidak_valid' => 'Tidak Valid', 'tidakvalid' => 'Tidak Valid',
        ];
        return $map[strtolower($status)] ?? $status;
    }
}