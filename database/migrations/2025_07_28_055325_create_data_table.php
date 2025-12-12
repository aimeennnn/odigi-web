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
        Schema::create('data', function (Blueprint $table) {
            $table->bigIncrements('id_data');
            $table->unsignedBigInteger('id_reg');
            $table->string('jenis_data');
            $table->text('keterangan')->nullable();
            $table->string('file')->nullable();
            $table->string('input_by')->nullable();
            $table->timestamps();
            
            $table->foreign('id_reg')->references('id_reg')->on('registers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
