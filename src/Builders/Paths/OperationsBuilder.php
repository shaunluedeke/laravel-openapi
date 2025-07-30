<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock;
use Vyuldashev\LaravelOpenApi\Attributes\Operation as OperationAttribute;
use Vyuldashev\LaravelOpenApi\Builders\ExtensionsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ParametersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\RequestBodyBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\SecurityBuilder;
use Vyuldashev\LaravelOpenApi\Factories\ServerFactory;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class OperationsBuilder
{
    public function __construct(
        private CallbacksBuilder $callbacksBuilder,
        private ParametersBuilder $parametersBuilder,
        private RequestBodyBuilder $requestBodyBuilder,
        private ResponsesBuilder $responsesBuilder,
        private ExtensionsBuilder $extensionsBuilder,
        private SecurityBuilder $securityBuilder
    ) {
    }

    /**
     * @param RouteInformation[]|Collection $routes
     *
     * @throws InvalidArgumentException
     */
    public function build(array|Collection $routes): array
    {
        $operations = [];

        /** @var RouteInformation[] $routes */
        foreach ($routes as $route) {
            /** @var OperationAttribute|null $operationAttribute */
            $operationAttribute = $route->actionAttributes->first(static fn (object $attribute) => $attribute instanceof OperationAttribute);
            $operation = Operation::create()
                ->action(Str::lower($operationAttribute->method) ?: $route->method)
                ->tags(...($operationAttribute->tags ?? []))
                ->deprecated($this->isDeprecated($route->actionDocBlock))
                ->description($route->actionDocBlock->getDescription()->render() !== '' ? $route->actionDocBlock->getDescription()->render() : null)
                ->summary($route->actionDocBlock->getSummary() !== '' ? $route->actionDocBlock->getSummary() : null)
                ->operationId(optional($operationAttribute)->id)
                ->parameters(...($this->parametersBuilder->build($route)))
                ->requestBody($this->requestBodyBuilder->build($route))
                ->responses(...($this->responsesBuilder->build($route)))
                ->callbacks(...($this->callbacksBuilder->build($route)))
                ->servers(...(collect($operationAttribute->servers)->filter(fn ($server) => app($server) instanceof ServerFactory)->map(static fn ($server) => app($server)->build())->toArray()));

            $security = $this->securityBuilder->build($route);
            $operation = count($security) === 1 && $security[0]->securityScheme === null ? $operation->noSecurity() : $operation->security(...$security);
            $this->extensionsBuilder->build($operation, $route->actionAttributes);
            $operations[] = $operation;
        }

        return $operations;
    }

    protected function isDeprecated(?DocBlock $actionDocBlock): ?bool
    {
        if ($actionDocBlock === null) {
            return null;
        }
        return count($actionDocBlock->getTagsByName('deprecated')) > 0 ? true : null;
    }
}
