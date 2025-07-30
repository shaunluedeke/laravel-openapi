<?php

declare(strict_types=1);

namespace Vyuldashev\LaravelOpenApi\Console;

use Illuminate\Console\Command;
use Vyuldashev\LaravelOpenApi\Generator;

class GenerateCommand extends Command
{
    protected $signature = 'openapi:generate {collection=default} {--output= : Output file}';
    protected $description = 'Generate OpenAPI specification';

    public function handle(Generator $generator): void
    {
        if (!collect(config('openapi.collections'))->has($this->argument('collection'))) {
            $this->error('Collection "'.$this->argument('collection').'" does not exist.');
            return;
        }
        if ($this->option('output')) {
            file_put_contents($this->option('output'), $generator->generate($this->argument('collection'))->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('OpenAPI specification generated successfully.');
            return;
        }
        $this->line($generator->generate($this->argument('collection'))->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
