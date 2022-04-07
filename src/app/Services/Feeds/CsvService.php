<?php

namespace App\Services\Feeds;

use Illuminate\Contracts\View\View;
use App\Facades\Currency as CurrencyFacade;

class CsvService extends AbstractFeedService
{
    /**
     * Generate csv file
     *
     * @return void
     */
    public function generate(): void
    {
        return;

        // CurrencyFacade::setCurrentCurrency($this->currency->code);

        // $data = view('xml.' . $this->xmlInstance->getKey(), [
        //     'currency' => $this->currency,
        //     'data' => $this->xmlInstance->getPreparedData()
        // ]);

        // $this->saveToFile($data);
    }

    /**
     * Save csv data to file
     *
     * @param View $data
     * @return void
     */
    protected function saveToFile(View $data): void
    {
        file_put_contents($this->filePath, $data->render(), LOCK_EX);
    }
}
