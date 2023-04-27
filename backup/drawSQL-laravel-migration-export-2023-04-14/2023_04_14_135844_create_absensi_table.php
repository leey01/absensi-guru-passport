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
        Schema::disableForeignKeyConstraints();

        Schema::create('absensi', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->foreign('user_id')->references('id')->on('user');
            $table->enum('keterangan', ['masuk', 'pulang', 'izin']);
            $table->boolean('is_valid_masuk')->nullable();
            $table->string('catatan_masuk')->nullable();
            $table->time('waktu_masuk')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('lokasi_masuk')->nullable();
            $table->string('latitude_masuk')->nullable();
            $table->string('longitude_masuk')->nullable();
            $table->boolean('is_valid_pulang')->nullable();
            $table->string('catatan_pulang')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->date('tanggal_pulang')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->string('lokasi_pulang')->nullable();
            $table->string('latitude_pulang')->nullable();
            $table->string('longitude_pulang')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
