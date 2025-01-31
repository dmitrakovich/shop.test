<?php

namespace App\Data\Casts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        if (is_array($value)) {
            return $this->getForCollection($value);
        }

        return $this->modelClass::query()->where($this->keyName, $value)->firstOrFail();
    }

    /**
     * Retrieves a collection of models based on an array of values
     *
     * @param  array  $values  Array of values to search for in the keyName column
     * @return \Illuminate\Database\Eloquent\Collection Collection of found models
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If any values are not found
     */
    private function getForCollection(array $values): Collection
    {
        $result = $this->modelClass::query()->whereIn($this->keyName, $values)->get();

        $notFoundIds = array_diff($values, $result->pluck($this->keyName)->toArray());
        if ($notFoundIds) {
            throw (new ModelNotFoundException())->setModel($this->modelClass, $notFoundIds);
        }

        return $result;
    }
}
