@extends('layouts.app')


@section('content')
<div class="my-5 col-12 text-center">
    <h1 class="text-danger h3">Счёт № {{ $online_payment->payment_num }}</h1>
    <p class="text-muted">к заказу № {{ $online_payment->order_id }} от {{ $online_payment->order->created_at->format('d.m.Y') }}</p>
    <p class="h4">Сумма {{ $online_payment->amount }} BYN</p>
</div>
<div class="my-3 col-12">
    <h2 class="h3 mb-3 text-center font-weight-normal">Как оплатить?</h3>
        <div class="row">
            @if($online_payment->qr_code)
            <div class="col-12 col-md-6 mb-4">
                <h3 class="h4">По QR-коду в мобильном банкинге</h3>
                <p>1. Выберите в Вашем мобильном банкинге способ оплаты<br><i>E-POS - оплата товаров по QR-коду</i></p>
                <p>2. Отсканируйте код ниже:</p>
            </div>
            @endif
            <div class="col-12 col-md-6">
                <h3 class="h4">Оплата в ЕРИП по номеру счета</h3>
                <p>Оплатить можно через моюильный и интернет-банкинг, банкомат и инфокиоск, в отделении банка или почты.</p>
                <p><b>Порядок оплаты</b></p>
                <p>1. Выберите пункт <i>Система "Расчёт" (ЕРИП)</i>.</p>
                <p>2. Перейдите в <i>Сервис E-POS</i> (второй в списке).</p>
                <p>3. Введите номер счета <b><i>18464-1-{{ $online_payment->payment_num }}</i></b>.</p>
                <p>4. Проверьте правильность введенных данных. Дополните при необходимости.</p>
                <p>5. Подтвердите платеж.</p>

                <!--
                <h3 class="h4">3-й способ</h3>
                <p>Оплата в ЕРИП - выбор среди списка услуг</p>
                <ul class="pl-3">
                    <li>Пункт “Система “Расчет” (ЕРИП)</li>
                    <li>Интернет-магазины/сервисы</li>
                    <li>A-Z Латинские домены</li>
                    <li>«B»</li>
                    <li>Barocco.by (в инфокиосках РУП «Белпочта»может быть указано «Оплата товара ООО БароккоСтайл»).</li>
                </ul>
                <p>Далее введите номер договора (соответствует номеру счета) и сумму, если не указана.</p>

-->
            </div>
        </div>

</div>

@endsection
