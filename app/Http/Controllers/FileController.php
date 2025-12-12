<?php

namespace App\Http\Controllers;

use App\Helpers\UrlEncryptionHelper;
use App\Models\Bank;
use App\Models\Data;
use App\Models\Slik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FileController extends Controller
{
    /**
     * View encrypted file
     */
    public function viewEncryptedFile(string $encrypted, Request $request)
    {
        // Decrypt the URL
        $data = UrlEncryptionHelper::decryptFileUrl($encrypted);
        
        if (!$data) {
            abort(404, 'File tidak ditemukan atau URL tidak valid.');
        }

        $id = $data['id'];
        $index = $data['index'] ?? 0;
        $feature = $data['feature'];
        $name = $data['name'];

        // Get the file based on feature
        if ($feature === 'bank') {
            return $this->viewBankFile($id, $index, $name, $request);
        } elseif ($feature === 'bank_hasil') {
            return $this->viewBankHasilFile($id, $name, $request);
        } elseif ($feature === 'data') {
            return $this->viewDataFile($id, $index, $name, $request);
        } elseif ($feature === 'slik') {
            return $this->viewSlikFile($id, $index, $name, $request);
        } elseif ($feature === 'slik_hasil2') {
            return $this->viewSlikHasil2File($id, $index, $name, $request);
        }

        abort(404, 'Fitur tidak ditemukan.');
    }

    /**
     * View bank file with encrypted URL
     */
    private function viewBankFile(int $id, int $index, string $name, Request $request)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->file) {
            abort(404);
        }

        // If multiple files stored as JSON, open the specified one
        $files = json_decode($bank->file, true);
        $path = is_array($files) ? ($files[$index] ?? null) : $bank->file;
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

        $originalFilename = basename($path);
        
        // Check if download is requested
        if ($request->has('download')) {
            $filename = UrlEncryptionHelper::generateDownloadFilename($originalFilename, 'bank', $name);
            $disposition = 'attachment';
        } else {
            $filename = $originalFilename;
            $disposition = 'inline';
        }

        $stream = $disk->readStream($path);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    /**
     * View bank hasil file with encrypted URL
     */
    private function viewBankHasilFile(int $id, string $name, Request $request)
    {
        $bank = Bank::findOrFail($id);
        if (!$bank->hasil) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($bank->hasil)) {
            abort(404);
        }

        // Basic mime detection fallback by extension
        $ext = strtolower(pathinfo($bank->hasil, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }

        $originalFilename = basename($bank->hasil);
        
        // Check if download is requested
        if ($request->has('download')) {
            $filename = UrlEncryptionHelper::generateDownloadFilename($originalFilename, 'bank_hasil', $name);
            $disposition = 'attachment';
        } else {
            $filename = $originalFilename;
            $disposition = 'inline';
        }

        $stream = $disk->readStream($bank->hasil);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    /**
     * View data file with encrypted URL
     */
    private function viewDataFile(int $id, int $index, string $name, Request $request)
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
        
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404, 'File fisik tidak ditemukan');
        }

        // Basic mime detection fallback by extension
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }

        $originalFilename = basename($path);
        
        // Check if download is requested
        if ($request->has('download')) {
            $filename = UrlEncryptionHelper::generateDownloadFilename($originalFilename, 'data', $name);
            $disposition = 'attachment';
        } else {
            $filename = $originalFilename;
            $disposition = 'inline';
        }

        $stream = $disk->readStream($path);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    /**
     * View slik file with encrypted URL (support multiple files dengan index)
     */
    private function viewSlikFile(int $id, int $index, string $name, Request $request)
    {
        $slik = Slik::findOrFail($id);
        if (!$slik->hasil) {
            abort(404);
        }

        // Parse hasil sebagai JSON array
        $hasilFiles = [];
        $parsed = is_string($slik->hasil) ? json_decode($slik->hasil, true) : $slik->hasil;
        if (is_array($parsed)) {
            $hasilFiles = $parsed;
        } elseif (is_string($slik->hasil)) {
            // Backward compatibility
            $hasilFiles = [$slik->hasil];
        }

        if (empty($hasilFiles) || !isset($hasilFiles[$index])) {
            abort(404);
        }

        // Handle format baru (object) atau format lama (string)
        $fileData = $hasilFiles[$index];
        $filePath = is_array($fileData) && isset($fileData['path']) ? $fileData['path'] : $fileData;
        $disk = Storage::disk('public');
        if (!$disk->exists($filePath)) {
            abort(404);
        }

        // Basic mime detection fallback by extension
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }

        $originalFilename = basename($filePath);
        
        // Check if download is requested
        if ($request->has('download')) {
            $filename = UrlEncryptionHelper::generateDownloadFilename($originalFilename, 'slik', $name);
            $disposition = 'attachment';
        } else {
            $filename = $originalFilename;
            $disposition = 'inline';
        }

        $stream = $disk->readStream($filePath);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }

    /**
     * View slik hasil2 file with encrypted URL (support multiple files dengan index)
     */
    private function viewSlikHasil2File(int $id, int $index, string $name, Request $request)
    {
        $slik = Slik::findOrFail($id);
        if (!$slik->hasil2) {
            abort(404);
        }

        // Parse hasil2 sebagai JSON array
        $hasil2Files = [];
        $parsed = is_string($slik->hasil2) ? json_decode($slik->hasil2, true) : $slik->hasil2;
        if (is_array($parsed)) {
            $hasil2Files = $parsed;
        } elseif (is_string($slik->hasil2)) {
            // Backward compatibility
            $hasil2Files = [$slik->hasil2];
        }

        if (empty($hasil2Files) || !isset($hasil2Files[$index])) {
            abort(404);
        }

        // Handle format baru (object) atau format lama (string)
        $fileData = $hasil2Files[$index];
        $filePath = is_array($fileData) && isset($fileData['path']) ? $fileData['path'] : $fileData;
        $disk = Storage::disk('public');
        if (!$disk->exists($filePath)) {
            abort(404);
        }

        // Basic mime detection fallback by extension
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) { $mime = 'image/jpeg'; }
        elseif ($ext === 'png') { $mime = 'image/png'; }
        elseif ($ext === 'pdf') { $mime = 'application/pdf'; }

        $originalFilename = basename($filePath);
        
        // Check if download is requested
        if ($request->has('download')) {
            $filename = UrlEncryptionHelper::generateDownloadFilename($originalFilename, 'slik_hasil2', $name);
            $disposition = 'attachment';
        } else {
            $filename = $originalFilename;
            $disposition = 'inline';
        }

        $stream = $disk->readStream($filePath);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }
}
