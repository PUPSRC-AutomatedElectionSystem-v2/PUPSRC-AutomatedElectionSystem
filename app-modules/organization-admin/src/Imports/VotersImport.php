<?php

namespace Modules\OrganizationAdmin\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\OrganizationAdmin\Imports\Formats\StudentVoterImport;
use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;

/**
 * Imports voter data from Excel/CSV and publishes messages for processing.
 *
 * Uses the message broker for decoupled, async processing.
 * When apps are split, this will send messages to the organization app.
 */
class VotersImport implements ToCollection, WithHeadingRow
{
    private const EXCHANGE = 'organization';

    private const ROUTING_KEY = 'user.create';

    public function __construct(
        private readonly MessagePublisherInterface $publisher,
    ) {}

    public function collection(Collection $rows): void
    {
        $tenantId = $this->getCurrentTenantId();

        foreach ($rows as $row) {
            $data = StudentVoterImport::parseRow($row);

            // Ensure $data is an array
            if ($data instanceof Collection) {
                $data = $data->toArray();
            }

            // Add tenant context for the return message
            $data['tenant_id'] = $tenantId;

            // Create and publish domain message
            $message = DomainMessage::create(
                type: 'user.create',
                data: $data,
                version: 1,
            );

            $this->publisher->publish($message, self::EXCHANGE, self::ROUTING_KEY);
        }
    }

    /**
     * Get the current tenant ID from tenancy context.
     */
    private function getCurrentTenantId(): ?string
    {
        if (! tenancy()->initialized) {
            return null;
        }

        return tenancy()->tenant?->getTenantKey();
    }
}
