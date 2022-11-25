<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoLink extends Model
{
    use HasFactory;
    protected $guarded  = ['id'];
    protected $dates    = ['frequency_updated_at'];

    public function setFrequencyAttribute($value)
    {
        if ($this->frequency != $value || ($this->frequency == NULL && $value)) {
            $this->attributes['frequency_updated_at'] = Carbon::now();
        }
        $this->attributes['frequency'] = $value;
    }
    public function setDestinationAttribute($value)
    {
        $result = '';
        $parse_url = parse_url($value);
        if (isset($parse_url['path']) && $parse_url['path']) {
            $result .= $parse_url['path'];
            if (isset($parse_url['query']) && $parse_url['query']) {
                $result .= '?' . $parse_url['query'];
            }
        }
        $result = ltrim($result, '/');
        $result = ltrim($result, 'catalog');
        $result = ltrim($result, '/');
        $this->attributes['destination'] = 'catalog/' . $result;
    }
}
