<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('absensis', function (Blueprint $table) {
            DB::statement("ALTER TABLE absensis MODIFY COLUMN keterangan ENUM('masuk', 'pulang', 'alpha', 'izin', 'libur') DEFAULT 'alpha'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('absensis', function (Blueprint $table) {
            DB::statement("ALTER TABLE absensis MODIFY COLUMN keterangan ENUM('masuk', 'pulang', 'alpha', 'izin') DEFAULT 'alpha'");
        });
    }
};
