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
        Schema::create('youth_visit_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('schedule_date');
            $table->foreignId('church_id')->constrained('churches')->onDelete('cascade');
            $table->time('time');
            $table->string('worship_leader');
            $table->string('speaker');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('youth_visit_schedules');
    }
};