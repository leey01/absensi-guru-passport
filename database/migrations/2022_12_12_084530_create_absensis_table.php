<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->enum('keterangan', ['masuk', 'pulang']);
            $table->boolean('is_valid_masuk');
            $table->string('catatan_masuk');
            $table->time('waktu_masuk');
            $table->date('tanggal_masuk');
            $table->string('foto_masuk');
            $table->string('lokasi_masuk');
            $table->string('latitude_masuk');
            $table->string('longitude_masuk');
            $table->boolean('is_valid_pulang')->nullable();
            $table->string('catatan_pulang')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->date('tanggal_pulang')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->string('lokasi_pulang')->nullable();
            $table->string('latitude_pulang')->nullable();
            $table->string('longitude_pulang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensis');
    }
};
