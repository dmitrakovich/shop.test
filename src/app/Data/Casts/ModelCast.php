<?php

namespace App\Data\Casts;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

readonly class ModelCast implements Cast
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __construct(
        private string $modelClass,
        private readonly string $keyName = 'id'
    ) {}

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return (new $this->modelClass())->setKeyName($this->keyName)->findOrFail($value);
    }
}
