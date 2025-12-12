<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('komites', function (Blueprint $table) {
            $table->id('id_komite');
            $table->unsignedBigInteger('id_reg');
            $table->date('tgl');
            $table->text('keterangan')->nullable();
            $table->string('keputusan', 100);
            $table->string('tipe_memorandum')->nullable()->index();
            $table->string('input_by')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
            $table->foreign('id_reg')->references('id_reg')->on('registers')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('komites');
    }
}; 