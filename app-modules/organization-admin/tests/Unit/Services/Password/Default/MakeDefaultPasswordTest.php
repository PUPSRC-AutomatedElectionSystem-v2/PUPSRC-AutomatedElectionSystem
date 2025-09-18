<?php

namespace Modules\OrganizationAdmin\Tests\Unit\Services\Password\Default;

use Modules\OrganizationAdmin\Services\Password\Default\DefaultAdminPassword;
use Modules\OrganizationAdmin\Services\Password\Default\DefaultUserPassword;
use Modules\OrganizationAdmin\Services\Password\Default\MakeDefaultPassword;
use PHPUnit\Framework\TestCase;

class MakeDefaultPasswordTest extends TestCase
{
    public function test_get_default_password_for_voter(): void
    {
        $defaultUserPassword = $this->createMock(DefaultUserPassword::class);
        $defaultUserPassword->expects($this->once())
            ->method('get')
            ->willReturn('user_password');

        $defaultAdminPassword = $this->createMock(DefaultAdminPassword::class);

        $service = new MakeDefaultPassword($defaultUserPassword, $defaultAdminPassword);

        $this->assertEquals('user_password', $service->getDefaultPassword('voter'));
    }

    public function test_get_default_password_for_committee(): void
    {
        $defaultUserPassword = $this->createMock(DefaultUserPassword::class);

        $defaultAdminPassword = $this->createMock(DefaultAdminPassword::class);
        $defaultAdminPassword->expects($this->once())
            ->method('get')
            ->willReturn('admin_password');

        $service = new MakeDefaultPassword($defaultUserPassword, $defaultAdminPassword);

        $this->assertEquals('admin_password', $service->getDefaultPassword('committee'));
    }

    public function test_get_default_password_throws_exception_for_invalid_account_type(): void
    {
        $defaultUserPassword = $this->createMock(DefaultUserPassword::class);
        $defaultAdminPassword = $this->createMock(DefaultAdminPassword::class);

        $service = new MakeDefaultPassword($defaultUserPassword, $defaultAdminPassword);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid account type: invalid');

        $service->getDefaultPassword('invalid');
    }
}
