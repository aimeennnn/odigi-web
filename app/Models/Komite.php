<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komite extends Model
{
    use HasFactory;
    protected $table = 'komites';
    protected $primaryKey = 'id_komite';
    protected $fillable = ['id_reg', 'tgl', 'keterangan', 'keputusan', 'input_by', 'tipe_memorandum'];

    public function register() {
        return $this->belongsTo(Register::class, 'id_reg', 'id_reg');
    }

    public static function keputusanList()
    {
        return [
            'Disetujui' => 'Disetujui',
            'Ditolak' => 'Ditolak',
            'Revisi' => 'Revisi',
        ];
    }

    public function getKeputusanLabelAttribute()
    {
        if (!$this->keputusan) {
            return null;
        }
        $list = self::keputusanList();
        return $list[$this->keputusan] ?? $this->keputusan;
    }
} 