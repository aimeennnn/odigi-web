<?php

namespace App\Http\Controllers;

use App\Models\Data;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\RoleHelper;

class DataController extends BaseController
{
    public function index(Request $request)
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_data', 'view', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $register_id = $request->query('register_id') ?: session('current_register_id');
        $query = Data::with('register');
        
        // Filter berdasarkan register_id jika ada
        if ($register_id) {
            $query->where('id_reg', $register_id);
        }
        
        // Filter Nomor Register (relasi ke register)
        if ($nomor = $request->input('filter_nomor')) {
            $query->whereHas('register', function($q) use ($nomor) {
                $q->where('nomor', 'like', '%' . $nomor . '%');
            });
        }
        // Filter Jenis Data
        if ($jenis = $request->input('filter_jenis')) {
            $query->where('jenis_data', 'like', '%' . $jenis . '%');
        }
        // Handle per_page parameter
        $perPage = $request->get('per_page', 5);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        $data = $query->orderBy('id_data', 'asc')->paginate($perPage)->withQueryString();
        // Dropdown register: jika ada register_id, hanya tampilkan register itu
        if ($register_id) {
            $registers = Register::where('id_reg', $register_id)->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        } else {
            $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        }
        return view('data.index', compact('data', 'registers'));
    }

    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_data', 'create', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'jenis_data' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'temp_paths' => 'nullable|array',
            'temp_paths.*' => 'string'
        ]);

        // Handle file uploads (multiple)
        $paths = [];
        // Case 1: temp paths from pre-upload
        if ($request->filled('temp_paths')) {
            $tempPaths = array_filter($request->input('temp_paths', []), function ($p) {
                return is_string($p) && $p !== '';
            });
            foreach ($tempPaths as $temp) {
                if (Storage::disk('public')->exists($temp)) {
                    $final = 'data/files/' . basename($temp);
                    Storage::disk('public')->makeDirectory('data/files');
                    Storage::disk('public')->move($temp, $final);
                    $paths[] = $final;
                }
            }
        }
        // Case 2: direct upload via files[] (fallback)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $f) {
                $paths[] = $f->store('data/files', 'public');
            }
        }
        $validated['file'] = $paths ? json_encode($paths) : null;

        // Tambahkan input_by untuk tracking siapa yang input data
        $user = auth()->user();
        $validated['input_by'] = $user ? $user->nama : 'System';

        Data::create($validated);

        // Redirect dengan parameter register_id jika ada
        $redirectParams = [];
        if ($request->has('register_id')) {
            $redirectParams['register_id'] = $request->register_id;
        }

        return redirect()->route('data.index', $redirectParams)->with('success', 'Data tambahan berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        // Terima ID terenkripsi (URL-safe) maupun numeric biasa
        $realId = $id;
        if (!is_numeric($id)) {
            try {
                // Kembalikan dari URL-safe ke format asli sebelum decrypt
                $encoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
                $realId = Crypt::decryptString($encoded);
            } catch (\Throwable $e) {
                abort(404);
            }
        }

        $data = Data::with('register')->findOrFail($realId);
        return view('data.show', compact('data'));
    }

    public function edit(string $id)
    {
        $data = Data::findOrFail($id);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        
        return view('data.edit', compact('data', 'registers'));
    }

    /**
     * Render edit page using encrypted id on query string: /data/edit?id=<enc>
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
        $data = Data::findOrFail($realId);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama', 'nomor', 'no_identitas']);
        return view('data.edit', compact('data', 'registers'));
    }

    public function update(Request $request, string $id)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_data', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        $data = Data::findOrFail($id);

        $validated = $request->validate([
            'id_reg' => 'required|integer|exists:registers,id_reg',
            'jenis_data' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Handle file uploads (multiple) - replace existing list
        if ($request->hasFile('files')) {
            // Optionally: remove old files from storage (comment out if you want to keep)
            if ($data->file) {
                $old = json_decode($data->file, true);
                if (is_array($old)) {
                    foreach ($old as $p) {
                        if ($p && Storage::disk('public')->exists($p)) {
                            Storage::disk('public')->delete($p);
                        }
                    }
                } elseif (is_string($old) && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }
            $newPaths = [];
            foreach ($request->file('files') as $f) {
                $newPaths[] = $f->store('data/files', 'public');
            }
            $validated['file'] = $newPaths ? json_encode($newPaths) : null;
        }

        $data->update($validated);

        // Redirect dengan parameter register_id jika ada
        $redirectParams = [];
        if ($request->has('register_id')) {
            $redirectParams['register_id'] = $request->register_id;
        }

        // Setelah update, kembali ke detail dengan ID terenkripsi agar konsisten
        $encId = Crypt::encryptString($data->id_data);
        $urlSafeId = strtr($encId, ['+' => '-', '/' => '_', '=' => '.']);
        $detailUrl = route('data.show', $urlSafeId);
        if (!empty($redirectParams)) {
            $detailUrl .= '?'.http_build_query($redirectParams);
        }
        return redirect($detailUrl)->with('success', 'Data tambahan berhasil diubah!');
    }

    public function destroy(Request $request, string $id)
    {
        // Double check menu access dan permission untuk delete
        $accessCheck = $this->checkMenuAccess('menu_data', 'delete', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $data = Data::findOrFail($id);

        // Delete file if exists
        if ($data->file && Storage::disk('public')->exists($data->file)) {
            Storage::disk('public')->delete($data->file);
        }

        $data->delete();

        // Redirect dengan parameter register_id jika ada
        $redirectParams = [];
        if ($request->has('register_id')) {
            $redirectParams['register_id'] = $request->register_id;
        }

        return redirect()->route('data.index', $redirectParams)->with('success', 'Data tambahan berhasil dihapus!');
    }

    /**
     * Upload sementara (tanpa data_id) untuk form tambah data.
     */
    public function uploadTempNew(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $tempPaths = [];
        foreach ($request->file('files') as $file) {
            $tempPaths[] = $file->store('data/tmp', 'public');
        }

        return response()->json([
            'success' => true,
            'temp_paths' => $tempPaths,
        ]);
    }

    /**
     * Stream file for preview or download without requiring storage:link.
     */
    public function viewFile(Request $request, string $id, ?int $index = 0)
    {
        $data = Data::findOrFail($id);
        $list = [];
        if (is_string($data->file) && Str::startsWith($data->file, '[')) {
            $list = json_decode($data->file, true) ?: [];
        } elseif (is_string($data->file) && $data->file !== '') {
            $list = [$data->file];
        }
        if (empty($list)) {
            abort(404, 'File tidak ditemukan');
        }
        $index = $index ?? 0;
        if (!isset($list[$index])) {
            abort(404, 'File index tidak valid');
        }
        $path = ltrim((string)$list[$index], '/');
        if (strpos($path, '/') === false) {
            $path = 'data/files/' . $path;
        }
        if (Str::startsWith($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, 8);
        }
        $absolute = Storage::disk('public')->path($path);
        if (!is_file($absolute)) {
            abort(404, 'File fisik tidak ditemukan');
        }
        $download = $request->boolean('download');
        $mime = File::mimeType($absolute) ?: 'application/octet-stream';
        if ($download) {
            return response()->download($absolute, basename($absolute));
        }
        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=10800',
        ]);
    }
}