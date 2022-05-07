<?php

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndexLink extends Model
{
    use HasFactory;

    protected $casts = [
        'links' =>'json',
    ];

    /**
     * Interact with the index page's links.
     *
     * @return Attribute
     */
    public function links(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_values(json_decode($value, true) ?: []),
            set: fn ($value) => $this->attributes['links'] = json_encode(array_values($value))
        );
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
