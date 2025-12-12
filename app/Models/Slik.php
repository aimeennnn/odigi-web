<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slik extends Model
{
    protected $table = 'sliks';
    protected $primaryKey = 'id_slik';
    protected $fillable = [
        'id_reg', 'nomor', 'tgl', 'nama', 'no_identitas', 'keterkaitan', 'hasil', 'hasil2', 'status', 'input_by'
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'id_reg', 'id_reg');
    }

    public static function keterkaitanList()
    {
        return [
            'Pribadi' => 'Pribadi',
            'Pengurus' => 'Pengurus',
            'Terkait' => 'Terkait',
            'Keluarga' => 'Keluarga',
            'Lain-lain' => 'Lain-lain',
        ];
    }

    public static function statusList()
    {
        return [
            'proses' => 'Dalam Proses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];
    }

    public function getStatusLabelAttribute()
    {
        // Jika hasil2 null, kunci status menjadi "Dalam Proses"
        if (is_null($this->hasil2)) {
            return 'Dalam Proses';
        }
        
        // Ambil dari statusList jika ada
        $list = self::statusList();
        return $list[strtolower($this->status)] ?? $this->status;
    }
}
