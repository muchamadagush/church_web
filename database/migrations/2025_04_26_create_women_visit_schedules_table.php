<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('women_visit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->date('visit_date');
            $table->string('worship_leader');
            $table->string('preacher');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('women_visit_schedules');
    }
};