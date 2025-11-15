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
        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->string('suffix')
                ->nullable();
            $table->uuid('position_id');
            $table->text('photo_path')
                ->nullable()
                ->comment('Path to the candidate photo');
            $table->text('description')
                ->nullable()
                ->comment('Candidate platform or manifesto');
            $table->json('data')
                ->nullable()
                ->comment('Additional data related to the candidate');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
