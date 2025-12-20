<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Register;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process; 
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Helpers\RoleHelper;

class BankController extends BaseController
{
    // --- INDEX (TIDAK BERUBAH) ---
    public function index(Request $request)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'view', $request);
        if ($accessCheck) return $accessCheck;

        $register_id = $request->query('register_id') ?: session('current_register_id');
        $query = Bank::query()->with('register');
        
        if ($register_id) $query->where('id_reg', $register_id);
        if ($nama = $request->input('filter_nama')) $query->whereHas('register', fn($q) => $q->where('nama', 'like', '%' . $nama . '%'));
        if ($bank = $request->input('filter_bank')) $query->where('nama_bank', 'like', '%' . $bank . '%');
        
        $sort = $request->query('sort', '');
        $order = $request->query('order', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableMap = [
            'nama' => 'registers.nama', 'nama_bank' => 'banks.nama_bank',
            'no_rekening' => 'banks.no_rekening', 'file' => 'banks.file',
            'status' => 'banks.status', 'hasil' => 'banks.hasil', 'updated_at' => 'banks.updated_at'
        ];

        if (array_key_exists($sort, $sortableMap)) {
            if ($sort === 'nama') $query->leftJoin('registers', 'registers.id_reg', '=', 'banks.id_reg')->select('banks.*')->orderBy($sortableMap[$sort], $order);
            else $query->orderBy($sortableMap[$sort], $order);
        } else {
            $query->orderBy('banks.id_bank', 'asc');
        }

        $bankData = $query->paginate($request->get('per_page', 5))->withQueryString();
        $registers = $register_id ? Register::where('id_reg', $register_id)->get() : Register::orderBy('id_reg', 'desc')->get();
        $register = $register_id ? Register::find($register_id) : null;
        
        return view('bank.index', compact('bankData', 'registers', 'register'));
    }

    // --- UPLOAD TEMP (TIDAK BERUBAH) ---
    public function uploadTempNew(Request $request)
    {
        $request->validate(['files' => 'required|array', 'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $tempPaths = [];
        foreach ($request->file('files') as $file) $tempPaths[] = $file->store('bank/tmp', 'public');
        return response()->json(['success' => true, 'temp_paths' => $tempPaths]);
    }

    // ==========================================================
    // STORE (GABUNGAN: OCBC LAMA + MANDIRI BARU)
    // ==========================================================
    public function store(Request $request)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'create', $request);
        if ($accessCheck) return $accessCheck;

        $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string',
            'no_rekening' => 'required|string',
            'files' => 'nullable|array',
            'file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $namaBank = $request->nama_bank;

        // 1. Handle File Upload
        $filePaths = [];
        $folder = (strtoupper($namaBank) == 'MANDIRI') ? 'bank/mandiri' : 'bank/files'; // Pisahkan folder biar rapi

        if ($request->hasFile('file')) {
            $filePaths[] = $request->file('file')->store($folder, 'public');
        } elseif ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store($folder, 'public');
            }
        } elseif ($request->filled('temp_paths')) {
            foreach ($request->temp_paths as $tmp) {
                if (Storage::disk('public')->exists($tmp)) {
                    $new = str_replace('bank/tmp/', $folder . '/', $tmp);
                    Storage::disk('public')->move($tmp, $new);
                    $filePaths[] = $new;
                }
            }
        }

        // 2. Simpan ke Database
        $bank = Bank::create([
            'id_reg' => $request->id_reg,
            'nama_bank' => $namaBank,
            'no_rekening' => $request->no_rekening,
            'file' => json_encode($filePaths),
            'status' => 'Processing Python...', // Status awal
            'hasil' => null
        ]);

        // 3. JALANKAN LOGIKA SESUAI BANK
        foreach ($filePaths as $path) {
            $fullPdfPath = Storage::disk('public')->path($path);
            $hasilExcel = null;
            $n8nUrl = null;

            // === JALUR OCBC (LAMA & TERBUKTI) ===
            if (strtoupper($namaBank) == 'OCBC') {
                $hasilExcel = $this->processOcrOcbc($fullPdfPath);
                $n8nUrl = 'https://n8n.gusaha.id/webhook/0e8f5d1f-f831-4320-a464-8630df2d9866'; // URL OCBC
            }
            // === JALUR MANDIRI (BARU) ===
            elseif (strtoupper($namaBank) == 'MANDIRI') {
                $hasilExcel = $this->processOcrMandiri($fullPdfPath);
                // Ganti URL ini dengan URL Webhook Mandiri kamu yang BEDA
                $n8nUrl = 'https://n8n.gusaha.id/webhook/beaad1c3-270d-47c8-98b9-9d191ab6dc40'; 
            }

            // Kirim ke n8n jika Python Sukses
            if ($hasilExcel && $n8nUrl) {
                $this->sendToN8n($hasilExcel, $bank->id_bank ?? $bank->id, $n8nUrl);
                $bank->update(['status' => 'Sedang Diproses n8n']); // Notif Status
            } else {
                $bank->update(['status' => 'Gagal OCR']);
                Log::error("Gagal OCR untuk $namaBank ID: " . $bank->id);
            }
        }

        return redirect()->route('bank.index')->with('success', "Data $namaBank berhasil diupload! Proses ekstraksi sedang berjalan di background (n8n).");
    }

    // ==========================================================
    // UPDATE
    // ==========================================================
    public function update(Request $request, string $id)
    {
        $bank = Bank::findOrFail($id);
        
        $validated = $request->validate([
            'id_reg' => 'required|integer',
            'nama_bank' => 'required|string',
            'nomor_rekening' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $bank->nama_bank = $validated['nama_bank'];
        $bank->no_rekening = $validated['nomor_rekening'];

        // Jika ada file baru
        if ($request->hasFile('file')) {
            $folder = (strtoupper($bank->nama_bank) == 'MANDIRI') ? 'bank/mandiri' : 'bank/files';
            $path = $request->file('file')->store($folder, 'public');
            
            $bank->file = json_encode([$path]);
            $bank->status = 'Processing Python (Update)...';
            $bank->save();

            // Jalankan OCR Ulang
            $fullPdfPath = Storage::disk('public')->path($path);
            $hasilExcel = null;
            $n8nUrl = null;

            if (strtoupper($bank->nama_bank) == 'OCBC') {
                $hasilExcel = $this->processOcrOcbc($fullPdfPath);
                $n8nUrl = 'https://n8n.gusaha.id/webhook/0e8f5d1f-f831-4320-a464-8630df2d9866';
            } elseif (strtoupper($bank->nama_bank) == 'MANDIRI') {
                $hasilExcel = $this->processOcrMandiri($fullPdfPath);
                $n8nUrl = 'https://n8n.gusaha.id/webhook/beaad1c3-270d-47c8-98b9-9d191ab6dc40';
            }

            if ($hasilExcel && $n8nUrl) {
                $this->sendToN8n($hasilExcel, $bank->id_bank ?? $bank->id, $n8nUrl);
                $bank->update(['status' => 'Sedang Diproses n8n']);
                return redirect()->back()->with('success', 'File diperbarui & sedang diproses ulang oleh n8n!');
            } else {
                $bank->update(['status' => 'Gagal OCR']);
                return redirect()->back()->with('error', 'Gagal memproses Python.');
            }
        }

        $bank->save();
        return redirect()->back()->with('success', 'Data diupdate!');
    }


    // ==========================================================
    // CORE LOGIC (PROCESSORS)
    // ==========================================================
    
    /**
     * PROCESSOR OCBC (Yang Lama & Stabil)
     */
    private function processOcrOcbc($pdfPath)
    {
        if (!file_exists($pdfPath)) return null;
        $xlsxName = 'ocbc_' . time() . '.xlsx';
        $outputFolder = 'bank/hasil_ocr';
        Storage::disk('public')->makeDirectory($outputFolder);
        
        $xlsxAbsolutePath = Storage::disk('public')->path($outputFolder . '/' . $xlsxName);
        $scriptPath = base_path('python_script/ocbc.py'); 

        try {
            $process = new Process(['python', $scriptPath, $pdfPath, $xlsxAbsolutePath]);
            $process->setTimeout(300); 
            $process->mustRun();
            
            // Cek output JSON gaya lama
            $result = json_decode($process->getOutput(), true);
            if (isset($result['status']) && $result['status'] === 'success') {
                return $outputFolder . '/' . $xlsxName;
            }
            return null;

        } catch (\Exception $e) {
            Log::error("OCBC Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * PROCESSOR MANDIRI (Baru)
     */
    private function processOcrMandiri($pdfPath)
    {
        if (!file_exists($pdfPath)) return null;
        $xlsxName = 'mandiri_' . time() . '.xlsx';
        $outputFolder = 'bank/mandiri/hasil'; // Folder khusus mandiri
        Storage::disk('public')->makeDirectory($outputFolder);
        
        $xlsxAbsolutePath = Storage::disk('public')->path($outputFolder . '/' . $xlsxName);
        $scriptPath = base_path('python_script/mandiri.py'); 

        try {
            // Jalankan Python Mandiri
            // Pastikan script mandiri.py sudah bisa terima sys.argv[1] (input) dan sys.argv[2] (output)
            $process = new Process(['python', $scriptPath, $pdfPath, $xlsxAbsolutePath]);
            $process->setTimeout(300); 
            $process->mustRun();
            
            $output = $process->getOutput();
            
            // Mandiri outputnya TEXT "SUCCESS" (bukan JSON)
            if (str_contains(strtoupper($output), 'SUCCESS') && file_exists($xlsxAbsolutePath)) {
                return $outputFolder . '/' . $xlsxName;
            }
            return null;

        } catch (\Exception $e) {
            Log::error("Mandiri Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * PENGIRIM KE N8N (Universal)
     */
    private function sendToN8n($relativePath, $bankId, $n8nUrl)
    {
        $absolutePath = Storage::disk('public')->path($relativePath);
        if (!file_exists($absolutePath)) return;

        try {
            Http::timeout(30)
                ->attach('file_excel', fopen($absolutePath, 'r'), 'summary.xlsx')
                ->post($n8nUrl, [
                    'id_bank' => $bankId
                ]);
            
            Log::info("Sukses kirim ke n8n. BankID: $bankId");

        } catch (\Exception $e) {
            Log::error("Gagal kirim ke n8n: " . $e->getMessage());
        }
    }

    // --- WEBHOOK HANDLER ---
    public function handleWebhook(Request $request)
    {
        try {
            if (!$request->has('id_bank') || !$request->hasFile('file_hasil')) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
            }

            $id = $request->input('id_bank');
            $file = $request->file('file_hasil');
            
            // PERBAIKAN DISINI:
            // Hapus "orWhere('id', ...)" karena kolom 'id' tidak ada di tabelmu.
            // Cukup cari berdasarkan 'id_bank' saja.
            $bank = Bank::where('id_bank', $id)->first();
            
            if ($bank) {
                $path = $file->store('bank/hasil', 'public');
                
                $bank->update([
                    'hasil'  => $path,
                    'status' => 'Selesai' // Status Akhir
                ]);

                return response()->json(['status' => 'success', 'message' => 'Berhasil update hasil']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Bank ID tidak ditemukan'], 404);
            }

        } catch (\Exception $e) {
            Log::error("Webhook Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // --- HELPER LAIN ---
    public function show(Request $request, string $id) { 
        try { $id = Crypt::decryptString(strtr($id, ['-'=>'+','_'=>'/', '.'=>'='])); } catch(\Exception $e){}
        $bank = Bank::with('register')->findOrFail($id);
        return view('bank.show', compact('bank'));
    }
    public function destroy(Request $request, string $id) {
        $bank = Bank::findOrFail($id);
        $bank->delete();
        return redirect()->route('bank.index')->with('success', 'Dihapus');
    }
    public function edit(Request $request, string $id) {
        try { $id = Crypt::decryptString(strtr($id, ['-'=>'+','_'=>'/', '.'=>'='])); } catch(\Exception $e){}
        $bank = Bank::findOrFail($id);
        $registers = Register::all();
        return view('bank.edit', compact('bank', 'registers'));
    }
     public function viewFile(string $id, ?int $index = 0) {
        $bank = Bank::findOrFail($id);
        if (!$bank->file) abort(404);
        $files = json_decode($bank->file, true);
        $path = is_array($files) ? ($files[$index ?? 0] ?? null) : $bank->file;
        if (empty($path) || !Storage::disk('public')->exists($path)) abort(404);
        return response()->file(Storage::disk('public')->path($path));
    }
    public function viewHasil(string $id) {
        $bank = Bank::findOrFail($id);
        if (!$bank->hasil) abort(404);
        return response()->file(Storage::disk('public')->path($bank->hasil));
    }
}