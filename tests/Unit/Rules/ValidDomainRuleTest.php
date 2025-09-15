<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidDomainRule;
use PHPUnit\Framework\TestCase;

class ValidDomainRuleTest extends TestCase
{
    public function test_valid_domains(): void
    {
        $this->assertTrue(ValidDomainRule::isValid('example.com'));
        $this->assertTrue(ValidDomainRule::isValid('sub.example.co.uk'));
        $this->assertTrue(ValidDomainRule::isValid('xn--d1acj3b.xn--p1ai')); // punycode like
    }

    public function test_invalid_domains(): void
    {
        $this->assertFalse(ValidDomainRule::isValid('http://example.com'));
        $this->assertFalse(ValidDomainRule::isValid('example!.com'));
        $this->assertFalse(ValidDomainRule::isValid(''));
        $this->assertFalse(ValidDomainRule::isValid(str_repeat('a', 300) . '.com'));
    }
}
