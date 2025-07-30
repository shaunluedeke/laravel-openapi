<?php

namespace Vyuldashev\LaravelOpenApi\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class SecuritySchemeFactoryMakeCommand extends GeneratorCommand
{
    protected $name = 'openapi:make-security-scheme';
    protected $description = 'Create a new SecurityScheme factory class';
    protected $type = 'SecurityScheme';

    protected function buildClass($name): array|string
    {
        return str_replace('DummySecurityScheme', Str::replaceLast('SecurityScheme', '', class_basename($name)), parent::buildClass($name));
    }

    protected function getStub(): string
    {
        return __DIR__.'/stubs/securityscheme.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\OpenApi\SecuritySchemes';
    }

    protected function qualifyClass($name): string
    {
        $name = parent::qualifyClass($name);
        return Str::endsWith($name, 'SecurityScheme') ? $name : ($name . 'SecurityScheme');
    }
}
