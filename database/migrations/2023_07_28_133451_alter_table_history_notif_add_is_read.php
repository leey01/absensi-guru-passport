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
        Schema::table("history_notifs", function (Blueprint $table) {
            $table->boolean("is_read")->default(false)->after("event_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("history_notifs", function (Blueprint $table) {
            $table->dropColumn("is_read");
        });
    }
};
