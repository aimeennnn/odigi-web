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
        Schema::create('banks', function (Blueprint $table) {
            $table->bigIncrements('id_bank');
            $table->unsignedBigInteger('id_reg');
            $table->string('nama_bank');
            $table->string('no_rekening');
            $table->string('file')->nullable();
            $table->string('hasil')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('banks');
    }
};
