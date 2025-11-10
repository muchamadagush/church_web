<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sermon_schedule_details', 'month')) {
            Schema::table('sermon_schedule_details', function (Blueprint $table) {
                $table->string('month')->nullable()->after('church_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sermon_schedule_details', 'month')) {
            Schema::table('sermon_schedule_details', function (Blueprint $table) {
                $table->dropColumn('month');
            });
        }
    }
};
