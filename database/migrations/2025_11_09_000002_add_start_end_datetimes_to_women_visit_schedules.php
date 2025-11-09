<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddStartEndDatetimesToWomenVisitSchedules extends Migration
{
    public function up()
    {
        // Tambah kolom baru (nullable sementara)
        Schema::table('women_visit_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('women_visit_schedules', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable()->after('church_id');
            }
            if (!Schema::hasColumn('women_visit_schedules', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            }
        });

        // Migrasikan data dari visit_date (jika ada)
        if (Schema::hasColumn('women_visit_schedules', 'visit_date')) {
            $rows = DB::table('women_visit_schedules')->select('id', 'visit_date')->get();
            foreach ($rows as $row) {
                if ($row->visit_date) {
                    $start = Carbon::parse($row->visit_date)->startOfDay();
                    $end = Carbon::parse($row->visit_date)->endOfDay();
                    DB::table('women_visit_schedules')->where('id', $row->id)->update([
                        'start_datetime' => $start->toDateTimeString(),
                        'end_datetime' => $end->toDateTimeString(),
                    ]);
                }
            }

            // Hapus kolom visit_date setelah migrasi
            Schema::table('women_visit_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('women_visit_schedules', 'visit_date')) {
                    $table->dropColumn('visit_date');
                }
            });
        }
    }

    public function down()
    {
        // Tambah kembali visit_date (nullable) jika belum ada
        if (!Schema::hasColumn('women_visit_schedules', 'visit_date')) {
            Schema::table('women_visit_schedules', function (Blueprint $table) {
                $table->date('visit_date')->nullable()->after('church_id');
            });
        }

        // Migrasikan data dari start_datetime -> visit_date (ambil tanggal start)
        if (Schema::hasColumn('women_visit_schedules', 'start_datetime')) {
            $rows = DB::table('women_visit_schedules')->select('id', 'start_datetime')->get();
            foreach ($rows as $row) {
                if ($row->start_datetime) {
                    $date = Carbon::parse($row->start_datetime)->toDateString();
                    DB::table('women_visit_schedules')->where('id', $row->id)->update([
                        'visit_date' => $date,
                    ]);
                }
            }
        }

        // Hapus kolom start_datetime & end_datetime
        Schema::table('women_visit_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('women_visit_schedules', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('women_visit_schedules', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
        });
    }
}
