<?php

namespace App\Models\Xml;

abstract class AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Prepare data for xml file
     *
     * @return array
     */
    abstract public function getPreparedData(): array;

    /**
     * Return host url
     *
     * @return string
     */
    public function getHost(): string
    {
        return 'https://' . request()->getHost();
    }
}
