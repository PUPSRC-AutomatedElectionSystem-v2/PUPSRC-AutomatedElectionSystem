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
        Schema::create('voting_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->dateTime('start_time')->comment('The start time for voting schedule');
            $table->dateTime('end_time')->comment('The end time for voting schedule');
            $table->json('matching_rules')->nullable()->comment('Structured JSON expression tree for matching users (AND/OR/NOT and leaf conditions)');
            $table->boolean('is_active')->default(true)->comment('Indicates if the voting schedule is currently active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_schedules');
    }
};
