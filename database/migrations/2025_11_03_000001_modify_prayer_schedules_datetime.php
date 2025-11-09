<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prayer_schedules', function (Blueprint $table) {
            $table->dropColumn('tanggal');
            $table->datetime('start_datetime')->after('id');
            $table->datetime('end_datetime')->after('start_datetime');
        });
    }

    public function down()
    {
        Schema::table('prayer_schedules', function (Blueprint $table) {
            $table->date('tanggal')->after('id');
            $table->dropColumn('start_datetime');
            $table->dropColumn('end_datetime');
        });
    }
};
