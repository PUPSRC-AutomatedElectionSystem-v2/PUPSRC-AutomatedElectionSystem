<?php

namespace Tests\Feature\Messaging;

use App\Handlers\CreateUserDataHandler;
use App\Models\Central\UserData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateUserDataHandlerTest extends TestCase
{
    use RefreshDatabase;

    private CreateUserDataHandler $handler;

    private MockInterface $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = Mockery::mock(MessagePublisherInterface::class);
        $this->publisher->shouldReceive('publish')->byDefault();

        $this->handler = new CreateUserDataHandler($this->publisher);
    }

    #[Test]
    public function it_returns_correct_queue(): void
    {
        $this->assertEquals('organization.user', $this->handler->queue());
    }

    #[Test]
    public function it_handles_user_create_message_type(): void
    {
        $this->assertEquals('user.create', $this->handler->handles());
    }

    #[Test]
    public function it_supports_version_one(): void
    {
        $this->assertContains(1, $this->handler->supportedVersions());
    }

    #[Test]
    public function it_creates_user_data_from_message(): void
    {
        $this->publisher->shouldReceive('publish')
            ->once()
            ->withArgs(function (DomainMessage $msg, string $exchange, string $routingKey) {
                return $msg->type === 'userdata.created'
                    && $exchange === 'central'
                    && $routingKey === 'userdata.created'
                    && $msg->data['identity_id'] === 'STU-2025-001';
            });

        $message = DomainMessage::create(
            type: 'user.create',
            data: [
                'identity_id' => 'STU-2025-001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'middle_name' => 'Smith',
                'email' => 'john.doe@example.com',
                'course' => 'BSCS',
                'year_level' => '3',
            ],
            version: 1,
        );

        $this->handler->handle($message);

        $this->assertDatabaseHas('users_data', [
            'identity_id' => 'STU-2025-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'Smith',
        ]);

        $userData = UserData::where('identity_id', 'STU-2025-001')->first();
        $this->assertNotNull($userData);
        $this->assertEquals('john.doe@example.com', $userData->data['email']);
        $this->assertEquals('BSCS', $userData->data['course']);
        $this->assertEquals('3', $userData->data['year_level']);
    }

    #[Test]
    public function it_skips_duplicate_identity_id(): void
    {
        $this->publisher->shouldNotReceive('publish');

        // Create existing user
        UserData::create([
            'identity_id' => 'STU-2025-002',
            'first_name' => 'Existing',
            'last_name' => 'User',
        ]);

        $message = DomainMessage::create(
            type: 'user.create',
            data: [
                'identity_id' => 'STU-2025-002',
                'first_name' => 'New',
                'last_name' => 'User',
            ],
            version: 1,
        );

        $this->handler->handle($message);

        // Should still have only one record with original data
        $this->assertDatabaseCount('users_data', 1);
        $this->assertDatabaseHas('users_data', [
            'identity_id' => 'STU-2025-002',
            'first_name' => 'Existing',
        ]);
    }

    #[Test]
    public function it_logs_and_skips_empty_data(): void
    {
        $this->publisher->shouldNotReceive('publish');

        $message = DomainMessage::create(
            type: 'user.create',
            data: [],
            version: 1,
        );

        $this->handler->handle($message);

        $this->assertDatabaseCount('users_data', 0);
    }

    #[Test]
    public function it_logs_validation_errors(): void
    {
        $this->publisher->shouldNotReceive('publish');

        $message = DomainMessage::create(
            type: 'user.create',
            data: [
                'identity_id' => 'STU-2025-003',
                // Missing required first_name and last_name
            ],
            version: 1,
        );

        $this->handler->handle($message);

        $this->assertDatabaseCount('users_data', 0);
    }

    #[Test]
    public function it_maps_student_id_to_identity_id(): void
    {
        $this->publisher->shouldReceive('publish')->once();

        $message = DomainMessage::create(
            type: 'user.create',
            data: [
                'student_id' => 'STU-2025-004', // Using student_id instead of identity_id
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
            version: 1,
        );

        $this->handler->handle($message);

        $this->assertDatabaseHas('users_data', [
            'identity_id' => 'STU-2025-004',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);
    }

    #[Test]
    public function it_publishes_userdata_created_with_tenant_id(): void
    {
        $this->publisher->shouldReceive('publish')
            ->once()
            ->withArgs(function (DomainMessage $msg) {
                return $msg->type === 'userdata.created'
                    && $msg->data['identity_id'] === 'STU-2025-005'
                    && $msg->data['tenant_id'] === 'org-123';
            });

        $message = DomainMessage::create(
            type: 'user.create',
            data: [
                'identity_id' => 'STU-2025-005',
                'first_name' => 'Test',
                'last_name' => 'User',
                'tenant_id' => 'org-123',
            ],
            version: 1,
        );

        $this->handler->handle($message);

        $this->assertDatabaseHas('users_data', [
            'identity_id' => 'STU-2025-005',
        ]);
    }
}
