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
            $pattern = EmailPattern::RAW;

            // Add a named CHECK constraint for basic email format validation.
            // The SQL differs per driver: MySQL supports REGEXP, PostgreSQL uses the ~ operator,
            // and SQLite does not support adding named CHECK constraints via ALTER TABLE reliably.
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                // Escape for MySQL single-quoted string
                $mysqlPattern = addslashes($pattern);
                DB::statement(sprintf(
                    "ALTER TABLE organization_contacts ADD CONSTRAINT chk_org_contacts_email_format CHECK (email REGEXP '%s')",
                    $mysqlPattern
                ));
            } elseif ($driver === 'pgsql') {
                // PostgreSQL uses the POSIX regex operator (~). Double single-quotes for SQL string.
                $pgPattern = str_replace("'", "''", $pattern);
                DB::statement(sprintf(
                    "ALTER TABLE organization_contacts ADD CONSTRAINT chk_org_contacts_email_format CHECK (email ~ '%s')",
                    $pgPattern
                ));
            } else {
                // Skip adding the DB-level constraint on other drivers (e.g. sqlite) to avoid syntax errors.
                // Validation should still be enforced at the application level.
            }
        } catch (\Throwable $e) {
            report($e);
            // Ignore if the DB/driver doesn't support adding the constraint.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                // MySQL: DROP CHECK <name>
                DB::statement('ALTER TABLE organization_contacts DROP CHECK chk_org_contacts_email_format');
            } elseif ($driver === 'pgsql') {
                // Postgres: DROP CONSTRAINT IF EXISTS <name>
                DB::statement('ALTER TABLE organization_contacts DROP CONSTRAINT IF EXISTS chk_org_contacts_email_format');
            } else {
                // For other drivers (sqlite) there's no portable way to drop a named CHECK via ALTER TABLE here.
            }
        } catch (\Throwable $e1) {
            report($e1);
            // ignore if constraint drop not supported
        }

        Schema::dropIfExists('organization_contacts');
    }
};
