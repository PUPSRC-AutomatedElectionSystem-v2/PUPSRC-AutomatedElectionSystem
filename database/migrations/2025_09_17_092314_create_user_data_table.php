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
        // Centralized data of users in the Application
        // indentity_id is unique and can be reference by tenant users regardless of which tenant it falls if do have permission
        Schema::create('users_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identity_id')->comment('i.e. Student ID')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_data');
    }
};
