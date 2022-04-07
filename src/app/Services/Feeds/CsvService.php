<?php

namespace App\Services\Feeds;

class CsvService extends AbstractFeedService
{
    /**
     * Generate csv file
     *
     * @return void
     */
    public function generate(): void
    {
        $this->saveToFile($this->feedInstance->getPreparedData());
    }

    /**
     * Save csv data to file
     *
     * @param object $data
     * @return void
     */
    protected function saveToFile(object $data): void
    {
        $stream = fopen($this->filePath, 'w');

        fputcsv($stream, $data->headers);

        foreach ($data->rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);
    }
}
