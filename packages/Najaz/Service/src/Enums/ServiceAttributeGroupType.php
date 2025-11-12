<?php

namespace Najaz\Service\Enums;

enum ServiceAttributeGroupType: string
{
    case GENERAL = 'general';
    case CITIZEN = 'citizen';

    /**
     * Return all enum values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Determine whether given value is supported.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}


