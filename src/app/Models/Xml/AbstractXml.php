<?php

namespace App\Models\Xml;

abstract class AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    abstract function getPartFilename(): string;
}
