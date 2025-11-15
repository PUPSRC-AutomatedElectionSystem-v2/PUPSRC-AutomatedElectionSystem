<?php

namespace Modules\OrganizationVoting\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class OrganizationVotingServiceProviderTest extends BaseTestCase
{
    public function test_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(\Modules\OrganizationVoting\Providers\OrganizationVotingServiceProvider::class));
    }
}
