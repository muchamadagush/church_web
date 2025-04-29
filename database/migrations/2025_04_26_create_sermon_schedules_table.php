<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sermon_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('pengkhotbah');
            $table->timestamps();
        });

        Schema::create('sermon_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('month');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sermon_schedule_details');
        Schema::dropIfExists('sermon_schedules');
    }
};