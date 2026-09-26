<?php

namespace App\Services\Feeds;

use Illuminate\Contracts\View\View;

class XmlService extends AbstractFeedService
{
    /**
     * Write xml file
     */
    protected function write(): void
    {
        $data = view('xml.' . $this->feedInstance->getViewName(), [
            'currency' => $this->currency,
            'data' => $this->feedInstance->getPreparedData(),
        ]);

        $this->saveToFile($data);
    }

    /**
     * Save xml data to file
     */
    protected function saveToFile(View $data): void
    {
        file_put_contents($this->filePath, $data->render(), LOCK_EX);
    }
}
