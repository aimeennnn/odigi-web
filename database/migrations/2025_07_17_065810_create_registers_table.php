<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->bigIncrements('id_reg');
            $table->string('nomor');
            $table->string('nama')->nullable();
            $table->string('jenis_entitas')->default('perorangan');
            $table->string('nama_badan_usaha')->nullable();
            $table->string('jenis_dokumen_usaha')->nullable();
            $table->string('nomor_legalitas_usaha')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->text('alamat_usaha')->nullable();
            $table->string('jns_kelamin')->nullable();
            $table->string('no_identitas')->nullable();
            $table->string('jns_identitas')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->text('alamat')->nullable();
            $table->date('tgl_pengajuan');
            $table->string('jns_pengajuan');
            $table->decimal('nominal_pengajuan', 18, 2);
            $table->string('jw_pengajuan');
            $table->string('jaminan');
            $table->string('status');
            $table->date('tanggal_realisasi')->nullable();
            $table->bigInteger('nominal_disetujui')->nullable();
            $table->unsignedBigInteger('id_user');
            $table->string('input_by')->nullable();
            $table->timestamps();

            // Foreign key constraint dihapus sementara karena tabel user belum dibuat
            // $table->foreign('id_user')->references('id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registers');
    }
};
