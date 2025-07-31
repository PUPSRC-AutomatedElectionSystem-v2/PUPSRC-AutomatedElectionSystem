<?php

use App\Rules\Email\EmailPattern;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_contacts', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->foreignUlid('organization_id');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('threads')->nullable();
            $table->string('discord')->nullable();
            $table->timestamps();
        });

        try {
            $pattern = addslashes(EmailPattern::RAW);

            // Add a named CHECK constraint for basic email format validation (MySQL REGEXP).
            DB::statement(sprintf(
                "ALTER TABLE organization_contacts ADD CONSTRAINT chk_org_contacts_email_format CHECK (email REGEXP '%s')",
                $pattern
            ));
        } catch (\Throwable $e) {
            // Ignore if the DB/driver doesn't support adding the constraint.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE organization_contacts DROP CONSTRAINT chk_org_contacts_email_format');
        } catch (\Throwable $e1) {
            try {
                DB::statement('ALTER TABLE organization_contacts DROP CHECK chk_org_contacts_email_format');
            } catch (\Throwable $e2) {
                // ignore if constraint drop not supported
            }
        }
        Schema::dropIfExists('organization_contacts');
    }
};
