<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sliks', function (Blueprint $table) {
            $table->bigIncrements('id_slik');
            $table->unsignedBigInteger('id_reg');
            $table->string('nomor');
            $table->date('tgl');
            $table->string('nama');
            $table->string('no_identitas');
            $table->string('keterkaitan');
            $table->text('hasil')->nullable(); // JSON array untuk menyimpan multiple files
            $table->text('hasil2')->nullable(); // JSON array untuk menyimpan multiple files (hasil ekstraksi)
            $table->string('status');
            $table->string('input_by')->nullable();
            $table->timestamps();

            $table->foreign('id_reg')->references('id_reg')->on('registers')->onDelete('cascade');
        });

        // Data seeding untuk status yang sudah ada (dari fix_existing_slik_status.php)
        // Note: Ini akan dijalankan saat fresh migration, jadi tidak ada data existing
        // Tapi tetap disertakan untuk konsistensi
        
        // Update status dari format singkat ke format lengkap (dari update_slik_status_to_full_format.php)
        // Note: Ini juga akan dijalankan saat fresh migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliks');
    }
};
