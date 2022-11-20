<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Product size class
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Size extends Model
{
    use AttributeFilterTrait;

    final const ONE_SIZE_SLUG = 'size-none';

    public $timestamps = false;

    /**
     * Generate filter badge name
     */
    public function getBadgeName(): string
    {
        return 'Размер: ' . $this->name;
    }
}
