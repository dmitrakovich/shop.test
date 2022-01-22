<?php

namespace App\Models\Xml;

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
}
