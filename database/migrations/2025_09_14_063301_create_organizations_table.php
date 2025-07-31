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
        Schema::create('organizations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('short_name')->unique();
            $table->unsignedSmallInteger('contact_id');
            $table->string('name');
            $table->unsignedSmallInteger('category_id');
            $table->boolean('should_copy_from_other_org');
            $table->boolean('allow_cross_membership');
            $table->json('theme');
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
