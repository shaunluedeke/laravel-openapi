<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Vyuldashev\LaravelOpenApi\Attributes\Response as ResponseAttribute;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class ResponsesBuilder
{
    public function build(RouteInformation $route): array
    {
        return $route->actionAttributes
            ->filter(static fn (object $attribute) => $attribute instanceof ResponseAttribute)
            ->map(static function (ResponseAttribute $attribute) {
                if (isset($attribute->factories) && is_array($attribute->factories) && $attribute->factory === null) {
                    return collect($attribute->factories)->map(static function (string $factory) {
                        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                        try {
                            $factory = app($factory);
                            $response = $factory->build();
                            if ($factory instanceof Reusable) {
                                return Response::ref('#/components/responses/' . $response?->objectId);
                            }
                            return $response;
                        } catch (\Exception $e) {
                            Log::warning('Failed to build response: ' . $e->getMessage(), ['exception' => $e, 'factory' => $factory]);
                            return null;
                        }
                    })->values()->toArray();
                }
                if ($attribute->factory === null) {
                    throw new InvalidArgumentException('Factory class must be instance of ResponseFactory');
                }
                try {
                    $factory = app($attribute->factory);
                    $response = $factory->build();
                } catch (\Exception $e) {
                    Log::warning('Failed to instantiate response factory: ' . $e->getMessage(), ['exception' => $e, 'factory' => $attribute->factory]);
                    return null;
                }

                if ($factory instanceof Reusable) {
                    return Response::ref('#/components/responses/'.$response->objectId)
                        ->statusCode($attribute->statusCode)
                        ->description($attribute->description);
                }

                return $response;
            })
            ->filter(static fn ($item) => $item !== null)
            ->values()
            ->flatten()
            ->toArray();
    }
}
