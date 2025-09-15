<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use App\Models\CentralModels\OrganizationContacts;
use App\Models\CentralModels\Organizations;
use App\Models\CentralModels\Tenant;
use App\Rules\ValidDomainRule;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenant
{
    /**
     * @param  array{tenant: array{id?: string, domain: string}, organization: array{short_name: string, name: string, category_name: string, should_copy_from_other_org?: bool, allow_cross_membership?: bool, theme?: array|null, order?: int|null}, contacts: array{email: string, website?: ?string, facebook?: ?string, twitter?: ?string, instagram?: ?string, threads?: ?string, discord?: ?string}}  $payload
     * @return array{tenant: Tenant, domain: Domain, organization: Organizations, contacts: OrganizationContacts}
     */
    public function __construct(
        protected CreateOrganizationCategory $createCategory = new CreateOrganizationCategory,
        protected CreateOrganization $createOrganization = new CreateOrganization,
        protected CreateOrganizationContact $createContact = new CreateOrganizationContact,
    ) {}

    public function handle(array $payload): array
    {
        $tenantInput = $payload['tenant'];
        $orgInput = $payload['organization'];
        $contactsInput = $payload['contacts'];

        $tenantId = $tenantInput['id'] ?? null;
        $domainInput = $tenantInput['domain'];

        $domain = self::resolveDomain($domainInput, $tenantInput['use_default_domain']);

        // Create organization metadata in CENTRAL database via dedicated services
        [$tenant, $domainModel, $organization, $contacts] = DB::transaction(function () use ($tenantId, $domain, $orgInput, $contactsInput) {
            // Create central Tenant and Domain
            $tenant = Tenant::create([
                'id' => $tenantId ?? (string) \Illuminate\Support\Str::uuid(),
            ]);

            $domainModel = $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $category = $this->createCategory->handle($orgInput['category_name']);

            $organization = $this->createOrganization->handle([
                'tenant_id' => $tenant->id,
                'category_id' => $category->id,
                ...$orgInput,
            ]);

            $contacts = $this->createContact->handle([
                'organization_id' => $organization->id,
                ...$contactsInput,
            ]);

            $organization->update(['contact_id' => $contacts->id]);

            return [$tenant, $domainModel, $organization, $contacts];
        });

        return [
            'tenant' => $tenant,
            'domain' => $domainModel,
            'organization' => $organization,
            'contacts' => $contacts,
        ];
    }

    private static function resolveDomain(string $domain, bool  $useDefaultDomain)
    {
        $centralDomains = config('tenancy.central_domains', []);
        if (is_array($centralDomains) && isset($centralDomains[0]) && is_string($centralDomains[0]) && $centralDomains[0] !== '') {
            $centralDomain = $centralDomains[0];
        } else {
            $centralDomain = config('session.domain', '');
        }

        $candidateDomain = $useDefaultDomain ? ($domain . '.' . $centralDomain) : $domain;

        if (ValidDomainRule::isValid($candidateDomain)) {
            return $candidateDomain;
        } elseif (ValidDomainRule::isValid($domain)) {
            return $domain;
        } elseif (ValidDomainRule::isValid($centralDomain)) {
            return $centralDomain;
        } else {
            throw new \InvalidArgumentException('Invalid domain format provided.');
        }
    }
}
