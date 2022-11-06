<?php

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Index page's links class
 *
 * @property int $id
 * @property string $title
 * @property array $links
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @mixin Builder
 */
class IndexLink extends Model
{
    use HasFactory;

    protected $casts = [
        'links' =>'json',
    ];

    /**
     * Set the index page's links.
     */
    public function setLinksAttribute(array $value): void
    {
        $this->attributes['links'] = json_encode(array_values($value));
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
