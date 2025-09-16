This directory contains RabbitMQ pre-declared definitions loaded at container startup.

- `definitions.json`: Pre-declares vhosts, queues, exchanges and bindings. (Users and permissions are now configured via environment variables / compose.)

How it works
1. `docker-compose.yml` mounts `./docker/rabbitmq/definitions.json` into `/etc/rabbitmq/definitions.json` inside the container.
2. The `RABBITMQ_LOAD_DEFINITIONS` environment variable is set to `1`, which tells the official RabbitMQ image to import the file on first start.

Customizing
- To add queues, exchanges or bindings, edit `definitions.json`. You can export a live broker's definitions using the management UI (Management -> Admin -> Export definitions) and save that JSON here.
- If you change `definitions.json`, restart the `rabbitmq` service. If the broker already initialized, you may need to remove the rabbitmq volume to force re-import:

  docker compose down -v rabbitmq
  docker compose up -d rabbitmq

Security notes
- Users and permissions are intentionally NOT declared in `definitions.json` in this repository. The container's `RABBITMQ_DEFAULT_USER`/`RABBITMQ_DEFAULT_PASS` or your `.env` should provide the credentials used by your application.
- In production, avoid using `guest` and create a dedicated, least-privilege user for your app. If you prefer to manage users via `definitions.json`, export from an existing broker and store the exported file securely (beware of password hashes in VCS).
