<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // 2MB max
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Generate unique filename
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store the file in storage/app/public/uploads/images directory
                $path = $file->storeAs('uploads/images', $filename, 'public');
                
                // Return the URL menggunakan route khusus untuk serve file
                // Ini tidak bergantung pada storage:link
                $url = route('image.uploaded', ['filename' => $filename]);
                
                return response()->json([
                    'location' => $url
                ]);
            }
            
            return response()->json(['error' => 'No file uploaded'], 400);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Serve uploaded image file
     */
    public function serveImage($filename)
    {
        $path = 'uploads/images/' . $filename;
        $disk = Storage::disk('public');
        
        if (!$disk->exists($path)) {
            abort(404, 'Image not found');
        }
        
        // Determine mime type
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = 'image/jpeg';
        if ($ext === 'png') {
            $mime = 'image/png';
        } elseif ($ext === 'gif') {
            $mime = 'image/gif';
        } elseif ($ext === 'svg') {
            $mime = 'image/svg+xml';
        }
        
        $stream = $disk->readStream($path);
        if ($stream === false) {
            abort(404);
        }
        
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}

