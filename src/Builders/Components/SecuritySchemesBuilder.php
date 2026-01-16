<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Vyuldashev\LaravelOpenApi\Factories\SecuritySchemeFactory;
use Vyuldashev\LaravelOpenApi\Generator;

class SecuritySchemesBuilder extends Builder
{
    public function build(string $collection = Generator::COLLECTION_DEFAULT): array
    {
        return $this->getAllClasses($collection)
            ->filter(static fn ($class) => is_a($class, SecuritySchemeFactory::class, true))
            ->map(static fn ($class) => rescue(static fn() => app($class)->build()))
            ->filter(static fn ($item) => $item !== null)
            ->values()
            ->toArray();
    }
}
