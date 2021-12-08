<?php

namespace Database\Seeders;

class OrderStatusSeeder extends AbstractSeeder
{
    protected $sortColumn = 'sorting';
    protected $tableName = 'order_statuses';
    protected $values = [
        ['key' => 'new', 'name_for_admin' => 'Принят', 'name_for_user' => 'Принят'],
        ['key' => 'canceled', 'name_for_admin' => 'Отменен', 'name_for_user' => 'Отменен'],
        ['key' => 'in_work', 'name_for_admin' => 'В работе', 'name_for_user' => 'В работе'],
        ['key' => 'wait_payment', 'name_for_admin' => 'Ожидает оплату', 'name_for_user' => 'Ожидает оплату'],
        ['key' => 'paid', 'name_for_admin' => 'Оплачен', 'name_for_user' => 'Оплачен'],
        ['key' => 'assembled', 'name_for_admin' => 'Собран', 'name_for_user' => 'В работе'],
        ['key' => 'packaging', 'name_for_admin' => 'На упаковку', 'name_for_user' => 'В работе'],
        ['key' => 'ready', 'name_for_admin' => 'Готов к отправке', 'name_for_user' => 'Готов к отправке'],
        ['key' => 'sent', 'name_for_admin' => 'Отправлен', 'name_for_user' => 'Отправлен'],
        ['key' => 'fitting', 'name_for_admin' => 'Отправлен на примерку', 'name_for_user' => 'Отправлен на примерку'],
        ['key' => 'complete', 'name_for_admin' => 'Выполнен', 'name_for_user' => 'Выполнен'],
        ['key' => 'return', 'name_for_admin' => 'Возвращен', 'name_for_user' => 'Возвращен'],
        ['key' => 'return_fitting', 'name_for_admin' => 'Возвращен с примерки', 'name_for_user' => 'Возвращен'],
    ];
}
