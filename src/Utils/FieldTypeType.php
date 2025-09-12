<?php

namespace App\Utils;

enum FieldTypeType: string
{
    case TEXT = "text";
    case INTEGER = "integer";
    case TEXTAREA = "textarea";
    case BOOLEAN = "boolean";
    case DATE = "date";
    case TIME = "time";
    case DATETIME = "datetime";

    function getInputType(): string
    {
        return match ($this) {
            self::TEXT, self::INTEGER => "text",
            self::TEXTAREA => "textarea",
            self::BOOLEAN => "checkbox",
            self::DATE => "date",
            self::TIME => "time",
            self::DATETIME => "datetime-local"
        };
    }
}
