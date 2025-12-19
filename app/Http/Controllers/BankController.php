<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Register;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Wajib
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Helpers\RoleHelper;

class BankController extends BaseController
{
    // --- INDEX ---
    public function index(Request $request)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'view', $request);
        if ($accessCheck) { return $accessCheck; }

        $register_id = $request->query('register_id') ?: session('current_register_id');
        $query = Bank::query()->with('register');
        
        if ($register_id) { $query->where('id_reg', $register_id); }
        if ($nama = $request->input('filter_nama')) {
            $query->whereHas('register', function($q) use ($nama) { $q->where('nama', 'like', '%' . $nama . '%'); });
        }
        if ($bank = $request->input('filter_bank')) { $query->where('nama_bank', 'like', '%' . $bank . '%'); }
        
        // Sorting Logic
        $sort = $request->query('sort', '');
        $order = $request->query('order', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableMap = [
            'nama' => 'registers.nama', 'nama_bank' => 'banks.nama_bank',
            'no_rekening' => 'banks.no_rekening', 'file' => 'banks.file',
            'status' => 'banks.status', 'hasil' => 'banks.hasil', 'updated_at' => 'banks.updated_at'
        ];
        if (array_key_exists($sort, $sortableMap)) {
            if ($sort === 'nama') {
                $query->leftJoin('registers', 'registers.id_reg', '=', 'banks.id_reg')
                      ->select('banks.*')->orderBy($sortableMap[$sort], $order);
            } else { $query->orderBy($sortableMap[$sort], $order); }
        } else { $query->orderBy('banks.id_bank', 'asc'); }

        $perPage = in_array($request->get('per_page'), [5, 10, 25, 50, 100]) ? $request->get('per_page') : 5;
        $bankData = $query->paginate($perPage)->withQueryString();
        
        $registers = $register_id 
            ? Register::where('id_reg', $register_id)->get(['id_reg', 'nama', 'nomor', 'no_identitas'])
            : Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        
        $register = $register_id ? Register::find($register_id) : null;
        return view('bank.index', compact('bankData', 'registers', 'register'));
    }

    // --- STORE (CREATE) ---
    public function store(Request $request)
    {
        // Tambahkan 2 baris ini di paling atas function
        ini_set('max_execution_time', 600); // 600 detik = 10 menit
        set_time_limit(600);
        $accessCheck = $this->checkMenuAccess('menu_bank', 'create', $request);
        if ($accessCheck) { return $accessCheck; }

        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'temp_paths' => 'nullable|array',
            'temp_paths.*' => 'nullable|string',
        ]);

        $filePaths = [];
        $hasilPaths = []; // Menyimpan path Excel sementara

        // 1. Handle File Upload
        if ($request->filled('temp_paths')) {
            $tempPaths = array_filter($request->temp_paths ?? [], function ($p) { return is_string($p) && $p !== ''; });
            foreach ($tempPaths as $tempPath) {
                if (Storage::disk('public')->exists($tempPath)) {
                    $newPath = str_replace('bank/tmp/', 'bank/files/', $tempPath);
                    Storage::disk('public')->makeDirectory('bank/files');
                    Storage::disk('public')->move($tempPath, $newPath);
                    $filePaths[] = $newPath;
                }
            }
        } elseif ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store('bank/files', 'public');
            }
        }

        // 2. JALANKAN OCR (PYTHON) -> DAPAT EXCEL
        foreach ($filePaths as $path) {
            $absolutePath = Storage::disk('public')->path($path);
            $xlsxResult = $this->processOcr($absolutePath); // Output .xlsx
            
            if ($xlsxResult) {
                $hasilPaths[] = $xlsxResult;
            }
        }

        // 3. Simpan ke Database (Status masih 'Proses' atau 'Valid' sementara)
        $validated['file'] = !empty($filePaths) ? json_encode($filePaths) : null;
        $validated['hasil'] = !empty($hasilPaths) ? json_encode($hasilPaths) : null;
        $validated['status'] = !empty($hasilPaths) ? 'Valid' : 'Dalam Proses';

        $bank = Bank::create($validated);

        // 4. KIRIM KE N8N (Jika ada hasil Excel)
        // Kita lakukan setelah create agar punya ID Bank untuk diupdate nanti
        if (!empty($hasilPaths)) {
            foreach ($hasilPaths as $xlsxPath) {
                $this->sendToN8n($xlsxPath, $bank->id_bank);
            }
        }

        return redirect()->route('bank.index')->with('success', 'Data diproses! PDF final dari n8n akan muncul sebentar lagi.');
    }

    // --- UPDATE ---
    public function update(Request $request, string $id)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'edit', $request);
        if ($accessCheck) { return $accessCheck; }
        
        $bank = Bank::findOrFail($id);
        
        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'nama_bank' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|min:8|max:20',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $validated['no_rekening'] = $validated['nomor_rekening'];
        unset($validated['nomor_rekening']);

        $newFilePath = null;

        if ($request->hasFile('file')) {
            $newFilePath = $request->file('file')->store('bank/files', 'public');
            $validated['file'] = $newFilePath;
        }

        if ($request->filled('temp_path')) {
            $temp = ltrim($request->input('temp_path'), '/');
            if (Storage::disk('public')->exists($temp)) {
                $final = 'bank/files/' . basename($temp);
                Storage::disk('public')->makeDirectory('bank/files');
                Storage::disk('public')->move($temp, $final);
                if ($bank->file && Storage::disk('public')->exists($bank->file)) {
                     Storage::disk('public')->delete($bank->file);
                }
                $newFilePath = $final;
                $validated['file'] = $final;
            }
        }

        if ($newFilePath) {
            $absolutePath = Storage::disk('public')->path($newFilePath);
            $xlsxResult = $this->processOcr($absolutePath);
            
            if ($xlsxResult) {
                $validated['hasil'] = $xlsxResult;
                $validated['status'] = 'Valid';
                
                // Update dulu databasenya
                $bank->update($validated);
                
                // Baru kirim ke n8n
                $this->sendToN8n($xlsxResult, $bank->id_bank);
            } else {
                $bank->update($validated);
            }
        } else {
            if ($request->filled('status')) {
                $validated['status'] = $this->mapStatus($request->input('status'));
            }
            $bank->update($validated);
        }

        // Redirect Encrypted Logic
        $enc = Crypt::encryptString($bank->id_bank);
        $urlSafe = strtr($enc, ['+' => '-', '/' => '_', '=' => '.']);
        $detailUrl = route('bank.show', $urlSafe);
        if ($request->has('register_id')) { $detailUrl .= '?register_id=' . $request->register_id; }
        
        return redirect($detailUrl)->with('success', 'Data Bank berhasil diubah & diproses ulang!');
    }

    // --- DELETE ---
    public function destroy(Request $request, string $id)
    {
        $accessCheck = $this->checkMenuAccess('menu_bank', 'delete', $request);
        if ($accessCheck) { return $accessCheck; }
        
        $bank = Bank::findOrFail($id);
        
        // Hapus PDF Asli
        if ($bank->file) {
            $files = json_decode($bank->file, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($files)) {
                foreach ($files as $path) { if (Storage::disk('public')->exists($path)) Storage::disk('public')->delete($path); }
            } elseif (Storage::disk('public')->exists($bank->file)) { Storage::disk('public')->delete($bank->file); }
        }

        // Hapus Hasil (Excel/PDF)
        if ($bank->hasil) {
            $hasils = json_decode($bank->hasil, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($hasils)) {
                foreach ($hasils as $path) { if (Storage::disk('public')->exists($path)) Storage::disk('public')->delete($path); }
            } elseif (Storage::disk('public')->exists($bank->hasil)) { Storage::disk('public')->delete($bank->hasil); }
        }
        
        $bank->delete();
        return redirect()->route('bank.index')->with('success', 'Data dihapus!');
    }

    // --- HELPER FUNCTIONS ---
    private function mapStatus($status) {
        $map = ['1'=>'Dalam Proses','proses'=>'Dalam Proses','2'=>'Valid','valid'=>'Valid','3'=>'Tidak Valid','tidak valid'=>'Tidak Valid'];
        return $map[strtolower($status)] ?? $status;
    }

    // --- OCR PROCESSOR (PYTHON) ---
    private function processOcr($pdfPath)
    {
        if (!file_exists($pdfPath)) return null;

        $fileName = pathinfo($pdfPath, PATHINFO_FILENAME);
        $xlsxName = $fileName . '_' . time() . '_hasil.xlsx'; // Output Excel
        
        $outputFolder = 'bank/hasil_ocr';
        Storage::disk('public')->makeDirectory($outputFolder);
        $xlsxRelativePath = $outputFolder . '/' . $xlsxName;
        $xlsxAbsolutePath = Storage::disk('public')->path($xlsxRelativePath);
        $scriptPath = base_path('python_script/ocbc.py'); 

        try {
            $process = new Process(['python', $scriptPath, $pdfPath, $xlsxAbsolutePath]);
            $process->setTimeout(300); 
            $process->mustRun(); 
            $output = json_decode($process->getOutput(), true);
            
            if (isset($output['status']) && $output['status'] === 'success') {
                return $xlsxRelativePath; 
            }
            return null;
        } catch (\Exception $e) {
            Log::error("OCR Exception: " . $e->getMessage());
            return null;
        }
    }

    // --- SEND TO N8N (PRODUCTION URL) ---
    private function sendToN8n($relativePath, $bankId)
    {
        // URL Production n8n Kamu (Sesuai Screenshot)
        $n8nUrl = 'https://n8n.itsaimen.my.id/webhook/9eb723cb-d272-4f78-9a8d-30f00b71c771';

        $absolutePath = Storage::disk('public')->path($relativePath);
        if (!file_exists($absolutePath)) return;

        try {
            $fileStream = fopen($absolutePath, 'r');
            $fileName = basename($absolutePath);

            // 1. Kirim File -> Tunggu Response JSON
            $response = Http::withoutVerifying() // Anti SSL Error di Localhost
                ->timeout(300) 
                ->attach('file', $fileStream, $fileName)
                ->post($n8nUrl);

            if ($response->successful()) {
                $data = $response->json();

                // 2. Ambil Link PDF dari n8n
                if (isset($data['pdf_url'])) {
                    $downloadUrl = $data['pdf_url'];
                    
                    // 3. Download File PDF-nya
                    $pdfContent = Http::withoutVerifying()->get($downloadUrl)->body();

                    if ($pdfContent) {
                        // 4. Simpan PDF Final di Laptop
                        $newFileName = 'n8n_result_' . time() . '.pdf';
                        $newRelativePath = 'bank/hasil_n8n/' . $newFileName;
                        Storage::disk('public')->put($newRelativePath, $pdfContent);
                        
                        // 5. Update Database dengan PDF Final
                        $bank = Bank::find($bankId);
                        if ($bank) {
                            $bank->hasil = $newRelativePath; // Timpa path Excel dengan path PDF
                            $bank->save();
                            Log::info("Sukses! PDF n8n tersimpan: $newRelativePath");
                        }
                    }
                }
            } else {
                Log::error("Gagal request n8n: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception n8n: " . $e->getMessage());
        }
    }
    
    // --- VIEWERS ---
    public function viewHasil(string $id)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->hasil || !Storage::disk('public')->exists($bank->hasil)) abort(404);
        
        $path = $bank->hasil;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) { 'csv'=>'text/csv', 'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pdf'=>'application/pdf', default=>'application/octet-stream' };
        
        return response()->stream(function () use ($path) {
            fpassthru(Storage::disk('public')->readStream($path));
        }, 200, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="'.basename($path).'"']);
    }
    
    // (Fungsi show, edit, viewFile, uploadTemp lainnya tetap ada seperti biasa)
    public function show(Request $request, string $id) { /* ...Sama seperti sebelumnya... */ return view('bank.show', ['bank' => Bank::findOrFail(is_numeric($id) ? $id : Crypt::decryptString(strtr($id, ['-'=>'+','_'=>'/', '.'=>'='])))]); }
    public function edit(Request $request, string $id) { /* ...Sama seperti sebelumnya... */ return view('bank.edit', ['bank' => Bank::findOrFail(is_numeric($id) ? $id : Crypt::decryptString(strtr($id, ['-'=>'+','_'=>'/', '.'=>'=']))), 'registers' => Register::orderBy('id_reg', 'desc')->get()]); }
    public function viewFile(string $id, ?int $index = 0) { /* ...Sama seperti sebelumnya... */ $bank = Bank::findOrFail($id); $path = json_decode($bank->file, true)[$index] ?? $bank->file; return response()->file(Storage::disk('public')->path($path)); }
    public function uploadTempNew(Request $request) { /* ...Sama seperti sebelumnya... */ $tempPaths = []; foreach($request->file('files') as $file) $tempPaths[] = $file->store('bank/tmp', 'public'); return response()->json(['success'=>true, 'temp_paths'=>$tempPaths]); }
}