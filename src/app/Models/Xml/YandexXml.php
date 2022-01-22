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
     * @return array
     */
    public function getPreparedData(): array
    {
        return [
            //
        ];
    }
}
