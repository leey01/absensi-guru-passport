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
        Schema::table('absensis', function (Blueprint $table) {
            $table->boolean('valid_masuk')->nullable()->change();
            $table->boolean('valid_pulang')->nullable()->change();
            $table->boolean('is_valid_masuk')->nullable()->change();
            $table->boolean('is_valid_pulang')->nullable()->change();
            $table->boolean('isvld_wkt_masuk')->nullable()->change();
            $table->boolean('isvld_wkt_pulang')->nullable()->change();
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
            $table->boolean('valid_masuk')->change();
            $table->boolean('valid_pulang')->change();
            $table->boolean('is_valid_masuk')->change();
            $table->boolean('is_valid_pulang')->change();
            $table->boolean('isvld_wkt_masuk')->change();
            $table->boolean('isvld_wkt_pulang')->change();
        });
    }
};
