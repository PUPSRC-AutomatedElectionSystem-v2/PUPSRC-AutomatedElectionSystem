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
        Schema::create('registration_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->dateTime('start_time')->comment('The start time for voter registration');
            $table->dateTime('end_time')->comment('The end time for voter registration');
            $table->unsignedInteger('priority')->default(0)->comment('Higher = more specific; used to resolve overlapping schedules');
            $table->boolean('is_active')->default(true)->comment('Indicates if the registration schedule is currently active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_schedules');
    }
};
