<?php

namespace Vyuldashev\LaravelOpenApi\Builders;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Vyuldashev\LaravelOpenApi\Attributes;
use Vyuldashev\LaravelOpenApi\Attributes\Collection as CollectionAttribute;
use Vyuldashev\LaravelOpenApi\Builders\Paths\OperationsBuilder;
use Vyuldashev\LaravelOpenApi\Contracts\PathMiddleware;
use Vyuldashev\LaravelOpenApi\Generator;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class PathsBuilder
{
    public function __construct(private OperationsBuilder $operationsBuilder)
    {
    }

    /**
     * @param PathMiddleware[] $middlewares
     * @throws InvalidArgumentException
     */
    public function build(string $collection, array $middlewares): array
    {
        return $this->routes()
            ->filter(static function (RouteInformation $routeInformation) use ($collection) {
                /** @var CollectionAttribute|null $collectionAttribute */
                $collectionAttribute = collect()
                    ->merge($routeInformation->controllerAttributes)
                    ->merge($routeInformation->actionAttributes)
                    ->first(static fn (object $item) => $item instanceof CollectionAttribute);
                return (! $collectionAttribute && $collection === Generator::COLLECTION_DEFAULT) || ($collectionAttribute && in_array($collection, $collectionAttribute->name, true));
            })
            ->map(static function (RouteInformation $item) use ($middlewares) {
                foreach ($middlewares as $middleware) {
                    app($middleware)->before($item);
                }
                return $item;
            })
            ->groupBy(static fn (RouteInformation $routeInformation) => $routeInformation->uri)
            ->map(fn (Collection $routes, $uri) => PathItem::create()->route($uri)->operations(...($this->operationsBuilder->build($routes))))
            ->map(static function (PathItem $item) use ($middlewares) {
                foreach ($middlewares as $middleware) {
                    $item = app($middleware)->after($item);
                }
                return $item;
            })
            ->values()
            ->toArray();
    }

    protected function routes(): Collection
    {
        return collect(app(Router::class)->getRoutes())
            ->filter(static fn (Route $route) => $route->getActionName() !== 'Closure')
            ->map(static fn (Route $route) => RouteInformation::createFromRoute($route))
            ->filter(
                static fn (?RouteInformation $route) => !is_null($route)
                && $route->controllerAttributes->first(static fn (object $attribute) => $attribute instanceof Attributes\PathItem)
                && $route->actionAttributes->first(static fn (object $attribute) => $attribute instanceof Attributes\Operation)
            );
    }
}
