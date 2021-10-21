<?php

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndexLink extends Model
{
    use HasFactory;

    protected $casts = [
        'links' =>'json',
    ];

    public function getLinksAttribute($value)
    {
        return array_values(json_decode($value, true) ?: []);
    }

    public function setLinksAttribute($value)
    {
        $this->attributes['links'] = json_encode(array_values($value));
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
