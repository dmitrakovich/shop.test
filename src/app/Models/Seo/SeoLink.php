<?php

namespace App\Models\Seo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoLink extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'frequency_updated_at' => 'datetime',
    ];

    public function setFrequencyAttribute($value)
    {
        if ($this->frequency != $value || ($this->frequency == null && $value)) {
            $this->attributes['frequency_updated_at'] = Carbon::now();
        }
        $this->attributes['frequency'] = $value;
    }

    public function setDestinationAttribute($value)
    {
        if ($value) {
            $result = '';
            $parse_url = parse_url($value);
            $result .= ($parse_url['path'] ?? '') . (!empty($parse_url['query']) ? ('?' . $parse_url['query']) : '');
            $result = ltrim(ltrim(ltrim($result, '/'), 'catalog'), '/');
            $this->attributes['destination'] = 'catalog/' . $result;
        } else {
            $this->attributes['destination'] = null;
        }
    }
}
