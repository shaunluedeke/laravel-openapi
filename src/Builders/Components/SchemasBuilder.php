<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;
use Vyuldashev\LaravelOpenApi\Generator;

class SchemasBuilder extends Builder
{
    public function build(string $collection = Generator::COLLECTION_DEFAULT): array
    {
        return $this->getAllClasses($collection)
            ->filter(static fn ($class) => is_a($class, SchemaFactory::class, true) && is_a($class, Reusable::class, true))
            ->map(static fn ($class) => app($class)->build())
            ->values()
            ->toArray();
    }
}
