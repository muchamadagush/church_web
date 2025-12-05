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
        Schema::table('sermon_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('sermon_schedules', 'month')) {
                $table->string('month')->nullable()->after('church_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sermon_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('sermon_schedules', 'month')) {
                $table->dropColumn('month');
            }
        });
    }
};
