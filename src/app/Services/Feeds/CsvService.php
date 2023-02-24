<?php

namespace App\Services\Feeds;

class CsvService extends AbstractFeedService
{
    /**
     * Generate csv file
     */
    public function generate(): void
    {
        $this->saveToFile($this->feedInstance->getPreparedData());
    }

    /**
     * Save csv data to file
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
