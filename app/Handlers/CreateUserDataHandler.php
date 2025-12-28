<?php

namespace App\Handlers;

use App\Models\Central\UserData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;

/**
 * Central app handler for user.create messages.
 *
 * Consumes messages published by organization-admin module (or other apps)
 * and creates UserData records in the central database.
 *
 * After creating UserData, publishes a 'userdata.created' message so
 * organization-admin can create the tenant User and Voter.
 */
class CreateUserDataHandler implements MessageHandlerInterface
{
    private const PUBLISH_EXCHANGE = 'central';

    private const PUBLISH_ROUTING_KEY = 'userdata.created';

    public function __construct(
        private readonly MessagePublisherInterface $publisher,
    ) {}

    public function queue(): string
    {
        return 'organization.user';
    }

    public function handles(): string
    {
        return 'user.create';
    }

    public function supportedVersions(): array
    {
        return [1];
    }

    public function handle(DomainMessage $message): void
    {
        Log::info('Central app handling user.create message', [
            'message_id' => $message->id,
            'idempotency_key' => $message->idempotencyKey,
            'source' => $message->source,
        ]);

        $data = $message->data;

        if (empty($data)) {
            Log::warning('Empty data in user.create message', [
                'message_id' => $message->id,
            ]);

            return;
        }

        // Idempotency check: skip if already processed
        if ($this->alreadyProcessed($message)) {
            Log::info('Message already processed, skipping', [
                'message_id' => $message->id,
                'idempotency_key' => $message->idempotencyKey,
            ]);

            return;
        }

        // Map incoming data to UserData fields
        $userData = $this->mapToUserData($data);

        // Validate
        $validator = Validator::make($userData, $this->validationRules($userData));

        if ($validator->fails()) {
            Log::error('Validation failed for user.create message', [
                'message_id' => $message->id,
                'errors' => $validator->errors()->toArray(),
                'data' => $userData,
            ]);

            return;
        }

        // Create the user data record and publish confirmation
        try {
            DB::transaction(function () use ($validator, $message, $data) {
                $validated = $validator->validated();
                $userDataRecord = UserData::create($validated);

                Log::info('UserData created successfully', [
                    'message_id' => $message->id,
                    'identity_id' => $validated['identity_id'] ?? null,
                ]);

                // Publish userdata.created for organization-admin to consume
                $this->publishUserDataCreated($userDataRecord, $data, $message);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create UserData', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry/DLQ
        }
    }

    /**
     * Publish userdata.created message for organization-admin module.
     */
    private function publishUserDataCreated(UserData $userDataRecord, array $originalData, DomainMessage $originalMessage): void
    {
        $message = DomainMessage::create(
            type: 'userdata.created',
            data: [
                'identity_id' => $userDataRecord->identity_id,
                'user_data_id' => $userDataRecord->id,
                'tenant_id' => $originalData['tenant_id'] ?? null,
                'email' => $originalData['email'] ?? null,
                'first_name' => $userDataRecord->first_name,
                'last_name' => $userDataRecord->last_name,
                'middle_name' => $userDataRecord->middle_name,
                'suffix' => $userDataRecord->suffix,
                'cor_file' => $originalData['cor_file'] ?? null,
                'original_data' => $originalData,
            ],
            version: 1,
            correlationId: $originalMessage->id,
        );

        $this->publisher->publish($message, self::PUBLISH_EXCHANGE, self::PUBLISH_ROUTING_KEY);

        Log::info('Published userdata.created message', [
            'message_id' => $message->id,
            'identity_id' => $userDataRecord->identity_id,
            'tenant_id' => $originalData['tenant_id'] ?? null,
        ]);
    }

    /**
     * Map incoming message data to UserData model fields.
     */
    private function mapToUserData(array $data): array
    {
        // Extract additional fields into the 'data' JSON column
        $additionalData = array_filter([
            'email' => $data['email'] ?? null,
            'course' => $data['course'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'section' => $data['section'] ?? null,
        ]);

        return [
            'identity_id' => $data['identity_id'] ?? $data['student_id'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'suffix' => $data['suffix'] ?? null,
            'organizations' => $data['organizations'] ?? [],
            'data' => ! empty($additionalData) ? $additionalData : null,
        ];
    }

    /**
     * Get validation rules, adjusting for updates vs creates.
     */
    private function validationRules(array $data): array
    {
        $identityId = $data['identity_id'] ?? null;

        return [
            'identity_id' => ['required', 'string', 'max:255', "unique:users_data,identity_id,{$identityId},identity_id"],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:32'],
            'organizations' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
        ];
    }

    /**
     * Check if this message was already processed (idempotency).
     */
    private function alreadyProcessed(DomainMessage $message): bool
    {
        // Option 1: Check by identity_id if present
        $identityId = $message->data['identity_id'] ?? $message->data['student_id'] ?? null;

        if ($identityId && UserData::where('identity_id', $identityId)->exists()) {
            return true;
        }

        // Option 2: Could also track processed message IDs in a separate table
        // ProcessedMessage::where('message_id', $message->id)->exists()

        return false;
    }
}
