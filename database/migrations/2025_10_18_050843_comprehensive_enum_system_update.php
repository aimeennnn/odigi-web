<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Comprehensive enum system update - menggabungkan semua perubahan enum
     */
    public function up(): void
    {
        echo "🔄 Memulai migrasi sistem enum terpusat...\n";
        
        // 1. Update jns_pengajuan dari numerik ke string
        echo "📝 Mengupdate jenis pengajuan...\n";
        $count1 = DB::table('registers')->where('jns_pengajuan', '1')->count();
        $count2 = DB::table('registers')->where('jns_pengajuan', '2')->count();
        $count3 = DB::table('registers')->where('jns_pengajuan', '3')->count();
        
        DB::table('registers')->where('jns_pengajuan', '1')->update(['jns_pengajuan' => 'umum']);
        DB::table('registers')->where('jns_pengajuan', '2')->update(['jns_pengajuan' => 'kta']);
        DB::table('registers')->where('jns_pengajuan', '3')->update(['jns_pengajuan' => 'lain_lain']);
        
        echo "   ✅ Umum: {$count1} data\n";
        echo "   ✅ KTA: {$count2} data\n";
        echo "   ✅ Lain-lain: {$count3} data\n";
        
        // 2. Update jaminan dari format lama ke string key
        echo "📝 Mengupdate jaminan...\n";
        $jaminanUpdates = [
            'TANAH' => 'tanah',
            'BANGUNAN' => 'bangunan', 
            'KENDARAAN' => 'kendaraan',
            'BPKB' => 'bpkb',
            'DEPOSITO' => 'deposito',
            'TANPA JAMINAN' => 'tanpa_jaminan',
            'LAINNYA' => 'lainnya'
        ];
        
        foreach ($jaminanUpdates as $old => $new) {
            $count = DB::table('registers')->where('jaminan', $old)->count();
            if ($count > 0) {
                DB::table('registers')->where('jaminan', $old)->update(['jaminan' => $new]);
                echo "   ✅ {$old} → {$new}: {$count} data\n";
            }
        }
        
        // 3. Update pekerjaan dari format lama ke string key
        echo "📝 Mengupdate pekerjaan...\n";
        $pekerjaanUpdates = [
            'PNS/ASN' => 'pns_asn',
            'TNI/POLRI' => 'tni_polri',
            'Karyawan Swasta' => 'swasta',
            'Wiraswasta' => 'wirausaha',
            'Petani' => 'petani',
            'Nelayan' => 'nelayan',
            'Buruh' => 'buruh',
            'Guru/Dosen' => 'guru',
            'Dokter/Bidan/Perawat' => 'medis',
            'Pelajar/Mahasiswa' => 'pelajar',
            'Ibu Rumah Tangga' => 'irt',
            'Pensiunan' => 'pensiunan',
            'Sopir/Ojek' => 'sopir',
            'Pedagang' => 'pedagang',
            'Lainnya' => 'lainnya'
        ];
        
        foreach ($pekerjaanUpdates as $old => $new) {
            $count = DB::table('registers')->where('pekerjaan', $old)->count();
            if ($count > 0) {
                DB::table('registers')->where('pekerjaan', $old)->update(['pekerjaan' => $new]);
                echo "   ✅ {$old} → {$new}: {$count} data\n";
            }
        }
        
        echo "Migrasi sistem enum terpusat selesai!\n";
    }

    /**
     * Reverse the migrations.
     * Rollback semua perubahan enum ke format lama
     */
    public function down(): void
    {
        echo "🔄 Memulai rollback sistem enum...\n";
        
        // Rollback jns_pengajuan
        echo "📝 Rollback jenis pengajuan...\n";
        DB::table('registers')->where('jns_pengajuan', 'umum')->update(['jns_pengajuan' => '1']);
        DB::table('registers')->where('jns_pengajuan', 'kta')->update(['jns_pengajuan' => '2']);
        DB::table('registers')->where('jns_pengajuan', 'lain_lain')->update(['jns_pengajuan' => '3']);
        
        // Rollback jaminan
        echo "📝 Rollback jaminan...\n";
        $jaminanRollback = [
            'tanah' => 'TANAH',
            'bangunan' => 'BANGUNAN',
            'kendaraan' => 'KENDARAAN', 
            'bpkb' => 'BPKB',
            'deposito' => 'DEPOSITO',
            'tanpa_jaminan' => 'TANPA JAMINAN',
            'lainnya' => 'LAINNYA'
        ];
        
        foreach ($jaminanRollback as $new => $old) {
            DB::table('registers')->where('jaminan', $new)->update(['jaminan' => $old]);
        }
        
        // Rollback pekerjaan
        echo "📝 Rollback pekerjaan...\n";
        $pekerjaanRollback = [
            'pns_asn' => 'PNS/ASN',
            'tni_polri' => 'TNI/POLRI',
            'swasta' => 'Karyawan Swasta',
            'wirausaha' => 'Wiraswasta',
            'petani' => 'Petani',
            'nelayan' => 'Nelayan',
            'buruh' => 'Buruh',
            'guru' => 'Guru/Dosen',
            'medis' => 'Dokter/Bidan/Perawat',
            'pelajar' => 'Pelajar/Mahasiswa',
            'irt' => 'Ibu Rumah Tangga',
            'pensiunan' => 'Pensiunan',
            'sopir' => 'Sopir/Ojek',
            'pedagang' => 'Pedagang',
            'lainnya' => 'Lainnya'
        ];
        
        foreach ($pekerjaanRollback as $new => $old) {
            DB::table('registers')->where('pekerjaan', $new)->update(['pekerjaan' => $old]);
        }
        
        echo "✅ Rollback selesai!\n";
    }
};
