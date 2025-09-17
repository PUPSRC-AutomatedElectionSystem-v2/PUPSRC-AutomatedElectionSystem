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
        Schema::create('voters', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->tinyInteger('year_level');
            $table->string('section', 128);
            $table->tinyInteger('voter_status_id', unsigned: true);
            $table->foreign('voter_status_id')->references('id')->on('voter_statuses');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};
