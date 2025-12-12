<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;

    protected $table = 'data';
    protected $primaryKey = 'id_data';
    
    protected $fillable = [
        'id_reg',
        'jenis_data',
        'keterangan',
        'file',
        'input_by'
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'id_reg', 'id_reg');
    }

    public static function jenisDataList()
    {
        return [
            'KTP' => 'KTP',
            'e-KTP' => 'e-KTP',
            'NIK' => 'NIK',
            'KK' => 'KK',
            'AK (Akta Kelahiran)' => 'AK (Akta Kelahiran)',
            'IJZ (Ijazah)' => 'IJZ (Ijazah)',
            'PS (Paspor)' => 'PS (Paspor)',
            'SIM' => 'SIM',
            'NPWP' => 'NPWP',
            'BPJS' => 'BPJS',
            'SKCK' => 'SKCK',
        ];
    }
}
