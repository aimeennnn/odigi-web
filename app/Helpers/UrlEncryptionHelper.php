<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UrlEncryptionHelper
{
    /**
     * Generate 6 character random string
     */
    public static function generateShortCode(): string
    {
        return Str::random(6);
    }

    /**
     * Encrypt file URL with short code and feature info
     */
    public static function encryptFileUrl(int $id, int $index, string $feature, string $name): string
    {
        $data = [
            'id' => $id,
            'index' => $index,
            'feature' => $feature,
            'name' => $name,
            'short_code' => self::generateShortCode()
        ];

        $encrypted = Crypt::encryptString(json_encode($data));
        return strtr($encrypted, ['+' => '-', '/' => '_', '=' => '.']);
    }

    /**
     * Decrypt file URL
     */
    public static function decryptFileUrl(string $encryptedUrl): ?array
    {
        try {
            $decoded = strtr($encryptedUrl, ['-' => '+', '_' => '/', '.' => '=']);
            $decrypted = Crypt::decryptString($decoded);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate download filename with feature and name
     */
    public static function generateDownloadFilename(string $originalFilename, string $feature, string $name): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $cleanName = str_replace(' ', '_', trim($cleanName));
        
        return "{$feature}_{$cleanName}.{$extension}";
    }
}
