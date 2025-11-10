<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youth_visit_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('youth_visit_schedules', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable()->after('id');
            }
            if (!Schema::hasColumn('youth_visit_schedules', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            }
        });

        $hasDate = Schema::hasColumn('youth_visit_schedules', 'schedule_date');
        $hasTime = Schema::hasColumn('youth_visit_schedules', 'time');

        if ($hasDate) {
            if ($hasTime) {
                DB::statement("UPDATE youth_visit_schedules SET start_datetime = CONCAT(schedule_date, ' ', time) WHERE start_datetime IS NULL AND schedule_date IS NOT NULL");
                DB::statement("UPDATE youth_visit_schedules SET end_datetime = DATE_ADD(start_datetime, INTERVAL 2 HOUR) WHERE end_datetime IS NULL AND start_datetime IS NOT NULL");
            } else {
                DB::statement("UPDATE youth_visit_schedules SET start_datetime = CONCAT(schedule_date, ' 00:00:00') WHERE start_datetime IS NULL AND schedule_date IS NOT NULL");
                DB::statement("UPDATE youth_visit_schedules SET end_datetime = DATE_ADD(start_datetime, INTERVAL 1 HOUR) WHERE end_datetime IS NULL AND start_datetime IS NOT NULL");
            }
        }

        Schema::table('youth_visit_schedules', function (Blueprint $table) use ($hasDate, $hasTime) {
            $drops = [];
            if ($hasDate) { $drops[] = 'schedule_date'; }
            if ($hasTime) { $drops[] = 'time'; }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }

    public function down(): void
    {
        Schema::table('youth_visit_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('youth_visit_schedules', 'schedule_date')) {
                $table->date('schedule_date')->nullable()->after('id');
            }
            if (!Schema::hasColumn('youth_visit_schedules', 'time')) {
                $table->time('time')->nullable()->after('schedule_date');
            }
        });

        if (Schema::hasColumn('youth_visit_schedules', 'start_datetime')) {
            DB::statement("UPDATE youth_visit_schedules SET schedule_date = DATE(start_datetime) WHERE schedule_date IS NULL AND start_datetime IS NOT NULL");
            DB::statement("UPDATE youth_visit_schedules SET time = TIME(start_datetime) WHERE time IS NULL AND start_datetime IS NOT NULL");
        }

        Schema::table('youth_visit_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('youth_visit_schedules', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('youth_visit_schedules', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
        });
    }
};
