<?php

namespace Vyuldashev\LaravelOpenApi\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ExtensionFactoryMakeCommand extends GeneratorCommand
{
    protected $name = 'openapi:make-extension';
    protected $description = 'Create a new extension factory class';
    protected $type = 'Extension';

    protected function buildClass($name): array|string
    {
        return str_replace('DummyExtension', Str::start(Str::snake(Str::replaceLast('DummyExtension', '', class_basename($name)), '-'), 'x-'), parent::buildClass($name));
    }

    protected function getStub(): string
    {
        return __DIR__.'/stubs/extension.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\OpenApi\Extensions';
    }

    protected function qualifyClass($name): string
    {
        $name = parent::qualifyClass($name);
        return Str::endsWith($name, 'Extension') ? $name : ($name . 'Extension');
    }
}
