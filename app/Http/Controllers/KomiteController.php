<?php
namespace App\Http\Controllers;

use App\Models\Komite;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\RoleHelper;

class KomiteController extends BaseController
{
    public function index(Request $request)
    {
        // Double check menu access untuk keamanan tambahan
        $accessCheck = $this->checkMenuAccess('menu_komite', 'view', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        try {
            $register_id = $request->query('register_id') ?: session('current_register_id');
            $query = Komite::with('register');
            
            // Filter berdasarkan register_id jika ada
            if ($register_id) {
                $query->where('id_reg', $register_id);
            }
            
            // Filter tanggal
            if ($request->tgl_awal) {
                $query->where('tgl', '>=', $request->tgl_awal);
            }
            if ($request->tgl_akhir) {
                $query->where('tgl', '<=', $request->tgl_akhir);
            }
            // Filter keputusan
            if ($request->keputusan) {
                $query->where('keputusan', $request->keputusan);
            }
            
            // Handle per_page parameter
            $perPage = $request->get('per_page', 5);
            $allowedPerPage = [5, 10, 25, 50, 100];
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 5;
            }

            $komites = $query->orderBy('id_komite', 'asc')->paginate($perPage)->withQueryString();
            
            // Dropdown register: jika ada register_id, hanya tampilkan register itu
            if ($register_id) {
                $register = Register::find($register_id);
            } else {
                $register = null;
            }
            
            // Ambil data memorandum per tipe
            $rekomendasiManager = null;
            $opiniDirektur = null;
            $keputusanDirektur = null;
            $mengetahuiKomisaris = null;
            if ($register_id) {
                $rekomendasiManager = \App\Models\Komite::where('id_reg', $register_id)->where('tipe_memorandum', 'rekomendasi')->orderBy('tgl', 'desc')->first();
                $opiniDirektur = \App\Models\Komite::where('id_reg', $register_id)->where('tipe_memorandum', 'opini')->orderBy('tgl', 'desc')->first();
                $keputusanDirektur = \App\Models\Komite::where('id_reg', $register_id)->where('tipe_memorandum', 'keputusan')->orderBy('tgl', 'desc')->first();
                $mengetahuiKomisaris = \App\Models\Komite::where('id_reg', $register_id)->where('tipe_memorandum', 'mengetahui')->orderBy('tgl', 'desc')->first();
                
            }
            
            
            return view('komite.index', compact('komites', 'register', 'rekomendasiManager', 'opiniDirektur', 'keputusanDirektur', 'mengetahuiKomisaris'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data komite.');
        }
    }

    public function store(Request $request)
    {
        // Double check menu access dan permission untuk create
        $accessCheck = $this->checkMenuAccess('menu_komite', 'create', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        try {
            $validated = $request->validate([
                'id_reg' => 'required|exists:registers,id_reg',
                'tgl' => 'required|date',
                'keterangan' => 'required|string',
                'keputusan' => 'nullable|string|max:100',
                'tipe_memorandum' => 'required|string',
            ]);
            
            // Keputusan hanya untuk tipe_memorandum = 'keputusan'
            if ($validated['tipe_memorandum'] !== 'keputusan') {
                $validated['keputusan'] = null;
            }
            
            // Tambahkan input_by untuk tracking siapa yang input data
            $user = auth()->user();
            $validated['input_by'] = $user ? $user->nama : 'System';

            // Guard: batasi hanya 1 entri per register & tipe memorandum
            $tipe = $validated['tipe_memorandum'];
            $existing = Komite::where('id_reg', $validated['id_reg'])
                ->where('tipe_memorandum', $tipe)
                ->first();
            if ($existing) {
                return redirect()->back()->with('error', 'Data untuk bagian ini sudah ada. Silakan gunakan Ubah Data.');
            }
            
            Komite::create($validated);
            // Update status register jika tipe_memorandum = keputusan (Direktur Utama)
            if (isset($validated['tipe_memorandum']) && $validated['tipe_memorandum'] === 'keputusan') {
                $register = Register::find($validated['id_reg']);
                if ($register) {
                    if (strtolower($validated['keputusan']) === 'disetujui') {
                        $register->status = 3;
                    } elseif (strtolower($validated['keputusan']) === 'ditolak') {
                        $register->status = 4;
                    } elseif (strtolower($validated['keputusan']) === 'revisi') {
                        $register->status = 'Revisi';
                    }
                    $register->save();
                }
            }
            
            return redirect()->route('komite.index', ['register_id' => $request->register_id])->with('success', 'Data Komite berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data komite.')->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        try {
            try { 
                $decoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
                $decrypted = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
            } catch (\Exception $e) { $decrypted = $id; }
            $komite = Komite::with('register')->findOrFail($decrypted);
            
            return view('komite.show', compact('komite'));
        } catch (\Exception $e) {
            return redirect()->route('komite.index')->with('error', 'Data komite tidak ditemukan.');
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            try { 
                $decoded = strtr($id, ['-' => '+', '_' => '/', '.' => '=']);
                $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
            } catch (\Exception $e) { $realId = $id; }
            $komite = Komite::findOrFail($realId);
            $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama']);
            
            return view('komite.edit', compact('komite', 'registers'));
        } catch (\Exception $e) {
            return redirect()->route('komite.index')->with('error', 'Data komite tidak ditemukan.');
        }
    }

    // Edit sederhana via /komite/edit?id=<enc>
    public function editSimple(Request $request)
    {
        $enc = (string) $request->query('id', '');
        if ($enc === '') abort(404);
        try {
            $decoded = strtr($enc, ['-' => '+', '_' => '/', '.' => '=']);
            $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
        } catch (\Throwable $e) { abort(404); }
        $komite = Komite::findOrFail($realId);
        $registers = Register::orderBy('id_reg', 'desc')->get(['id_reg', 'nama']);
        return view('komite.edit', compact('komite', 'registers'));
    }

    public function update(Request $request, $id)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_komite', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        try {
            
            $komite = Komite::findOrFail($id);
            $validated = $request->validate([
                'id_reg' => 'required|exists:registers,id_reg',
                'tgl' => 'required|date',
                'keterangan' => 'required|string',
                'keputusan' => 'nullable|string|max:100',
            ]);
            
            $komite->update($validated);
            // Update status register jika tipe_memorandum = keputusan (Direktur Utama)
            if (isset($validated['tipe_memorandum']) && $komite->tipe_memorandum === 'keputusan') {
                $register = Register::find($validated['id_reg']);
                if ($register) {
                    if (strtolower($validated['keputusan']) === 'disetujui') {
                        $register->status = 3;
                    } elseif (strtolower($validated['keputusan']) === 'ditolak') {
                        $register->status = 4;
                    } elseif (strtolower($validated['keputusan']) === 'revisi') {
                        $register->status = 'Revisi';
                    }
                    $register->save();
                }
            }
            
            // Redirect ke detail komite terenkripsi URL-safe
            $enc = Crypt::encryptString($komite->id_komite);
            $urlSafe = strtr($enc, ['+' => '-', '/' => '_', '=' => '.']);
            $detailUrl = route('komite.show', $urlSafe);
            return redirect($detailUrl)->with('success', 'Data Komite berhasil diubah!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah data komite.')->withInput();
        }
    }

    // Update sederhana via /komite/update?id=<enc>
    public function updateSimple(Request $request)
    {
        // Double check menu access dan permission untuk edit
        $accessCheck = $this->checkMenuAccess('menu_komite', 'edit', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        $enc = (string) $request->query('id', '');
        if ($enc === '') abort(404);
        
        try {
            $decoded = strtr($enc, ['-' => '+', '_' => '/', '.' => '=']);
            $realId = is_numeric($decoded) ? $decoded : Crypt::decryptString($decoded);
        } catch (\Throwable $e) { 
            abort(404); 
        }
        
        try {
            $komite = Komite::findOrFail($realId);
            $validated = $request->validate([
                'id_reg' => 'required|exists:registers,id_reg',
                'tgl' => 'required|date',
                'keterangan' => 'required|string',
                'keputusan' => 'nullable|string|max:100',
            ]);
            
            $komite->update($validated);
            
            // Redirect kembali ke halaman komite tanpa parameter
            return redirect()->route('komite.index')->with('success', 'Data Komite berhasil diubah!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah data komite.')->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        // Double check menu access dan permission untuk delete
        $accessCheck = $this->checkMenuAccess('menu_komite', 'delete', $request);
        if ($accessCheck) {
            return $accessCheck;
        }
        
        try {
            $komite = Komite::findOrFail($id);

            // Hanya pembuat data (input_by) atau SUPERADMIN yang boleh menghapus
            $user = auth()->user();
            $isCreator = $user && $komite->input_by === ($user->nama ?? '');
            $isSuperadmin = \App\Helpers\RoleHelper::isSuperAdmin();
            if (!$isCreator && !$isSuperadmin) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus data ini.');
            }

            $komite->delete();
            
            return redirect()->route('komite.index')->with('success', 'Data Komite berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data komite.');
        }
    }
}