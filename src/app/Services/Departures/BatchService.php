<?php

namespace App\Services\Departures;

use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BatchService
{
    /**
     * Create label
     */
    public function createBatchCsv(Batch $batch)
    {
        $result = [];
        $resultFileName = $batch->id . '.csv';
        $resultDir = 'departures/batch_send';
        $resultPath = $resultDir . '/' . $resultFileName;
        $resultPublicPath = public_path('storage/' . $resultPath);
        $resultStoragePath = storage_path('app/public/' . $resultPath);
        $zipPath = $resultDir . '/' . $batch->id . '.zip';
        $zipStoragePath = storage_path('app/public/' . $zipPath);

        File::ensureDirectoryExists(dirname(public_path($resultPublicPath)));
        $batch->loadMissing(
            [
                'orders' => fn ($query) => $query->with([
                    'itemsExtended' => fn ($query) => $query
                        ->whereIn('status_key', Order::$itemDepartureStatuses)
                        ->with('installment'),
                    'onlinePayments',
                    'delivery',
                    'user' => fn ($q) => $q->with('lastAddress'),
                ]),
            ]
        );
        $price = 11.46;
        foreach ($batch->orders as $key => $order) {
            $cod = ($order->payment_id == 1 || $order->payment_id == 4) ? $order->getTotalCODSum() : null;
            $result[] = [
                ++$key, // * Порядковый номер ПО в списке (A) (1 – 9999999)
                $order->first_name, // * Фамилия (B)
                $order->last_name, // Имя (C)
                $order->patronymic_name, // Отчество (D)
                'BY', // * ISO страны назначения (E) (BY – для Белоруссии)
                'БЕЛАРУСЬ', // * Страна назначения (F)
                ($order->user->lastAddress->zip ?? null), // * Почтовый индекс ОПС назначения (G)
                null, // ОПС назначения (H)
                ($order->user->lastAddress->region ?? null), // Область (I)
                ($order->user->lastAddress->district ?? null), // Район (J)
                ($order->user->lastAddress->city ?? null), // * НП назначения (K)
                ($order->user->lastAddress->street ?? null), // Улица (L)
                ($order->user->lastAddress->corpus ?? null), // Корпус (M)
                ($order->user->lastAddress->house ?? null), // Дом (N)
                ($order->user->lastAddress->room ?? null), // Квартира (O)
                0, // * Группа страны назначения (P) ("Не используется Всегда «0»")
                2, // * Тип ПО  (Q) (1 – индивидуальное; 2 - партия)
                0, // * Уведомление (R) (0 – без уведомления; 1 – простое; 2 – заказное; 5 – электронное; 7 – SMS уведомление)
                2, // * Отправитель ПО (S) (1 – население; 2 – организация;)
                0, // * Категория ПО (T) (0 – отправление по РБ 1 – неприритетное 2 – приоритетное)
                0, // * "Уведомить о вручении VIP (U) (0 – не уведомлять; 1 – уведомление; VIP)
                ($order->weight ?? 1200), // * Вес ПО (V)
                0, // * Объявленная ценность (W) (0 – без объявленной ценности)
                $price, // * Стоимость ПО (X)
                0, // * Особые отметки – «Осторожно»(Y) (0 – отсутствие отметки  1 – наличие отметки)
                $cod, // Сумма наложенного платежа (Z) (По умолчанию «0»)
                0, // Для посылок с объявленной ценностью значение «0»  (AA) (По умолчанию «0»)
                null, // Ярлык (штрихкод отправления) (AB)
                null, // Спецярлык (AC)
                null, // Ярлык (штрихкод) заказного уведомления )(AD)
                null, // Зарезервировано (AE)
                null, // Зарезервировано (AF)
                substr(preg_replace('/[^0-9]/', '', trim($order->phone)), -12), // Телефон получателя (AG)
                $order->email, // Эл. адрес получателя (AH)
            ];
        }
        array_unshift($result, [
            291711523, // УНН предприятия (A)
            date('d.m.Y', strtotime('now')), // Дата списка (B)
            (count($batch->orders) * $price), // Сумма оплаты ПО (C)
            count($batch->orders), // Количество ПО (D) (Равно количеству ПО списка)
            50, // Вид ПО (E) (Код почтового отправления)
            1, // Направление ПО (F) (1 – РБ 2 – СНГ 3 – ДЗ)
            0, // Вид оплаты (G) ("0 – вид оплаты не указан 1 – Наличными 2 – Плат.поручением  3 – Электронный л/с 4 – Ав.квитанцией 5 – Ден.документом 6 – Гарант.письмом)
            0, // Договорной тариф (H) (0– отсутствие тарифа 1 – наличие тарифа)
            1, // Наложенный платеж без объявленной ценности (I) (0 – отсутствие отметки 1 – наличие отметки)
            375291793790, // Телефон отправителя (J)
            'info@barocco.by', // Электронный адрес отправителя (K)
        ]);
        $fp = fopen($resultStoragePath, 'w');
        foreach ($result as $fields) {
            fputcsv($fp, array_map(fn ($value) => iconv('UTF-8', 'Windows-1251//IGNORE', $value), $fields), ';');
        }
        fclose($fp);

        $zip = new ZipArchive();
        $zip->open($zipStoragePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($resultStoragePath, $resultFileName);
        $zip->close();

        return url('storage/' . $zipPath);
    }
}
