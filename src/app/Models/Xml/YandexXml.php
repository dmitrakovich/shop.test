<?php

namespace App\Models\Xml;

use App\Models\Color;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class YandexXml extends AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'yandex';
    }

    /**
     * Prepare data for xml file
     *
     * @return object
     */
    public function getPreparedData(): object
    {
        return (object)[
            //
        ];
    }





    /**
     * Prepare color from colors for filters
     *
     * @param EloquentCollection $colors
     * @return array
     */
    public function getColors(EloquentCollection $colors): array
    {
        return $colors->map(function (Color $color) {
            return $color->name;
        })->toArray();
    }
}
