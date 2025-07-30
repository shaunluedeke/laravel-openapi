<?php

namespace Vyuldashev\LaravelOpenApi;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use ReflectionType;

class SchemaHelpers
{
    public static function guessFromReflectionType(ReflectionType $reflectionType): Schema
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return match ($reflectionType->getName()) {
            'int' => Schema::integer(),
            'bool' => Schema::boolean(),
            default => Schema::string(),
        };
    }
}
