<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use Vyuldashev\LaravelOpenApi\Attributes\Operation as OperationAttribute;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class SecurityBuilder
{
    /** @noinspection PhpParamsInspection */
    public function build(RouteInformation $route): array
    {
        return $route->actionAttributes
            ->filter(static fn (object $attribute) => $attribute instanceof OperationAttribute)
            ->filter(static fn (OperationAttribute $attribute) => isset($attribute->security))
            ->map(static fn (OperationAttribute $attribute) => SecurityRequirement::create()->securityScheme($attribute->security === '' ? null : app($attribute->security)->build())) // return a null scheme if the security is set to ''
            ->values()
            ->toArray();
    }
}
