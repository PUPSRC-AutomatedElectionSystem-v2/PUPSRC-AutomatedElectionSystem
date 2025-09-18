<?php

namespace App\Rules\Email;

/**
 * Centralized email regex patterns for reuse across PHP validation and SQL CHECKs.
 */
final class EmailPattern
{
    /**
     * Raw pattern (no delimiters) suitable for SQL REGEXP.
     */
    public const RAW = '^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$';

    /**
     * PHP preg pattern with delimiters.
     */
    public const PREG = '/'.self::RAW.'/';
}
