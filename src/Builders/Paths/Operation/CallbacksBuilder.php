<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use Exception;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use Illuminate\Support\Facades\Log;
use Vyuldashev\LaravelOpenApi\Attributes\Callback as CallbackAttribute;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class CallbacksBuilder
{
    public function build(RouteInformation $route): array
    {
        return $route->actionAttributes
            ->filter(static fn (object $attribute) => $attribute instanceof CallbackAttribute)
            ->map(static function (CallbackAttribute $attribute) {
                try {
                    $factory = app($attribute->factory);
                    $pathItem = $factory->build();
                    return $factory instanceof Reusable ? PathItem::ref('#/components/callbacks/' . $pathItem?->objectId) : $pathItem;
                } catch (Exception $e) {
                    Log::warning('Failed to build callback: ' . $e->getMessage(), ['exception' => $e, 'factory' => $attribute->factory]);
                    return null;
                }
            })
            ->filter(static fn ($item) => $item !== null)
            ->values()
            ->toArray();
    }
}
