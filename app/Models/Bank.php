<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    protected $primaryKey = 'id_bank';
    protected $fillable = [
        'id_reg', 'nama_bank', 'no_rekening', 'file', 'hasil', 'status', 'input_by'
    ];

    public function register()
    {
        return $this->belongsTo(\App\Models\Register::class, 'id_reg', 'id_reg');
    }

    public function getStatusLabelAttribute()
    {
        // Jika hasil null, kunci status menjadi "Dalam Proses"
        if (is_null($this->hasil)) {
            return 'Dalam Proses';
        }
        
        $map = [
            'proses' => 'Dalam Proses',
            'valid' => 'Valid',
            'tidak valid' => 'Tidak Valid',
            'tidak_valid' => 'Tidak Valid',
            'tidakvalid' => 'Tidak Valid',
        ];
        return $map[strtolower($this->status)] ?? $this->status;
    }

    public static function namaBankList()
    {
        return [
            'BRI' => 'BRI',
            'BNI' => 'BNI',
            'BCA' => 'BCA',
            'BTN' => 'BTN',
            'Mandiri' => 'Mandiri',
            'CIMB Niaga' => 'CIMB Niaga',
            'Permata' => 'Permata',
            'Panin' => 'Panin',
            'Mega' => 'Mega',
            'Danamon' => 'Danamon',
            'Maybank' => 'Maybank',
            'OCBC' => 'OCBC',
            'Citibank' => 'Citibank',
            'Standard Chartered' => 'Standard Chartered',
            'HSBC' => 'HSBC',
            'UOB' => 'UOB',
            'BTPN' => 'BTPN',
            'Sinarmas' => 'Sinarmas',
            'Bukopin' => 'Bukopin',
            'Commonwealth' => 'Commonwealth',
            'Jago' => 'Jago',
            'BNC (Bank Neo Commerce)' => 'BNC (Bank Neo Commerce)',
            'SeaBank' => 'SeaBank',
            'Allo Bank' => 'Allo Bank',
            'BWS (Bank Woori Saudara)' => 'BWS (Bank Woori Saudara)',
            'BTPNS (BTPN Syariah)' => 'BTPNS (BTPN Syariah)',
            'BCA Syariah' => 'BCA Syariah',
            'BSI (Bank Syariah Indonesia)' => 'BSI (Bank Syariah Indonesia)',
        ];
    }
}
