<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    protected $table = 'registers';

    protected $primaryKey = 'id_reg';

    protected $fillable = [
        'nomor',
        'nama',
        'jenis_entitas',
        'nama_badan_usaha',
        'jenis_dokumen_usaha',
        'nomor_legalitas_usaha',
        'bidang_usaha',
        'alamat_usaha',
        'jns_kelamin',
        'no_identitas',
        'jns_identitas',
        'pekerjaan',
        'alamat',
        'tgl_pengajuan',
        'jns_pengajuan',
        'nominal_pengajuan',
        'jw_pengajuan',
        'jaminan',
        'status',
        'tanggal_realisasi',
        'nominal_disetujui',
        'id_user',
        'input_by',
    ];

    public function slik()
    {
        return $this->hasMany(Slik::class, 'id_reg', 'id_reg');
    }

    public function bank()
    {
        return $this->hasMany(Bank::class, 'id_reg', 'id_reg');
    }

    public function data()
    {
        return $this->hasMany(Data::class, 'id_reg', 'id_reg');
    }

    public function komite()
    {
        return $this->hasMany(Komite::class, 'id_reg', 'id_reg');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    // Getter untuk nama user yang menginput
    public function getInputByAttribute()
    {
        // Jika ada data input_by di database, gunakan itu
        $dbInputBy = $this->getAttributes()['input_by'] ?? null;
        if ($dbInputBy) {
            return $dbInputBy;
        }
        
        // Fallback ke relasi user
        if ($this->user) {
            return $this->user->nama ?? $this->user->username ?? 'User ' . $this->id_user;
        }
        return 'User ' . $this->id_user;
    }

    public function getStatusLabelAttribute()
    {
        // Mapping untuk status numerik (backward compatibility)
        $numericMap = [
            '1' => 'Dalam Proses',
            '2' => 'Menunggu Komite',
            '3' => 'Disetujui',
            '4' => 'Ditolak',
        ];

        // Mapping untuk status teks
        $textMap = [
            'proses' => 'Dalam Proses',
            'dalam proses' => 'Dalam Proses',
            'menunggu komite' => 'Menunggu Komite',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ];

        // Cek apakah status adalah numerik
        if (isset($numericMap[$this->status])) {
            return $numericMap[$this->status];
        }

        // Cek apakah status adalah teks
        return $textMap[strtolower($this->status)] ?? $this->status;
    }

    public function getJenisPengajuanLabelAttribute()
    {
        if (!$this->jns_pengajuan) {
            return null;
        }
        $list = self::jenisPengajuanList();
        return strtoupper($list[$this->jns_pengajuan] ?? $this->jns_pengajuan);
    }

    public static function jenisPengajuanList()
    {
        return [
            'umum' => 'Umum',
            'kta' => 'KTA',
            'tunai'=> 'Tunai',
            'lain_lain' => 'Lain-lain',
        ];
    }
    public static function jenisEntitasList()
    {
        return [
            'perorangan' => 'Perorangan',
            'badan_usaha' => 'Badan Usaha',
        ];
    }
    public static function jenisDokumenUsahaList()
    {
        return [
            'siup' => 'SIUP (Surat Izin Usaha Perdagangan)',
            'tdp' => 'TDP (Tanda Daftar Perusahaan)',
            'npwp' => 'NPWP (Nomor Pokok Wajib Pajak)',
            'akta' => 'Akta Pendirian Perusahaan',
            'sk' => 'Surat Keputusan',
            'lainnya' => 'Lainnya',
        ];
    }
    public static function bidangUsahaList()
    {
        return [
            'perdagangan' => 'Perdagangan',
            'jasa' => 'Jasa',
            'manufaktur' => 'Manufaktur',
            'konstruksi' => 'Konstruksi',
            'pertanian' => 'Pertanian',
            'perikanan' => 'Perikanan',
            'pertambangan' => 'Pertambangan',
            'teknologi' => 'Teknologi',
            'kesehatan' => 'Kesehatan',
            'pendidikan' => 'Pendidikan',
            'lainnya' => 'Lainnya'
        ];
    }
    public static function jenisKelaminList()
    {
        return [
            'laki_laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
        ];
    }
    public static function jenisIdentitasList()
    {
        return [
            'ktp' => 'KTP',
            'sim' => 'SIM',
            'paspor' => 'Paspor',
            'lainnya' => 'Lainnya',
        ];
    }
    public static function pekerjaanList()
    {
        return [
            'pns_asn'    => 'PNS/ASN',
            'tni_polri'  => 'TNI/POLRI',
            'swasta'     => 'Karyawan Swasta',
            'wirausaha'  => 'Wiraswasta',
            'petani'     => 'Petani',
            'nelayan'    => 'Nelayan',
            'buruh'      => 'Buruh',
            'guru'       => 'Guru/Dosen',
            'medis'      => 'Dokter/Bidan/Perawat',
            'pelajar'    => 'Pelajar/Mahasiswa',
            'irt'        => 'Ibu Rumah Tangga',
            'pensiunan'  => 'Pensiunan',
            'sopir'      => 'Sopir/Ojek',
            'pedagang'   => 'Pedagang',
            'lainnya'    => 'Lainnya',
        ];
    }
    public static function jaminanList()
    {
        return [
            'tanah' => 'Tanah',
            'bangunan' => 'Bangunan',
            'kendaraan' => 'Kendaraan',
            'bpkb' => 'BPKB',
            'deposito' => 'Deposito',
            'tanpa_jaminan' => 'Tanpa Jaminan',
            'lainnya' => 'Lainnya',
        ];
    }

    /**
     * Get list of status options for realisasi (upload)
     */
    public static function statusRealisasiList()
    {
        return [
            'Disetujui' => 'Disetujui',
            'Ditolak' => 'Ditolak',
        ];
    }
}
