<?php

namespace App\Services\Feeds;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Facades\Currency as CurrencyFacade;

class XmlService extends AbstractFeedService
{
    /**
     * Generate xml file
     *
     * @return void
     */
    public function generate(): void
    {
        CurrencyFacade::setCurrentCurrency($this->currency->code);

        $data = view('xml.' . $this->xmlInstance->getKey(), [
            'currency' => $this->currency,
            'data' => $this->xmlInstance->getPreparedData()
        ]);

        $this->saveToFile($data);
    }

    /**
     * Save xml data to file
     *
     * @param View $data
     * @return void
     */
    protected function saveToFile(View $data): void
    {
        file_put_contents($this->filePath, $data->render(), LOCK_EX);
    }
}
