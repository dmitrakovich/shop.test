<?php

namespace App\Services\Feeds;

use Illuminate\Contracts\View\View;

class XmlService extends AbstractFeedService
{
    /**
     * Generate xml file
     *
     * @return void
     */
    public function generate(): void
    {
        $data = view('xml.' . $this->feedInstance->getKey(), [
            'currency' => $this->currency,
            'data' => $this->feedInstance->getPreparedData(),
        ]);

        $this->saveToFile($data);
    }

    /**
     * Save xml data to file
     *
     * @param  View  $data
     * @return void
     */
    protected function saveToFile(View $data): void
    {
        file_put_contents($this->filePath, $data->render(), LOCK_EX);
    }
}
