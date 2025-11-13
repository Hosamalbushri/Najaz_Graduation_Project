<?php

namespace Najaz\Service\Enums;

enum ServiceAttributeTypeEnum: string
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
    /**
     * Boolean type field.
     */
    case BOOLEAN = 'boolean';

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
     * Checkbox type field.
     */
    case CHECKBOX = 'checkbox';

    /**
     * Select type field.
     */
    case SELECT = 'select';

    /**
     * Multiselect type field.
     */
    case MULTISELECT = 'multiselect';

    /**
     * Image type field.
     */
    case IMAGE = 'image';

    /**
     * File type field.
     */
    case FILE = 'file';

    /**
     * Get all attribute type values as an array.
     */
    public static function getValues(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }
}


