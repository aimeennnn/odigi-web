<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('komites', function (Blueprint $table) {
            $table->string('keputusan', 100)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('komites', function (Blueprint $table) {
            $table->string('keputusan', 100)->nullable(false)->change();
        });
    }
};
