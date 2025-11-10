<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('christmas_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('christmas_schedules', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable()->after('id');
            }
            if (!Schema::hasColumn('christmas_schedules', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            }
        });

        // Backfill datetimes from existing columns if present
        $hasDate = Schema::hasColumn('christmas_schedules', 'schedule_date');
        $hasStart = Schema::hasColumn('christmas_schedules', 'start_time');
        $hasEnd = Schema::hasColumn('christmas_schedules', 'end_time');

        if ($hasDate) {
            if ($hasStart && $hasEnd) {
                DB::statement("UPDATE christmas_schedules SET start_datetime = CONCAT(schedule_date, ' ', start_time), end_datetime = CONCAT(schedule_date, ' ', end_time) WHERE start_datetime IS NULL AND schedule_date IS NOT NULL");
            } else {
                // Fallback: set both to midnight and 1 hour later if only date exists
                DB::statement("UPDATE christmas_schedules SET start_datetime = CONCAT(schedule_date, ' 00:00:00') WHERE start_datetime IS NULL AND schedule_date IS NOT NULL");
                DB::statement("UPDATE christmas_schedules SET end_datetime = DATE_ADD(start_datetime, INTERVAL 1 HOUR) WHERE end_datetime IS NULL AND start_datetime IS NOT NULL");
            }
        }

        // Drop old columns if they exist
        Schema::table('christmas_schedules', function (Blueprint $table) use ($hasDate, $hasStart, $hasEnd) {
            $drops = [];
            if ($hasDate) { $drops[] = 'schedule_date'; }
            if ($hasStart) { $drops[] = 'start_time'; }
            if ($hasEnd) { $drops[] = 'end_time'; }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old columns
        Schema::table('christmas_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('christmas_schedules', 'schedule_date')) {
                $table->date('schedule_date')->nullable()->after('id');
            }
            if (!Schema::hasColumn('christmas_schedules', 'start_time')) {
                $table->time('start_time')->nullable()->after('schedule_date');
            }
            if (!Schema::hasColumn('christmas_schedules', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
        });

        // Backfill from datetimes
        if (Schema::hasColumn('christmas_schedules', 'start_datetime')) {
            DB::statement("UPDATE christmas_schedules SET schedule_date = DATE(start_datetime) WHERE schedule_date IS NULL AND start_datetime IS NOT NULL");
            DB::statement("UPDATE christmas_schedules SET start_time = TIME(start_datetime) WHERE start_time IS NULL AND start_datetime IS NOT NULL");
        }
        if (Schema::hasColumn('christmas_schedules', 'end_datetime')) {
            DB::statement("UPDATE christmas_schedules SET end_time = TIME(end_datetime) WHERE end_time IS NULL AND end_datetime IS NOT NULL");
        }

        // Drop datetime columns
        Schema::table('christmas_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('christmas_schedules', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('christmas_schedules', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
        });
    }
};
