<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorshipSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('worship_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('church_id')->nullable()->constrained()->onDelete('set null')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->enum('type', ['doa', 'khotbah', 'kunjungan_ketua', 'kunjungan_wanita']);
            $table->dateTime('schedule_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('jan')->default(false);
            $table->boolean('feb')->default(false);
            $table->boolean('mar')->default(false);
            $table->boolean('apr')->default(false);
            $table->boolean('may')->default(false);
            $table->boolean('jun')->default(false);
            $table->boolean('jul')->default(false);
            $table->boolean('aug')->default(false);
            $table->boolean('sep')->default(false);
            $table->boolean('oct')->default(false);
            $table->boolean('nov')->default(false);
            $table->boolean('dec')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('worship_schedules');
    }
}
