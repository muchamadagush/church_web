<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('sermon_schedules', 'month')) {
            Schema::table('sermon_schedules', function (Blueprint $table) {
                $table->dropColumn('month');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('sermon_schedules', 'month')) {
            Schema::table('sermon_schedules', function (Blueprint $table) {
                $table->string('month', 3)->nullable()->index();
            });
        }
    }
};
