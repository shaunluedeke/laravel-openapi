<?php

namespace Vyuldashev\LaravelOpenApi;

use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Support\Arr;
use Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\InfoBuilder;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ServersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\TagsBuilder;

class Generator
{
    public const COLLECTION_DEFAULT = 'default';
    public string $version = OpenApi::OPENAPI_3_0_2;

    public function __construct(
        private array $config,
        private InfoBuilder $infoBuilder,
        private ServersBuilder $serversBuilder,
        private TagsBuilder $tagsBuilder,
        private PathsBuilder $pathsBuilder,
        private ComponentsBuilder $componentsBuilder
    ) {
    }

    public function generate(string $collection = self::COLLECTION_DEFAULT): OpenApi
    {
        $middlewares = Arr::get($this->config, 'collections.'.$collection.'.middlewares');
        $openApi = OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->info($this->infoBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.info', [])))
            ->servers(...($this->serversBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.servers', []))))
            ->paths(...($this->pathsBuilder->build($collection, Arr::get($middlewares, 'paths', []))))
            ->components($this->componentsBuilder->build($collection, Arr::get($middlewares, 'components', [])))
            ->security(...Arr::get($this->config, 'collections.'.$collection.'.security', []))
            ->tags(...($this->tagsBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.tags', []))));
        foreach (Arr::get($this->config, 'collections.'.$collection.'.extensions', []) as $key => $value) {
            $openApi = $openApi->x($key, $value);
        }
        return $openApi;
    }
}
