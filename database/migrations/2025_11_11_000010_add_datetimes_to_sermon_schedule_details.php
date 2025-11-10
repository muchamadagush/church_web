<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sermon_schedule_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sermon_schedule_details', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable()->after('church_id');
            }
            if (!Schema::hasColumn('sermon_schedule_details', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sermon_schedule_details', function (Blueprint $table) {
            if (Schema::hasColumn('sermon_schedule_details', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('sermon_schedule_details', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
        });
    }
};
