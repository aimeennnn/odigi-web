<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Register;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Helpers\RoleHelper;
use Illuminate\Support\Facades\Log;

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
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
     */
    public function store(Request $request)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'create', $request);
        if($accessCheck){
            return $accessCheck;
        }

        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string|max:225',
            'no_rekening' => 'required|string|max:225',
            'files' => 'nullable|array',
            'files.*' => 'nullable|files|mimes:pdf,jpg,jpeg,png|max:5120',
            'temp_paths' => 'nullable|array',
            'temp_paths.*' => 'nullable|string',
        ]);

        $filePaths = [];
        $hasilPaths = [];

        if($request->filled('temp_paths')){
            $tempPaths = array_filter($request->temp_paths ?? [], function ($p) {
              return is_string($p) && $p !== '';  
            });
            foreach($tempPaths as $tempPath) {
                if (Storage::disk('public')->exists($tempPath)){
                    $newPath = str_replace('bank/tmp/', 'bank/files/', $tempPath);
                    Storage::disk('public')->move($tempPath, $newPath);
                    $filePaths[] = $newPath;
                }
            }
        }elseif ($request->hasFile('files')) {
            foreach ( $request->file('files') as $file) {
                $filePaths[] = $file->store('bank/files', 'public');
            }
        }

        //proses ekstraksi python
        foreach($filePaths as $path) {
            $absolutePath = storage_path('app/public/' . $path);

            $csvResult = $this->processOcr($absolutePath);
            if($csvResult){
                $hasilPaths[] = $csvResult;
            }
        }

        //menyimpan file ke database
        if (!empty($filePaths)) {
            $validated['file'] = json_encode($filePaths);
        }else{
            $validated['file'] = null;
        }

        if (!empty($hasilPaths)) {
            $validated['hasil'] = json_encode($hasilPaths);
            $validated['status'] = 'Valid';
        }else {
            $validated['hasil'] = null;
            $validated['status'] = $this->mapStatus('proses');
        }

        Bank::create($validated);

        return redirect()->route('bank.index')->with('success', 'Data Bank Berhasil ditambahkan & diproses OCR!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // dukung ID terenkripsi URL-safe
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
     * Edit dengan URL sederhana: /bank/edit?id=<enc>
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
    
    public function destroy(Request $request, string $id)
    {
        // 1. Cek Permission (Keamanan)
        $accessCheck = $this->checkMenuAccess('menu_bank', 'delete', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $bank = Bank::findOrFail($id);
        
        // 2. Hapus File PDF (Support format Array JSON baru & String lama)
        if ($bank->file) {
            // Coba decode JSON (karena kode store baru menyimpan sebagai array)
            $files = json_decode($bank->file, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($files)) {
                // Hapus banyak file
                foreach ($files as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            } else {
                // Fallback: Hapus single file (data lama)
                if (Storage::disk('public')->exists($bank->file)) {
                    Storage::disk('public')->delete($bank->file);
                }
            }
        }

        // 3. Hapus File CSV Hasil OCR (Penting agar server tidak penuh)
        if ($bank->hasil && Storage::disk('public')->exists($bank->hasil)) {
            Storage::disk('public')->delete($bank->hasil);
        }
        
        // 4. Hapus Data dari Database
        $bank->delete();
        
        return redirect()->route('bank.index')->with('success', 'Data Bank dan filenya berhasil dihapus!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id){
        //Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_bank', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $bank = Bank::findOrFail($id);
        
        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|min:8|max:20',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Map nama field input ke kolom database
        $validated['no_rekening'] = $validated['nomor_rekening'];
        unset($validated['nomor_rekening']);

        $newFilePath = null;

        // 1. Handle File Upload Baru (Upload Langsung)
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada (Opsional, uncomment jika ingin hapus)
            // if ($bank->file && Storage::disk('public')->exists($bank->file)) {
            //     Storage::disk('public')->delete($bank->file);
            // }
            $newFilePath = $request->file('file')->store('bank/files', 'public');
            $validated['file'] = $newFilePath;
        }

        // 2. Handle File Upload Baru (Via Temp/AJAX)
        if ($request->filled('temp_path')) {
            $temp = ltrim($request->input('temp_path'), '/');
            if (Storage::disk('public')->exists($temp)) {
                $final = 'bank/files/' . basename($temp);
                Storage::disk('public')->makeDirectory('bank/files');
                Storage::disk('public')->move($temp, $final);
                
                // Hapus file lama jika ada
                if ($bank->file && Storage::disk('public')->exists($bank->file)) {
                     Storage::disk('public')->delete($bank->file);
                }
                
                $newFilePath = $final;
                $validated['file'] = $final;
            }
        }

        // 3. JIKA ADA FILE BARU -> JALANKAN OCR ULANG
        if ($newFilePath) {
            $absolutePath = storage_path('app/public/' . $newFilePath);
            
            // Panggil fungsi OCR
            $csvResult = $this->processOcr($absolutePath);
            
            if ($csvResult) {
                // Update kolom hasil. Asumsi kolom 'hasil' menyimpan string tunggal untuk edit ini.
                // Jika database kamu support array JSON di update, sesuaikan jadi json_encode([$csvResult])
                $validated['hasil'] = $csvResult;
                $validated['status'] = 'Valid';
            }
        }

        if ($request->filled('status')) {
            $validated['status'] = $this->mapStatus($request->input('status'));
        }

        $bank->update($validated);

        // Redirect Encrypted
        $enc = Crypt::encryptString($bank->id_bank);
        $urlSafe = strtr($enc, ['+' => '-', '/' => '_', '=' => '.']);
        $detailUrl = route('bank.show', $urlSafe);
        
        $append = [];
        if ($request->has('register_id')) { $append['register_id'] = $request->register_id; }
        if (!empty($append)) { $detailUrl .= '?'.http_build_query($append); }
        
        return redirect($detailUrl)->with('success', 'Data Bank berhasil diubah & diproses ulang!');
    }

    /**
     * Upload/Update dokumen hasil Bank dari tombol aksi, sekaligus update status (opsional).
     */
    public function uploadHasil(Request $request, string $id)
    {
        $request->validate([
            'hasil' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'temp_path' => 'nullable|string',
        ]);

        $bank = Bank::findOrFail($id);

        $update = [];

        if ($request->filled('temp_path')) {
            // Finalize from temporary storage
            $temp = ltrim($request->input('temp_path'), '/');
            if (Storage::disk('public')->exists($temp)) {
                $final = 'bank/hasil/' . basename($temp);
                Storage::disk('public')->makeDirectory('bank/hasil');
                Storage::disk('public')->move($temp, $final);
                $update['hasil'] = $final;
            }
        } elseif ($request->hasFile('hasil')) {
            // Hapus file lama jika ada
            if ($bank->hasil && Storage::disk('public')->exists($bank->hasil)) {
                Storage::disk('public')->delete($bank->hasil);
            }
            $path = $request->file('hasil')->store('bank/hasil', 'public');
            $update['hasil'] = $path;
        }

        // Otomatis set status menjadi "Valid" ketika upload hasil
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
     * Background upload untuk hasil Bank, menyimpan sementara lalu mengembalikan temp_path.
     */
    public function uploadTempHasil(Request $request, string $id)
    {
        $request->validate([
            'hasil' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $tmp = $request->file('hasil')->store('bank/hasil_tmp', 'public');
            return response()->json([
                'success' => true,
                'temp_path' => $tmp,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload sementara: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream file safely from storage to the browser (image/PDF/other)
     */
    public function viewFile(string $id, ?int $index = 0)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->file) {
            abort(404);
        }

        // If multiple files stored as JSON, open the first one
        $files = json_decode($bank->file, true);
        $path = is_array($files) ? ($files[$index ?? 0] ?? null) : $bank->file;
        if (empty($path)) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }

        // Basic mime detection fallback by extension
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }
        $filename = basename($path);
        $stream = $disk->readStream($path);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    public function viewHasil(string $id)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->hasil) {
            abort(404);
        }
        $path = $bank->hasil;
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }
        $filename = basename($path);
        $stream = $disk->readStream($path);
        if ($stream === false) { abort(404); }
        return response()->stream(function () use ($stream) { fpassthru($stream); }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    /**
     * Mapping status bank ke string konsisten
     */
    private function mapStatus($status)
    {
        $map = [
            '1' => 'Dalam Proses',
            'proses' => 'Dalam Proses',
            'dalam proses' => 'Dalam Proses',
            '2' => 'Valid',
            'valid' => 'Valid',
            '3' => 'Tidak Valid',
            'tidak valid' => 'Tidak Valid',
            'tidak_valid' => 'Tidak Valid',
            'tidakvalid' => 'Tidak Valid',
        ];
        return $map[strtolower($status)] ?? $status;
    }

    /**
     * FUNGSI KHUSUS: Menjalankan Python OCR
     * Lokasi Script: root/python_scripts/ocr_processor.py
     */
    private function processOcr($pdfPath)
    {
        // 1. Cek File PDF
        if (!file_exists($pdfPath)) {
            Log::warning("OCR Skipped: File PDF tidak ditemukan di $pdfPath");
            return null;
        }

        // 2. Siapkan Output CSV
        $fileName = pathinfo($pdfPath, PATHINFO_FILENAME);
        // Tambahkan timestamp agar unik
        $csvName = $fileName . '_' . time() . '_hasil.csv';
        
        // Folder tujuan: storage/app/public/bank/hasil_ocr
        $outputFolder = 'bank/hasil_ocr';
        Storage::disk('public')->makeDirectory($outputFolder);
        
        $csvRelativePath = $outputFolder . '/' . $csvName;
        // Gunakan helper Storage agar path-nya otomatis ikut settingan disk 'public' kamu
        $csvAbsolutePath = Storage::disk('public')->path($csvRelativePath);
        
        
        // 3. Panggil Python Script

        // PERHATIKAN: Path ini mengarah ke folder python_scripts yang baru kamu buat
        $scriptPath = base_path('python_script/ocbc.py');

        try {
            // Command: python script.py [INPUT_PDF] [OUTPUT_CSV]
            // Jika di server produksi pakai 'python3', ganti string 'python' di bawah
            $process = new Process([
                'python', 
                $scriptPath, 
                $pdfPath, 
                $csvAbsolutePath
            ]);
            
            $process->setTimeout(300); // 5 Menit (jaga-jaga file besar)
            $process->mustRun(); // Eksekusi

            // Cek Output dari Python
            $output = json_decode($process->getOutput(), true);
            
            if (isset($output['status']) && $output['status'] === 'success') {
                Log::info("OCR Sukses! CSV tersimpan di: $csvRelativePath");
                return $csvRelativePath; // Kembalikan path relative untuk DB
            } else {
                Log::error("OCR Error (Script): " . ($output['message'] ?? 'Unknown'));
                return null;
            }

        } catch (\Exception $e) {
            Log::error("OCR Error (System): " . $e->getMessage());
            return null;
        }
    }
}