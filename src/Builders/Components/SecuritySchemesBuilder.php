<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Vyuldashev\LaravelOpenApi\Factories\SecuritySchemeFactory;
use Vyuldashev\LaravelOpenApi\Generator;

class SecuritySchemesBuilder extends Builder
{
    /** @noinspection PhpParamsInspection */
    public function build(string $collection = Generator::COLLECTION_DEFAULT): array
    {
        return $this->getAllClasses($collection)
            ->filter(static fn ($class) => is_a($class, SecuritySchemeFactory::class, true))
            ->map(static fn ($class) => app($class)->build())
            ->values()
            ->toArray();
    }
}
