<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Illuminate\Support\Collection;
use ReflectionClass;
use Vyuldashev\LaravelOpenApi\Attributes\Collection as CollectionAttribute;
use Vyuldashev\LaravelOpenApi\ClassMapGenerator;
use Vyuldashev\LaravelOpenApi\Generator;

abstract class Builder
{
    protected array $directories = [];

    public function __construct(array $directories)
    {
        $this->directories = $directories;
    }

    /** @noinspection MethodVisibilityInspection */
    protected function getAllClasses(string $collection): Collection
    {
        return collect($this->directories)
            ->map(fn (string $directory) => array_keys(ClassMapGenerator::createMap($directory)))
            ->flatten()
            ->filter(function (string $class) use ($collection) {
                $collectionAttributes = (new ReflectionClass($class))->getAttributes(CollectionAttribute::class);
                if ($collection === Generator::COLLECTION_DEFAULT && count($collectionAttributes) === 0) {
                    return true;
                }
                if (count($collectionAttributes) === 0) {
                    return false;
                }
                $collectionAttribute = $collectionAttributes[0]->newInstance();
                return $collectionAttribute->name === ['*'] || in_array($collection, $collectionAttribute->name ?? [], true);
            });
    }
}
