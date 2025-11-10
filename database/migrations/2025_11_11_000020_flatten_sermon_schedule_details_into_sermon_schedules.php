<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to sermon_schedules if not exist
        Schema::table('sermon_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('sermon_schedules', 'church_id')) {
                $table->foreignId('church_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('sermon_schedules', 'month')) {
                $table->string('month')->nullable();
            }
            if (!Schema::hasColumn('sermon_schedules', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable();
            }
            if (!Schema::hasColumn('sermon_schedules', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable();
            }
            if (!Schema::hasColumn('sermon_schedules', 'created_from_detail')) {
                $table->boolean('created_from_detail')->default(false);
            }
        });

        if (Schema::hasTable('sermon_schedule_details')) {
            // Copy each detail row into a new sermon_schedules row
            $details = DB::table('sermon_schedule_details')->get();
            foreach ($details as $detail) {
                // Pull parent pengkhotbah
                $parent = DB::table('sermon_schedules')->where('id', $detail->sermon_schedule_id)->first();
                if (!$parent) { continue; }

                DB::table('sermon_schedules')->insert([
                    'pengkhotbah' => $parent->pengkhotbah,
                    'church_id' => $detail->church_id,
                    'month' => $detail->month ?? null,
                    'start_datetime' => $detail->start_datetime ?? null,
                    'end_datetime' => $detail->end_datetime ?? null,
                    'created_from_detail' => true,
                    'created_at' => $detail->created_at ?? $parent->created_at,
                    'updated_at' => $detail->updated_at ?? $parent->updated_at,
                ]);
            }

            // Remove original parent rows that served only as containers (no church_id set and had details)
            $parentIds = DB::table('sermon_schedule_details')->distinct()->pluck('sermon_schedule_id');
            DB::table('sermon_schedules')
                ->whereIn('id', $parentIds)
                ->whereNull('church_id')
                ->delete();

            // Drop detail table
            Schema::dropIfExists('sermon_schedule_details');
        }

        // Optional index for faster queries
        Schema::table('sermon_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('sermon_schedules', 'start_datetime')) return; // safety
            $table->index(['start_datetime']);
        });
    }

    public function down(): void
    {
        // Recreate detail table (best-effort reconstruction)
        if (!Schema::hasTable('sermon_schedule_details')) {
            Schema::create('sermon_schedule_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sermon_schedule_id')->constrained()->onDelete('cascade');
                $table->foreignId('church_id')->constrained()->onDelete('cascade');
                $table->string('month');
                $table->dateTime('start_datetime')->nullable();
                $table->dateTime('end_datetime')->nullable();
                $table->timestamps();
            });
        }

        // Split flattened rows back by creating a new parent per unique pengkhotbah and migrating its rows
        $flattened = DB::table('sermon_schedules')->where('created_from_detail', true)->get();
        $grouped = [];
        foreach ($flattened as $row) {
            $grouped[$row->pengkhotbah][] = $row;
        }

        foreach ($grouped as $pengkhotbah => $rows) {
            // Create parent
            $parentId = DB::table('sermon_schedules')->insertGetId([
                'pengkhotbah' => $pengkhotbah,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($rows as $r) {
                DB::table('sermon_schedule_details')->insert([
                    'sermon_schedule_id' => $parentId,
                    'church_id' => $r->church_id,
                    'month' => $r->month ?? 'jan',
                    'start_datetime' => $r->start_datetime,
                    'end_datetime' => $r->end_datetime,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                ]);
                // Delete flattened row
                DB::table('sermon_schedules')->where('id', $r->id)->delete();
            }
        }

        // Remove new columns from sermon_schedules (if desired)
        Schema::table('sermon_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('sermon_schedules', 'created_from_detail')) {
                $table->dropColumn('created_from_detail');
            }
            if (Schema::hasColumn('sermon_schedules', 'church_id')) {
                $table->dropConstrainedForeignId('church_id');
            }
            foreach (['month','start_datetime','end_datetime'] as $col) {
                if (Schema::hasColumn('sermon_schedules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
