<?php

namespace App\Models\ProductAttributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Manufacturer extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];
}
