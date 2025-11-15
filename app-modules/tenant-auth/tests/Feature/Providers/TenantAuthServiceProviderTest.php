<?php

namespace Modules\TenantAuth\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TenantAuthServiceProviderTest extends BaseTestCase
{
    public function test_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(\Modules\TenantAuth\Providers\TenantAuthServiceProvider::class));
    }
}
