<?php

namespace Vyuldashev\LaravelOpenApi\Concerns;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use InvalidArgumentException;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\CallbackFactory;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;
use Vyuldashev\LaravelOpenApi\Factories\SecuritySchemeFactory;

trait Referencable
{
    public static function ref(?string $objectId = null): Schema
    {
        $instance = app(static::class);
        if (!$instance instanceof Reusable) {
            throw new InvalidArgumentException('"'.static::class.'" must implement "'.Reusable::class.'" in order to be referencable.');
        }
        $baseRef = match (true) {
            $instance instanceof CallbackFactory => '#/components/callbacks/',
            $instance instanceof ParametersFactory => '#/components/parameters/',
            $instance instanceof RequestBodyFactory => '#/components/requestBodies/',
            $instance instanceof ResponseFactory => '#/components/responses/',
            $instance instanceof SchemaFactory => '#/components/schemas/',
            $instance instanceof SecuritySchemeFactory => '#/components/securitySchemes/',
            default => null,
        };
        return Schema::ref($baseRef . $instance->build()->objectId, $objectId);
    }
}
