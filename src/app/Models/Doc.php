<?php

namespace App\Models;

use Database\Factories\DocFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property int $sort
 * @property string $html
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Doc extends Model
{
    /** @use HasFactory<DocFactory> */
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
