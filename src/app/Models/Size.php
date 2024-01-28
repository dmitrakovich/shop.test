<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Size extends Model
{
    use AttributeFilterTrait;

    final const ONE_SIZE_SLUG = 'size-none';

    /**
     * Generate filter badge name
     */
    public function getBadgeName(): string
    {
        return 'Размер: ' . $this->name;
    }
}
