<?php

declare(strict_types=1);

namespace Vyuldashev\LaravelOpenApi;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Vyuldashev\LaravelOpenApi\Builders\Components\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\RequestBodiesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SchemasBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SecuritySchemesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\InfoBuilder;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ServersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\TagsBuilder;

class OpenApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/openapi.php', 'openapi');

        $this->app->bind(CallbacksBuilder::class, fn () => new CallbacksBuilder(self::getPathsFromConfig('callbacks')));
        $this->app->bind(RequestBodiesBuilder::class, fn () => new RequestBodiesBuilder(self::getPathsFromConfig('request_bodies')));
        $this->app->bind(ResponsesBuilder::class, fn () => new ResponsesBuilder(self::getPathsFromConfig('responses')));
        $this->app->bind(SchemasBuilder::class, fn () => new SchemasBuilder(self::getPathsFromConfig('schemas')));
        $this->app->bind(SecuritySchemesBuilder::class, fn () => new SecuritySchemesBuilder(self::getPathsFromConfig('security_schemes')));

        $this->app->singleton(Generator::class, static fn (Application $app) => new Generator(
            config('openapi'),
            $app->make(InfoBuilder::class),
            $app->make(ServersBuilder::class),
            $app->make(TagsBuilder::class),
            $app->make(PathsBuilder::class),
            $app->make(ComponentsBuilder::class)
        ));

        $this->commands([
            Console\GenerateCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CallbackFactoryMakeCommand::class,
                Console\ExtensionFactoryMakeCommand::class,
                Console\ParametersFactoryMakeCommand::class,
                Console\RequestBodyFactoryMakeCommand::class,
                Console\ResponseFactoryMakeCommand::class,
                Console\SchemaFactoryMakeCommand::class,
                Console\SecuritySchemeFactoryMakeCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/openapi.php' => config_path('openapi.php')], 'openapi-config');
        }
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }

    public static function getPathsFromConfig(string $type): array
    {
        return collect(config('openapi.locations.'.$type, []))->map(static fn ($dir) => glob($dir, GLOB_ONLYDIR))->flatten()->unique()->toArray();
    }
}
