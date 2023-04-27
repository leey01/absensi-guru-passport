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

        Schema::create('kategori_karyawan_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->foreign('user_id')->references('id')->on('user');
            $table->integer('kategori_id')->index();
            $table->foreign('kategori_id')->references('id')->on('kategori_karyawan');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_karyawan_user');
    }
};
