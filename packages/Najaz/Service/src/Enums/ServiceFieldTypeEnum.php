<?php

namespace Najaz\Service\Enums;

enum ServiceFieldTypeEnum: string
{
    /**
     * Text type field.
     */
    case TEXT = 'text';

    /**
     * Textarea type field.
     */
    case TEXTAREA = 'textarea';

    /**
     * Number type field.
     */
    case NUMBER = 'number';

    /**
     * Date type field.
     */
    case DATE = 'date';

    /**
     * Datetime type field.
     */
    case DATETIME = 'datetime';

    /**
     * Email type field.
     */
    case EMAIL = 'email';

    /**
     * Phone type field.
     */
    case PHONE = 'phone';

    /**
     * Get all field type values as an array.
     */
    public static function getValues(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }
}

