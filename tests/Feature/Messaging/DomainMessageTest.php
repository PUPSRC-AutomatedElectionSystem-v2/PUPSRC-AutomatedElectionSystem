<?php

namespace Tests\Feature\Messaging;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DomainMessageTest extends TestCase
{
    #[Test]
    public function it_creates_a_domain_message_with_auto_generated_fields(): void
    {
        $message = DomainMessage::create(
            type: 'user.create',
            data: ['first_name' => 'John', 'last_name' => 'Doe'],
            version: 1,
        );

        $this->assertEquals('user.create', $message->type);
        $this->assertEquals(1, $message->version);
        $this->assertEquals(['first_name' => 'John', 'last_name' => 'Doe'], $message->data);
        $this->assertNotEmpty($message->id);
        $this->assertNotEmpty($message->timestamp);
        $this->assertNotEmpty($message->idempotencyKey);
        $this->assertEquals(config('app.name'), $message->source);
    }

    #[Test]
    public function it_creates_a_domain_message_from_array(): void
    {
        $payload = [
            'type' => 'user.update',
            'version' => 2,
            'id' => 'test-id-123',
            'timestamp' => '2025-01-01T00:00:00+00:00',
            'data' => ['name' => 'Updated Name'],
            'idempotency_key' => 'idempotency-123',
            'source' => 'test-source',
            'metadata' => ['trace_id' => 'abc123'],
        ];

        $message = DomainMessage::fromArray($payload);

        $this->assertEquals('user.update', $message->type);
        $this->assertEquals(2, $message->version);
        $this->assertEquals('test-id-123', $message->id);
        $this->assertEquals('2025-01-01T00:00:00+00:00', $message->timestamp);
        $this->assertEquals(['name' => 'Updated Name'], $message->data);
        $this->assertEquals('idempotency-123', $message->idempotencyKey);
        $this->assertEquals('test-source', $message->source);
        $this->assertEquals(['trace_id' => 'abc123'], $message->metadata);
    }

    #[Test]
    public function it_converts_to_array_and_json(): void
    {
        $message = DomainMessage::create(
            type: 'test.message',
            data: ['key' => 'value'],
        );

        $array = $message->toArray();
        $json = $message->toJson();

        $this->assertIsArray($array);
        $this->assertEquals('test.message', $array['type']);
        $this->assertEquals(['key' => 'value'], $array['data']);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals($array, $decoded);
    }

    #[Test]
    public function it_throws_exception_for_missing_required_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing message type');

        DomainMessage::fromArray(['id' => 'test', 'data' => []]);
    }

    #[Test]
    public function it_throws_exception_for_missing_message_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing message id');

        DomainMessage::fromArray(['type' => 'test.type']);
    }
}
