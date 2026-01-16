<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\CallbackFactory;
use Vyuldashev\LaravelOpenApi\Generator;

class CallbacksBuilder extends Builder
{
    public function build(string $collection = Generator::COLLECTION_DEFAULT): array
    {
        return $this->getAllClasses($collection)
            ->filter(static fn ($class) => is_a($class, CallbackFactory::class, true) && is_a($class, Reusable::class, true))
            ->map(static fn ($class) => rescue(static fn() => app($class)->build()))
            ->filter(static fn ($item) => $item !== null)
            ->values()
            ->toArray();
    }
}
