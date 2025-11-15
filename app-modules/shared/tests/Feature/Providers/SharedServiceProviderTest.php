<?php

namespace Modules\Shared\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class SharedServiceProviderTest extends BaseTestCase
{
	public function test_provider_class_exists(): void
	{
		$this->assertTrue(class_exists(\Modules\Shared\Providers\SharedServiceProvider::class));
	}
}
