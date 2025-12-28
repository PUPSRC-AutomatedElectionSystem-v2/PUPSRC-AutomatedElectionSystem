<?php

namespace Modules\OrganizationAdmin\Handlers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\OrganizationAdmin\Models\User;
use Modules\OrganizationAdmin\Models\Voter;
use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;

/**
 * Organization-admin handler for userdata.created messages.
 *
 * Consumes messages published by the central app after UserData is created.
 * Creates the tenant User and Voter records within the tenant context.
 */
class CreateTenantUserHandler implements MessageHandlerInterface
{
    public function queue(): string
    {
        return 'central.userdata';
    }

    public function handles(): string
    {
        return 'userdata.created';
    }

    public function supportedVersions(): array
    {
        return [1];
    }

    public function handle(DomainMessage $message): void
    {
        Log::info('Organization-admin handling userdata.created message', [
            'message_id' => $message->id,
            'correlation_id' => $message->correlationId,
        ]);

        $data = $message->data;

        if (empty($data['tenant_id'])) {
            Log::warning('No tenant_id in userdata.created message, skipping', [
                'message_id' => $message->id,
            ]);

            return;
        }

        if (empty($data['identity_id'])) {
            Log::warning('No identity_id in userdata.created message, skipping', [
                'message_id' => $message->id,
            ]);

            return;
        }

        // Initialize tenant context and create records
        $this->createTenantRecords($data, $message);
    }

    /**
     * Create User and Voter records within tenant context.
     */
    private function createTenantRecords(array $data, DomainMessage $message): void
    {
        $tenantId = $data['tenant_id'];

        try {
            tenancy()->initialize($tenantId);

            // Idempotency check: skip if user already exists
            if ($this->userAlreadyExists($data['identity_id'])) {
                Log::info('Tenant user already exists, skipping', [
                    'message_id' => $message->id,
                    'identity_id' => $data['identity_id'],
                    'tenant_id' => $tenantId,
                ]);

                return;
            }

            DB::transaction(function () use ($data, $message) {
                // Create Voter first (referenced by User via morph)
                $voter = $this->createVoter($data);

                // Create User linked to Voter
                $user = $this->createUser($data, $voter);

                Log::info('Tenant User and Voter created successfully', [
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'voter_id' => $voter->id,
                    'identity_id' => $data['identity_id'],
                    'tenant_id' => $data['tenant_id'],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create tenant User/Voter', [
                'message_id' => $message->id,
                'tenant_id' => $tenantId,
                'identity_id' => $data['identity_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw for retry/DLQ
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Create the Voter record.
     */
    private function createVoter(array $data): Voter
    {
        return Voter::create([
            'cor_file' => $data['cor_file'] ?? '',
        ]);
    }

    /**
     * Create the User record linked to the Voter.
     */
    private function createUser(array $data, Voter $voter): User
    {
        $originalData = $data['original_data'] ?? [];

        return User::create([
            'account_type' => 'voter',
            'account_id' => $voter->id,
            'identity_id' => $data['identity_id'],
            'email' => $data['email'] ?? $this->generatePlaceholderEmail($data['identity_id']),
            'password' => Hash::make($this->generateDefaultPassword($data)),
            'data' => array_filter([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'suffix' => $data['suffix'] ?? null,
                'course' => $originalData['course'] ?? null,
                'year_level' => $originalData['year_level'] ?? null,
                'section' => $originalData['section'] ?? null,
            ]),
        ]);
    }

    /**
     * Check if user already exists in tenant.
     */
    private function userAlreadyExists(string $identityId): bool
    {
        return User::where('identity_id', $identityId)->exists();
    }

    /**
     * Generate a placeholder email if none provided.
     */
    private function generatePlaceholderEmail(string $identityId): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', $identityId);

        return strtolower($sanitized) . '@placeholder.local';
    }

    /**
     * Generate default password for new user.
     *
     * Uses the MakeDefaultPassword service if available, otherwise generates from identity_id.
     */
    private function generateDefaultPassword(array $data): string
    {
        // Try to use the application's default password service
        if (app()->bound('make-default-password')) {
            $service = app('make-default-password');

            return $service->forUser($data);
        }

        // Fallback: use identity_id as password (should be changed on first login)
        return $data['identity_id'] ?? 'changeme123';
    }
}
