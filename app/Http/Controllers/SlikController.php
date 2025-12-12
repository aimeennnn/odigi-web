<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slik;
use App\Models\Register;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\RoleHelper;

class SlikController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_slik', 'view', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $register_id = $request->query('register_id') ?: session('current_register_id');
        $query = Slik::query();
        
        // Filter berdasarkan register_id jika ada
        if ($register_id) {
            $query->where('id_reg', $register_id);
        }
        
        // Filter berdasarkan nama nasabah jika ada
        if ($nama = $request->input('filter_nama')) {
            $query->where('nama', 'like', '%' . $nama . '%');
        }
        
        // Filter berdasarkan no identitas jika ada
        if ($no_identitas = $request->input('filter_no_identitas')) {
            $query->where('no_identitas', 'like', '%' . $no_identitas . '%');
        }
        
        // Filter berdasarkan status jika ada
        if ($status = $request->input('filter_status')) {
            $query->where('status', $status);
        }
        
        // Handle per_page parameter
        $perPage = $request->get('per_page', 5);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        $sliks = $query->orderBy('id_slik', 'asc')->paginate($perPage)->withQueryString();
        
        // Tambahkan file info untuk setiap SLIK
        $sliks->each(function($slik) {
            if ($slik->hasil) {
                $slik->file_info = $this->getFileInfo($slik->hasil);
            } else {
                $slik->file_info = [
                    'exists' => false,
                    'size' => 'No file',
                    'error' => null
                ];
            }
        });
        
        // Refresh data untuk memastikan data terbaru
        $sliks->each(function($slik) {
            $slik->refresh();
        });
        
        // Debug: Log data yang akan ditampilkan
        Log::info('SLIK data for index', [
            'total_records' => $sliks->count(),
            'records' => $sliks->map(function($slik) {
                return [
                    'id_slik' => $slik->id_slik,
                    'nomor' => $slik->nomor,
                    'hasil' => $slik->hasil,
                    'status' => $slik->status,
                    'file_info' => $slik->file_info ?? 'No file info'
                ];
            })->toArray()
        ]);

        // Dropdown register: jika ada register_id, hanya tampilkan register itu
        if ($register_id) {
            $registers = Register::where('id_reg', $register_id)->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        } else {
            $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        }

        $year = date('Y');
        $lastSlik = Slik::where('nomor', 'like', 'SLIK-%/' . $year)
            ->orderByDesc('id_slik')
            ->first();
        if ($lastSlik && preg_match('/^SLIK-(\d{3})\/' . $year . '$/', $lastSlik->nomor, $m)) {
            $lastNum = (int)$m[1];
            $nextNumber = $lastNum + 1;
        } else {
            $nextNumber = 1;
        }
        $nomor_slik_berikutnya = 'SLIK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '/' . $year;
        
        return view('slik.index', compact('sliks', 'registers', 'nomor_slik_berikutnya'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_slik', 'create', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $year = date('Y');
        $lastSlik = Slik::where('nomor', 'like', 'SLIK-%/' . $year)
            ->orderByDesc('id_slik')
            ->first();
        if ($lastSlik && preg_match('/^SLIK-(\d{3})\/' . $year . '$/', $lastSlik->nomor, $m)) {
            $lastNum = (int)$m[1];
            $nextNumber = $lastNum + 1;
        } else {
            $nextNumber = 1;
        }
        $nomor_slik = 'SLIK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '/' . $year;
        $validated = $request->validate([
            'nomor' => 'required',
            'nama' => 'required',
            'no_identitas' => 'required',
            'keterkaitan' => 'required',
            // 'hasil' => 'required|file|mimes:pdf,jpg,jpeg,png', // dihapus dari required
            'tgl' => 'required|date',
            'id_reg' => 'required|integer',
        ]);
        $validated['nomor'] = $nomor_slik;
        // Simpan file hasil ke storage jika ada
        if ($request->hasFile('hasil')) {
            $path = $request->file('hasil')->store('slik/hasil', 'public');
            $validated['hasil'] = $path;
        } else {
            $validated['hasil'] = null; // Berikan nilai default null
        }
        
        // Inisialisasi hasil2 sebagai null
        $validated['hasil2'] = null;
        
        // Kunci status pada tambah data SLIK
        $validated['status'] = 'Dalam Proses';
        
        // Tambahkan input_by untuk tracking siapa yang input data
        $user = auth()->user();
        $validated['input_by'] = $user ? $user->nama : 'System';
        
        Slik::create($validated);
        
        // Redirect dengan parameter register_id jika ada
        $redirectParams = [];
        if ($request->has('register_id')) {
            $redirectParams['register_id'] = $request->register_id;
        }

        return redirect()->route('slik.index')->with('success', 'Data SLIK berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        // Dukung ID terenkripsi URL-safe
        try { 
            $decoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
            $decrypted = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded); 
        } catch (\Exception $e) { $decrypted = $id; }
        $slik = \App\Models\Slik::findOrFail($decrypted);
        
        // Tambahkan file info
        if ($slik->hasil) {
            $slik->file_info = $this->getFileInfo($slik->hasil);
        } else {
            $slik->file_info = [
                'exists' => false,
                'size' => 'No file',
                'error' => null
            ];
        }
        
        return view('slik.show', compact('slik'));
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
        $slik = Slik::findOrFail($decrypted);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        
        return view('slik.edit', compact('slik', 'registers'));
    }

    /**
     * Edit dengan URL sederhana: /slik/edit?id=<enc>
     */
    public function editSimple(Request $request)
    {
        $enc = (string) $request->query('id', '');
        if ($enc === '') abort(404);
        try {
            $decoded = strtr($enc, ['-' => '+', '_' => '/', '.' => '=']);
            $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
        } catch (\Throwable $e) { abort(404); }
        $slik = Slik::findOrFail($realId);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        return view('slik.edit', compact('slik', 'registers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_slik', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $slik = Slik::findOrFail($id);
        $validated = $request->validate([
            'nomor' => 'required',
            'nama' => 'required',
            'no_identitas' => 'required',
            'keterkaitan' => 'required',
            'tgl' => 'required|date',
            'id_reg' => 'required|integer',
            // 'status' => 'required', // dihapus karena tidak ada di form edit
            // 'hasil' => 'nullable|file|mimes:pdf,jpg,jpeg,png', // dihapus karena tidak ada di form edit
        ]);
        // Hapus logic upload file hasil dan status pada update edit SLIK
        $slik->update($validated);
        
        // Setelah update, kembali ke detail SLIK terenkripsi URL-safe
        $enc = Crypt::encryptString($slik->id_slik);
        $urlSafe = strtr($enc, ['+' => '-', '/' => '_', '=' => '.']);
        $detailUrl = route('slik.show', $urlSafe);
        $append = [];
        if ($request->has('register_id')) { $append['register_id'] = $request->register_id; }
        if (!empty($append)) { $detailUrl .= '?'.http_build_query($append); }
        return redirect($detailUrl)->with('success', 'Data SLIK berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Double check menu access dan permission untuk delete
        $accessCheck = $this->checkMenuAccess('menu_slik', 'delete', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $slik = \App\Models\Slik::findOrFail($id);
        $slik->delete();
        
        // Redirect dengan parameter register_id jika ada
        $redirectParams = [];
        if ($request->has('register_id')) {
            $redirectParams['register_id'] = $request->register_id;
        }

        return redirect()->route('slik.index')->with('success', 'Data SLIK berhasil dihapus!');
    }

    /**
     * Get existing files for a SLIK record
     */
    public function getFiles($id)
    {
        try {
            $slik = Slik::findOrFail($id);
            
            $result = [
                'success' => true,
                'hasil' => [],
                'hasil2' => []
            ];
            
            // Parse hasil sebagai JSON array
            if ($slik->hasil) {
                $hasilFiles = is_string($slik->hasil) ? json_decode($slik->hasil, true) : $slik->hasil;
                if (is_array($hasilFiles)) {
                    foreach ($hasilFiles as $fileData) {
                        if ($fileData) {
                            // Handle format baru (object dengan path dan original_name)
                            if (is_array($fileData) && isset($fileData['path'])) {
                                $filePath = $fileData['path'];
                                $originalName = $fileData['original_name'] ?? basename($filePath);
                            } else {
                                // Handle format lama (string path)
                                $filePath = $fileData;
                                $originalName = basename($filePath);
                            }
                            
                            if ($filePath) {
                                $fileInfo = $this->getFileInfo($filePath);
                                $result['hasil'][] = [
                                    'name' => $originalName,
                                    'path' => $filePath,
                                    'size' => $fileInfo['size']
                                ];
                            }
                        }
                    }
                } else {
                    // Backward compatibility: jika masih string (file single)
                    $fileInfo = $this->getFileInfo($slik->hasil);
                    $result['hasil'][] = [
                        'name' => basename($slik->hasil),
                        'path' => $slik->hasil,
                        'size' => $fileInfo['size']
                    ];
                }
            }
            
            // Parse hasil2 sebagai JSON array
            if ($slik->hasil2) {
                $hasil2Files = is_string($slik->hasil2) ? json_decode($slik->hasil2, true) : $slik->hasil2;
                if (is_array($hasil2Files)) {
                    foreach ($hasil2Files as $fileData) {
                        if ($fileData) {
                            // Handle format baru (object dengan path dan original_name)
                            if (is_array($fileData) && isset($fileData['path'])) {
                                $filePath = $fileData['path'];
                                $originalName = $fileData['original_name'] ?? basename($filePath);
                            } else {
                                // Handle format lama (string path)
                                $filePath = $fileData;
                                $originalName = basename($filePath);
                            }
                            
                            if ($filePath) {
                                $fileInfo = $this->getFileInfo($filePath);
                                $result['hasil2'][] = [
                                    'name' => $originalName,
                                    'path' => $filePath,
                                    'size' => $fileInfo['size']
                                ];
                            }
                        }
                    }
                } else {
                    // Backward compatibility: jika masih string (file single)
                    $fileInfo = $this->getFileInfo($slik->hasil2);
                    $result['hasil2'][] = [
                        'name' => basename($slik->hasil2),
                        'path' => $slik->hasil2,
                        'size' => $fileInfo['size']
                    ];
                }
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle upload dokumen hasil dari tabel aksi (support 2 files di hasil).
     * Hasil2 dikunci "Dalam Proses" dan tidak bisa diubah manual (hasil ekstraksi otomatis).
     */
    public function uploadHasil(Request $request, $id)
    {
        try {
            // Validasi input
            $request->validate([
                'temp_path_hasil' => 'nullable|string',
                'temp_path_hasil2' => 'nullable|string',
                'original_name_hasil' => 'nullable|string',
                'original_name_hasil2' => 'nullable|string',
                'status_update' => 'nullable|string'
            ]);
            
            $slik = Slik::findOrFail($id);
            
            $updateData = [];
            $hasilFiles = [];
            
            // Parse hasil yang sudah ada (jika ada)
            if ($slik->hasil) {
                $existingHasil = is_string($slik->hasil) ? json_decode($slik->hasil, true) : $slik->hasil;
                if (is_array($existingHasil)) {
                    // Convert format lama (string) ke format baru (object) jika perlu
                    foreach ($existingHasil as $index => $fileData) {
                        if (is_string($fileData)) {
                            $hasilFiles[$index] = [
                                'path' => $fileData,
                                'original_name' => basename($fileData)
                            ];
                        } else {
                            $hasilFiles[$index] = $fileData;
                        }
                    }
                } elseif (is_string($slik->hasil)) {
                    // Backward compatibility
                    $hasilFiles = [[
                        'path' => $slik->hasil,
                        'original_name' => basename($slik->hasil)
                    ]];
                }
            }
            
            // Handle delete file 1 jika diminta
            if ($request->filled('delete_hasil_1')) {
                if (isset($hasilFiles[0])) {
                    $oldFile = is_array($hasilFiles[0]) ? $hasilFiles[0]['path'] : $hasilFiles[0];
                    try {
                        if (Storage::disk('public')->exists($oldFile)) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete hasil file 1', ['path' => $oldFile, 'error' => $e->getMessage()]);
                    }
                }
                unset($hasilFiles[0]);
            }
            
            // Handle delete file 2 jika diminta
            if ($request->filled('delete_hasil_2')) {
                if (isset($hasilFiles[1])) {
                    $oldFile = is_array($hasilFiles[1]) ? $hasilFiles[1]['path'] : $hasilFiles[1];
                    try {
                        if (Storage::disk('public')->exists($oldFile)) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete hasil file 2', ['path' => $oldFile, 'error' => $e->getMessage()]);
                    }
                }
                unset($hasilFiles[1]);
            }
            
            // Handle file hasil (file 1) - upload document 1
            if ($request->filled('temp_path_hasil')) {
                $temp = $request->input('temp_path_hasil');
                $originalName = $request->input('original_name_hasil');
                if (!Storage::disk('public')->exists($temp)) {
                    throw new \Exception('Temporary file hasil not found');
                }
                
                // Hapus file lama di index 0 jika ada
                if (isset($hasilFiles[0])) {
                    $oldFile = is_array($hasilFiles[0]) ? $hasilFiles[0]['path'] : $hasilFiles[0];
                    try {
                        if (Storage::disk('public')->exists($oldFile)) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old hasil file 1', ['path' => $oldFile, 'error' => $e->getMessage()]);
                    }
                }
                
                $finalName = 'slik/hasil/' . basename($temp);
                Storage::disk('public')->makeDirectory('slik/hasil');
                Storage::disk('public')->move($temp, $finalName);
                $hasilFiles[0] = [
                    'path' => $finalName,
                    'original_name' => $originalName ?: basename($finalName)
                ];
            }
            
            // Handle file hasil (file 2) - upload document 2
            if ($request->filled('temp_path_hasil2')) {
                $temp = $request->input('temp_path_hasil2');
                $originalName = $request->input('original_name_hasil2');
                if (!Storage::disk('public')->exists($temp)) {
                    throw new \Exception('Temporary file hasil2 not found');
                }
                
                // Hapus file lama di index 1 jika ada
                if (isset($hasilFiles[1])) {
                    $oldFile = is_array($hasilFiles[1]) ? $hasilFiles[1]['path'] : $hasilFiles[1];
                    try {
                        if (Storage::disk('public')->exists($oldFile)) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old hasil file 2', ['path' => $oldFile, 'error' => $e->getMessage()]);
                    }
                }
                
                $finalName = 'slik/hasil/' . basename($temp);
                Storage::disk('public')->makeDirectory('slik/hasil');
                Storage::disk('public')->move($temp, $finalName);
                $hasilFiles[1] = [
                    'path' => $finalName,
                    'original_name' => $originalName ?: basename($finalName)
                ];
            }
            
            // Simpan hasil sebagai JSON array
            if (!empty($hasilFiles)) {
                $updateData['hasil'] = json_encode(array_values($hasilFiles)); // array_values untuk reindex
            } else {
                // Jika semua file dihapus, set hasil menjadi null
                $updateData['hasil'] = null;
            }
            
            // Hasil2 dikunci "Dalam Proses" - tidak bisa diubah manual (hasil ekstraksi otomatis)
            // Hasil2 akan diisi otomatis oleh sistem ekstraksi
            
            // Update status jika ada
            if ($request->filled('status_update')) {
                $statusUpdate = $request->input('status_update');
                if ($statusUpdate === 'selesai') {
                    $updateData['status'] = 'Selesai';
                } elseif ($statusUpdate === 'ditolak') {
                    $updateData['status'] = 'Ditolak';
                } elseif ($statusUpdate === 'proses') {
                    $updateData['status'] = 'Dalam Proses';
                }
            } else {
                // Set status menjadi "Valid" jika ada file upload
                if (!empty($hasilFiles)) {
                    $updateData['status'] = 'Valid';
                }
            }
            
            // Update database
            $slik->update($updateData);
            
            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen hasil berhasil diupload',
                    'status' => $slik->status,
                    'id_slik' => $slik->id_slik,
                ]);
            }

            // Redirect dengan parameter register_id jika ada
            $redirectParams = [];
            if ($request->has('register_id')) {
                $redirectParams['register_id'] = $request->register_id;
            }

            return redirect()->route('slik.index')->with('success', 'Dokumen hasil berhasil diupload!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in uploadHasil', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('SLIK not found', [
                'id' => $id,
                'request_data' => $request->all()
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data SLIK tidak ditemukan!'
                ], 404);
            }
            return redirect()->back()->with('error', 'Data SLIK tidak ditemukan!');
        } catch (\Exception $e) {
            Log::error('Error in uploadHasil', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'id' => $id
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat upload file: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat upload file: ' . $e->getMessage());
        }
    }

    /**
     * Upload file sementara (temporary) untuk kemudian difinalisasi.
     */
    public function uploadTemp(Request $request, $id)
    {
        $request->validate([
            'hasil' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' // 2MB max
        ]);

        $file = $request->file('hasil');
        $tempPath = $file->store('slik/tmp', 'public');

        return response()->json([
            'success' => true,
            'temp_path' => $tempPath,
            'original_name' => $file->getClientOriginalName(), // Nama file asli
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
    }

    /**
     * Check if file exists and get file info safely
     */
    private function getFileInfo($filePath)
    {
        try {
            if (empty($filePath)) {
                return [
                    'exists' => false,
                    'size' => 'No file',
                    'error' => null
                ];
            }

            if (!Storage::disk('public')->exists($filePath)) {
                return [
                    'exists' => false,
                    'size' => 'File not found',
                    'error' => null
                ];
            }

            $size = Storage::disk('public')->size($filePath);
            
            if ($size === false) {
                return [
                    'exists' => true,
                    'size' => 'Size unknown',
                    'error' => 'Could not determine file size'
                ];
            }

            if ($size >= 1024 * 1024) {
                $sizeText = round($size / (1024 * 1024), 2) . ' MB';
            } elseif ($size >= 1024) {
                $sizeText = round($size / 1024, 1) . ' KB';
            } else {
                $sizeText = $size . ' B';
            }

            return [
                'exists' => true,
                'size' => $sizeText,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'exists' => false,
                'size' => 'Error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Stream file hasil directly from storage to avoid relying on public symlink.
     */
    public function viewFile($id)
    {
        $slik = Slik::findOrFail($id);
        if (!$slik->hasil) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($slik->hasil)) {
            abort(404);
        }

        $ext = strtolower(pathinfo($slik->hasil, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }
        $filename = basename($slik->hasil);
        $stream = $disk->readStream($slik->hasil);
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
}
