<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use Exception;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Illuminate\Support\Facades\Log;
use Vyuldashev\LaravelOpenApi\Attributes\RequestBody as RequestBodyAttribute;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class RequestBodyBuilder
{
    public function build(RouteInformation $route): ?RequestBody
    {
        try {
            /** @var RequestBodyAttribute|null $requestBody */
            $requestBody = $route->actionAttributes->first(static fn (object $attribute) => $attribute instanceof RequestBodyAttribute);
            if ($requestBody) {
                    $requestBodyFactory = app($requestBody->factory);
                    $requestBody = $requestBodyFactory->build();
                    if ($requestBodyFactory instanceof Reusable) {
                        return RequestBody::ref('#/components/requestBodies/' . $requestBody->objectId);
                    }
            }
            return $requestBody;
        } catch (Exception $e) {
            Log::warning('Failed to build request body: ' . $e->getMessage(), ['exception' => $e, 'requestBody' => $requestBody]);
        }
        return null;
    }
}
