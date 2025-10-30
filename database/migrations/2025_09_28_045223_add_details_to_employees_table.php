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
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('age')->after('name')->nullable();
            $table->string('gender')->after('age')->nullable(); // Laki-laki, Perempuan
            $table->string('position')->after('gender')->nullable();
            $table->date('join_date')->after('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['age', 'gender', 'position', 'join_date']);
        });
    }
};