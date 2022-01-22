<?php

namespace App\Models\Xml;

class YandexXml extends AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    public function getPartFilename(): string
    {
        return 'yandex_';
    }
}
