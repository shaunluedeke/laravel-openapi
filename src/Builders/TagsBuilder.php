<?php

namespace Vyuldashev\LaravelOpenApi\Builders;

use GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use Illuminate\Support\Arr;

class TagsBuilder
{
    /**
     * @return Tag[]
     */
    public function build(array $config): array
    {
        return collect($config)->map(static fn (array $tag) => Tag::create()
            ->name($tag['name'])
            ->description(Arr::get($tag, 'description'))
            ->externalDocs(
                Arr::has($tag, 'externalDocs')
                    ? ExternalDocs::create($tag['name'])->description(Arr::get($tag, 'externalDocs.description'))->url(Arr::get($tag, 'externalDocs.url'))
                    : null
            ))->toArray();
    }
}
