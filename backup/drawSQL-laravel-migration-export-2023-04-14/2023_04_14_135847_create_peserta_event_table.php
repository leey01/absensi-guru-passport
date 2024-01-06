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

        Schema::create('peserta_event', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->index();
            $table->foreign('event_id')->references('id')->on('event');
            $table->integer('user_id')->index();
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_event');
    }
};
