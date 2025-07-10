<?php

namespace App\Models\Seo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $folder_enum_id Enum id каталога
 * @property string|null $seo_url Seo ссылка
 * @property string|null $destination Куда приведет seo ссылка
 * @property string|null $tag Тег
 * @property int|null $frequency Частота
 * @property \Illuminate\Support\Carbon|null $frequency_updated_at Дата/время обновления поля частота
 * @property string|null $h1 h1 заголовок
 * @property string|null $main_text Основной текст
 * @property string|null $meta_title Meta title
 * @property string|null $meta_description Meta description
 * @property string|null $meta_keywords Meta keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SeoLink extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
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
