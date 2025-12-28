<?php

namespace Modules\Shared\Contracts\Messaging;

/**
 * Value object representing a domain message for inter-service communication.
 *
 * This DTO ensures type safety and consistent structure across services.
 * When apps are split, this contract should live in a shared composer package.
 */
final readonly class DomainMessage
{
    public function __construct(
        public string $type,
        public int $version,
        public string $id,
        public string $timestamp,
        public array $data,
        public ?string $idempotencyKey = null,
        public ?string $source = null,
        public ?string $correlationId = null,
        public array $metadata = [],
    ) {}

    /**
     * Create a new DomainMessage with auto-generated id and timestamp.
     */
    public static function create(
        string $type,
        array $data,
        int $version = 1,
        ?string $idempotencyKey = null,
        ?string $source = null,
        ?string $correlationId = null,
        array $metadata = [],
    ): self {
        return new self(
            type: $type,
            version: $version,
            id: (string) \Illuminate\Support\Str::uuid(),
            timestamp: now()->toIso8601String(),
            data: $data,
            idempotencyKey: $idempotencyKey ?? (string) \Illuminate\Support\Str::uuid(),
            source: $source ?? config('app.name'),
            correlationId: $correlationId,
            metadata: $metadata,
        );
    }

    /**
     * Reconstruct from array (e.g., from JSON decode).
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            type: $payload['type'] ?? throw new \InvalidArgumentException('Missing message type'),
            version: (int) ($payload['version'] ?? 1),
            id: $payload['id'] ?? throw new \InvalidArgumentException('Missing message id'),
            timestamp: $payload['timestamp'] ?? now()->toIso8601String(),
            data: $payload['data'] ?? [],
            idempotencyKey: $payload['idempotency_key'] ?? null,
            source: $payload['source'] ?? null,
            correlationId: $payload['correlation_id'] ?? null,
            metadata: $payload['metadata'] ?? [],
        );
    }

    /**
     * Convert to array for JSON encoding.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'version' => $this->version,
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'data' => $this->data,
            'idempotency_key' => $this->idempotencyKey,
            'source' => $this->source,
            'correlation_id' => $this->correlationId,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convert to JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
