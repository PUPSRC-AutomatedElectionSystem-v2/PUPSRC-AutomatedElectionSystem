Developer notes
================

## Logging
See [logging documentation (used by laravel)](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels).

Post-generation dedupe
----------------------

This project uses Laravel Wayfinder to generate TypeScript definitions for routes and controller actions into `resources/js`. In some development environments Wayfinder may generate the same named route multiple times with different host variants (for example `//localhost`, `//127.0.0.1`, or `//my-site.test`). Multiple `export const <name>` declarations in the same TypeScript file cause ESBuild/TypeScript to fail the build with "Multiple exports with the same name" errors.

To avoid editing vendor files, we use a small post-generation dedupe script that runs automatically during the Vite build. The script removes duplicate `export const <name>` declarations (keeps the first occurrence) as well as duplicate helper declarations (e.g. `const loginForm`).

Files:

 - `resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs` — CommonJS script that deduplicates generated `.ts` files under `resources/js/routes`.
- `vite.config.ts` — runs the dedupe script as a `post` Vite plugin named `wayfinder-dedupe`.

Manual workflow:

1. Regenerate types (Wayfinder):

```powershell
php artisan wayfinder:generate
```

2. Run dedupe (optional — build will also run it automatically):

```powershell
node resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs
```

3. Build frontend assets:

```powershell
npm run build
```

Cross-platform cleanup (node_modules + lockfile)
-----------------------------------------------

On POSIX systems people often run `rm -rf node_modules package-lock.json`. On Windows PowerShell that exact command fails. Use the provided cross-platform helper instead.

1. Run the helper (node must be installed):

```powershell
node scripts/clean-node-modules.cjs
```

2. Reinstall and build:

```powershell
npm install
npm run build
```

If you prefer direct PowerShell commands:

```powershell
Remove-Item -Recurse -Force node_modules
Remove-Item -Force package-lock.json
```

Notes for contributors
----------------------
- This post-generation script is a local workaround. If you maintain Wayfinder please consider upstreaming a dedupe option or configuration to control host variant generation.

Service-Oriented Messaging (RabbitMQ)
-------------------------------------

This project uses RabbitMQ for async, decoupled messaging between modules. This architecture prepares for future app separation (central app + organization apps).

### Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│              Organization-Admin Module (Publisher)                          │
│  ┌──────────────────┐     ┌─────────────────────────┐                       │
│  │  VotersImport    │────▶│ MessagePublisherInterface│                       │
│  │  (Excel Import)  │     │ (RabbitMqMessagePublisher)                       │
│  └──────────────────┘     └───────────┬─────────────┘                       │
└───────────────────────────────────────┼─────────────────────────────────────┘
                                        │ publish(DomainMessage)
                                        ▼
                            ┌───────────────────────┐
                            │      RabbitMQ         │
                            │  Exchange: organization│
                            │  Routing: user.*      │
                            │  Queue: organization.user
                            └───────────┬───────────┘
                                        │
┌───────────────────────────────────────┼─────────────────────────────────────┐
│                      Central App (Consumer)                                  │
│                                       ▼                                      │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │  php artisan messaging:consume organization.user                       │ │
│  │  ┌───────────────────┐     ┌─────────────────────────┐                 │ │
│  │  │ MessageProcessor  │────▶│ MessageHandlerRegistry   │                 │ │
│  │  └───────────────────┘     └────────────┬────────────┘                 │ │
│  └─────────────────────────────────────────┼──────────────────────────────┘ │
│                                            ▼                                 │
│               ┌────────────────────────────────────────────┐                │
│               │  App\Handlers\CreateUserDataHandler        │                │
│               │  → Creates UserData in central database    │                │
│               └────────────────────────────────────────────┘                │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Key Components

| File | Purpose |
|------|---------|
| `app-modules/shared/src/Contracts/Messaging/DomainMessage.php` | Typed message DTO |
| `app-modules/shared/src/Contracts/Messaging/MessagePublisherInterface.php` | Publisher contract |
| `app-modules/shared/src/Contracts/Messaging/MessageConsumerInterface.php` | Consumer contract |
| `app-modules/shared/src/Contracts/Messaging/MessageHandlerInterface.php` | Handler contract |
| `app-modules/shared/src/Services/RabbitMqMessagePublisher.php` | RabbitMQ publisher |
| `app-modules/shared/src/Services/RabbitMqMessageConsumer.php` | RabbitMQ consumer |
| `app-modules/shared/src/Services/MessageHandlerRegistry.php` | Handler routing |
| `app/Handlers/CreateUserDataHandler.php` | Central app handler for user.create |

### Quick Start

1. **Ensure RabbitMQ is running:**

```powershell
docker compose up -d rabbitmq
```

2. **Set up exchanges, queues, and bindings (once):**

```powershell
php artisan messaging:setup --exchange=organization --queue=organization.user --routing-key="user.*"
```

3. **Start the consumer (separate terminal or supervisor):**

```powershell
# Auto-discover all queues from registered handlers (recommended)
php artisan messaging:consume

# Or consume a specific queue
php artisan messaging:consume organization.user
```

4. **List registered handlers:**

```powershell
php artisan messaging:handlers
```

Output shows queue, message type, handler class, and versions:

```
+-------------------+--------------+------------------------------------+----------+
| Queue             | Message Type | Handler Class                      | Versions |
+-------------------+--------------+------------------------------------+----------+
| organization.user | user.create  | App\Handlers\CreateUserDataHandler | 1        |
+-------------------+--------------+------------------------------------+----------+
```

### Publishing Messages

Messages are published automatically when importing voters via Excel. To publish manually:

```php
use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;

$publisher = app(MessagePublisherInterface::class);

$message = DomainMessage::create(
    type: 'user.create',
    data: [
        'identity_id' => 'STU-2025-001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ],
    version: 1,
);

$publisher->publish($message, 'organization', 'user.create');
```

### Adding New Message Handlers

1. **Create a handler class:**

```php
namespace App\Handlers;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;

class MyNewHandler implements MessageHandlerInterface
{
    public function queue(): string
    {
        return 'my.queue.name'; // Queue to consume from
    }

    public function handles(): string
    {
        return 'my.message.type'; // Message type this handler processes
    }

    public function supportedVersions(): array
    {
        return [1];
    }

    public function handle(DomainMessage $message): void
    {
        // Process the message
    }
}
```

2. **Register in AppServiceProvider:**

```php
$registry->register(new MyNewHandler());
```

The handler will be auto-discovered when running `php artisan messaging:consume` without arguments.

### Running Tests

```powershell
php artisan test --filter=Messaging
```

### Environment Variables

Add to `.env`:

```env
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

### Future: Splitting Apps

When breaking into separate applications:

1. Extract `app-modules/shared` into a composer package
2. Central app keeps consumers + handlers
3. Organization app keeps publishers
4. Both connect to the same RabbitMQ instance
5. Messages are JSON — no PHP class serialization across apps
