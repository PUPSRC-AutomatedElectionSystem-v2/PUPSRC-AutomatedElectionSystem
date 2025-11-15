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
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')
                ->nullable();
            $table->tinyInteger('votable_count')
                ->default(1)->comment('Number of candidates that can be voted for this position');
            $table->integer('order')
                ->nullable()
                ->comment('Order of the position in the list');
            $table->uuid('current_winner_id')
                ->nullable()
                ->comment('References the candidate who is the current winner for this position');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('current_winner_id')
                ->references('id')
                ->on('candidates')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
