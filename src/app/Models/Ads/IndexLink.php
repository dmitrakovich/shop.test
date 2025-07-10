<?php

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property array $links
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class IndexLink extends Model
{
    protected $casts = [
        'links' => 'json',
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
