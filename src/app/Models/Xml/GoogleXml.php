<?php

namespace App\Models\Xml;

class GoogleXml extends AbstractXml
{
    /**
     * Return part of a filename
     *
     * @return string
     */
    public function getPartFilename(): string
    {
        return 'google_';
    }
}
