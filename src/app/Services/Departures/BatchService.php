<?php

namespace App\Services\Departures;

use App\Models\Orders\Batch;
use Illuminate\Support\Facades\File;

class BatchService
{
    /**
     * Create label
     */
    public function createBatchCsv(Batch $batch)
    {
        $result = [];
        $resultPath = 'storage/departures/batch_send/' . $batch->id . '.csv';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $batch->load(['orders' => fn ($query) => $query->with('items')]);
        $price = 11.46;
        foreach ($batch->orders as $key => $order) {
            $cod = ($order->payment_id == 1) ? $order->getItemsPrice() : null;
            $result[] = [++$key, $order->first_name, $order->last_name, $order->patronymic_name, 'BY', 'БЕЛАРУСЬ', ($order->zip ?? ''), '', '', '', $order->city, $order->user_addr, '', '', '', 10, 2, 0, 2, 0, 0, ($order->weight ?? 1200), 0, $price, '', $cod, 0, '', '', '', '', '', $order->phohe, 'info@modny.by'];
        }
        array_unshift($result, [291711523, date('d.m.Y', strtotime('now')), (count($batch->orders) * $price), count($batch->orders), 50, 1, 0, 0, 1, 375291793790, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $fp = fopen(public_path($resultPath), 'w');
        foreach ($result as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        return url('/storage/departures/batch_send/' . $batch->id . '.csv');
    }
}
