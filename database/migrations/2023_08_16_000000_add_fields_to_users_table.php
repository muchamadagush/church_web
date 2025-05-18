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
        Schema::table('users', function (Blueprint $table) {
            // Make username and password nullable
            if (Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->change();
            }
            
            if (Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->change();
            }
            
            // Add new fields
            if (!Schema::hasColumn('users', 'birthplace')) {
                $table->string('birthplace')->nullable()->after('dateofbirth');
            }
            
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('birthplace');
            }
            
            if (!Schema::hasColumn('users', 'family_status')) {
                $table->string('family_status')->nullable()->after('gender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'birthplace')) {
                $table->dropColumn('birthplace');
            }
            
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
            
            if (Schema::hasColumn('users', 'family_status')) {
                $table->dropColumn('family_status');
            }
        });
    }
};
