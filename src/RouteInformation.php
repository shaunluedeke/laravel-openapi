<?php

declare(strict_types=1);

namespace Vyuldashev\LaravelOpenApi;

use Attribute;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Vyuldashev\LaravelOpenApi\Attributes\Parameters;

class RouteInformation
{
    public ?string $domain;
    public string $method;
    public string $uri;
    public ?string $name;
    public string $controller;

    public Collection $parameters;

    /** @var Collection|Attribute[] */
    public array|Collection $controllerAttributes;

    public string $action;

    /** @var ReflectionParameter[] */
    public array $actionParameters;

    /** @var Collection|Attribute[] */
    public array|Collection $actionAttributes;

    public ?DocBlock $actionDocBlock;

    /**
     */
    public static function createFromRoute(Route $route): ?RouteInformation
    {
        $actionNameParts = explode('@', $route->getActionName());

        if (count($actionNameParts) === 2) {
            [$controller, $action] = $actionNameParts;
        } else {
            [$controller] = $actionNameParts;
            $action = '__invoke';
        }

        preg_match_all('/{(.*?)}/', $route->uri, $parameters);
        $parameters = collect($parameters[1]);

        if ($parameters->isNotEmpty()) {
            $parameters = $parameters->map(static fn ($parameter) => [
                'name' => Str::replaceLast('?', '', $parameter),
                'required' => !Str::endsWith($parameter, '?'),
            ]);
        }

        try {
            $reflectionClass = new ReflectionClass($controller);
            $reflectionMethod = $reflectionClass->getMethod($action);
        } catch (ReflectionException) {
            // If the controller or action does not exist, we cannot create RouteInformation
            return null;
        }

        $actionAttributes = collect($reflectionMethod->getAttributes())->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance());

        $instance = new self();
        $instance->domain = $route->domain();
        $instance->method = collect($route->methods())->map(static fn ($value) => Str::lower($value))->filter(static fn ($value) => ! in_array($value, ['head', 'options'], true))->first();
        $instance->uri = Str::start($route->uri(), '/');
        $instance->name = $route->getName();
        $instance->controller = $controller;
        $instance->parameters = $actionAttributes->contains(fn ($value) => $value instanceof Parameters) ? collect() : $parameters;
        $instance->controllerAttributes = collect($reflectionClass->getAttributes())->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance());
        $instance->action = $action;
        $instance->actionParameters = $reflectionMethod->getParameters();
        $instance->actionAttributes = $actionAttributes;
        $instance->actionDocBlock = rescue(static fn () => ($docComment = $reflectionMethod->getDocComment()) ? DocBlockFactory::createInstance()->create($docComment) : null, null, false);
        return $instance;
    }
}
