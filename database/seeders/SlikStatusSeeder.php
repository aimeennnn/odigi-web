<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlikStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update status existing data jika ada
        // Note: Ini akan dijalankan setelah tabel dibuat
        
        // Update status dari format singkat ke format lengkap
        DB::table('sliks')
            ->where('status', 'proses')
            ->update(['status' => 'Dalam Proses']);
            
        DB::table('sliks')
            ->where('status', 'selesai')
            ->update(['status' => 'Selesai']);
            
        DB::table('sliks')
            ->where('status', 'ditolak')
            ->update(['status' => 'Ditolak']);
            
        // Update status menjadi 'Dalam Proses' untuk data yang hasil2 nya null
        DB::table('sliks')
            ->whereNull('hasil2')
            ->where('status', '!=', 'Dalam Proses')
            ->update(['status' => 'Dalam Proses']);
            
        $this->command->info('SLIK status updated successfully!');
    }
}
