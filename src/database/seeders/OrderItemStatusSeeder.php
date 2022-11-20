<?php

namespace Database\Seeders;

class OrderItemStatusSeeder extends AbstractSeeder
{
    protected $sortColumn = 'sorting';

    protected $tableName = 'order_item_statuses';

    protected $values = [
        ['key' => 'new', 'name_for_admin' => 'Принят', 'name_for_user' => 'Принят'],
        ['key' => 'delete', 'name_for_admin' => 'Удалена', 'name_for_user' => 'Удалена'],
        ['key' => 'reserved', 'name_for_admin' => 'Отложено', 'name_for_user' => 'Отложено'],
        ['key' => 'pickup', 'name_for_admin' => 'Забрано', 'name_for_user' => 'Забрано'],
        ['key' => 'packaging', 'name_for_admin' => 'Упаковано', 'name_for_user' => 'Упаковано'],
        ['key' => 'sent', 'name_for_admin' => 'Отправлен', 'name_for_user' => 'Отправлен'],
        ['key' => 'fitting', 'name_for_admin' => 'Отправлен на примерку', 'name_for_user' => 'Отправлен на примерку'],
        ['key' => 'complete', 'name_for_admin' => 'Выкуплен', 'name_for_user' => 'Выкуплен'],
        ['key' => 'return', 'name_for_admin' => 'Возвращен', 'name_for_user' => 'Возвращен'],
    ];
}
