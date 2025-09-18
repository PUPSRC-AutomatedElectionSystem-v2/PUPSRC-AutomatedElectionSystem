<?php

namespace App\Services;

use App\Rules\ValidDomainRule;

class DomainResolver
{
    /**
     * Resolve domain based on use_default_domain flag
     */
    public static function resolve(string $domain, bool $useDefaultDomain): string
    {
        $centralDomains = config('tenancy.central_domains', []);
        if (is_array($centralDomains) && isset($centralDomains[0]) && is_string($centralDomains[0]) && $centralDomains[0] !== '') {
            $centralDomain = $centralDomains[0];
        } else {
            $centralDomain = config('session.domain', '');
        }

        if ($useDefaultDomain) {
            return $domain.'.'.$centralDomain;
        } else {
            return $domain;
        }
    }

    /**
     * Resolve and validate domain
     */
    public static function resolveAndValidate(string $domain, bool $useDefaultDomain): string
    {
        $resolvedDomain = self::resolve($domain, $useDefaultDomain);

        if (! ValidDomainRule::isValid($resolvedDomain)) {
            throw new \InvalidArgumentException('Invalid domain format provided for: '.$resolvedDomain);
        }

        return $resolvedDomain;
    }

    /**
     * Get the resolved domain without validation
     */
    public static function getResolvedDomain(string $domain, bool $useDefaultDomain): string
    {
        return self::resolve($domain, $useDefaultDomain);
    }

    /**
     * Validate a resolved domain
     */
    public static function validateResolvedDomain(string $resolvedDomain): bool
    {
        return ValidDomainRule::isValid($resolvedDomain);
    }

    /**
     * Validate domain based on use_default_domain flag
     */
    public static function validateDomain(string $domain, bool $useDefaultDomain): bool
    {
        if ($useDefaultDomain) {
            // When using default domain, validate the full resolved domain
            $resolvedDomain = self::getResolvedDomain($domain, $useDefaultDomain);

            return self::validateResolvedDomain($resolvedDomain);
        } else {
            // When not using default domain, validate the domain as-is
            return self::validateResolvedDomain($domain);
        }
    }

    /**
     * Validate domain and return error message if invalid
     */
    public static function validateDomainWithMessage(string $domain, bool $useDefaultDomain): ?string
    {
        if ($useDefaultDomain) {
            // When using default domain, validate the full resolved domain
            $resolvedDomain = self::getResolvedDomain($domain, $useDefaultDomain);
            if (! self::validateResolvedDomain($resolvedDomain)) {
                return 'Invalid domain format provided for: '.$resolvedDomain;
            }
        } else {
            // When not using default domain, validate the domain as-is
            if (! self::validateResolvedDomain($domain)) {
                return 'Invalid domain format provided for: '.$domain;
            }
        }

        return null;
    }

    /**
     * Resolves the full domain from a stored domain string.
     * If the stored domain doesn't contain a dot, it's treated as a subdomain
     * and the central domain is appended to create the full domain.
     * If it contains a dot, it's returned as-is (custom full domain).
     */
    public static function fullDomain(string $inputDomain): string
    {
        // If domain doesn't contain a dot, it's a default subdomain, so append central domain
        if (strpos($inputDomain, '.') === false) {
            self::getResolvedDomain($inputDomain, true);
        }

        return $inputDomain;
    }
}
