@extends('layouts.app')

@section('title', 'Возврат')

@section('breadcrumbs', Breadcrumbs::render('static-return'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.static-pages-menu')
    </div>
    <div class="col-12 col-lg-9 static-page">
        <div class="col-12 py-2 text-center text-muted font-size-12" style="background-color: #EEF6FC;">
            Вы можете вернуть товары, которые вам не подошли, в течение 14 дней с момента получения
            заказа.
        </div>
        <p class="mt-5"><span class="title">1. Условия возврата</span></p>
        <p>Мы принимаем товар для обмена либо возврата денежных средств при следующих условиях:</p>
        <p>
            <span class="pl-3">
                • Сохранены бирки производителя и упаковка
            </span><br>
            <span class="pl-3">
                • Товар не находился в употреблении, потребительские свойства товара сохранены
            </span><br>
            <span class="pl-3">
                • Наличие товарного чека
            </span>
        </p>
        <p class="mt-5"><span class="title">2. Инструкция</span></p>
        <div class="row">
            <div class="col-12 col-xl-4">
                <p>
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">1</div>
                    <div class="col"><strong>Сообщите о возврате</strong></div>
                </div>
                </p>
                <p class="text-muted">
                    Если купленная вещь Вам не подошла, позвоните по номеру +375 44 728 66 06 или
                    отправьте письмо по e-mail INFO@MODNY.BY
                </p>
            </div>
            <div class="col-12 col-xl-4">
                <p>
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">2</div>
                    <div class="col"><strong>Отправьте товар почтой</strong></div>
                </div>
                </p>
                <p class="text-muted">
                    Отправьте обычной посылкой без объявленной ценности и наложенного платежа по адресу
                    г. Брест, ул. Советская, 72
                </p>
                <p class="text-muted font-italic">
                    ! К возврату и обмену не принимаются товары отправленные наложенным платежом,
                    вследствие чего данные товары будут храниться на почте в течение тридцати дней, а
                    затем будут возвращены почтой на адрес отправителя.
                </p>
            </div>
            <div class="col-12 col-xl-4">
                <p>
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">3</div>
                    <div class="col"><strong>Дождитесь возврата</strong></div>
                </div>
                </p>
                <p class="text-muted">
                    Денежные средства будут возвращены на карту, с которой Вы оплачивали в срок от 1 до
                    30 дней (в зависимости от банка выпустившего карту) с момента получения возврата.
                </p>
            </div>
        </div>
    </div>
@endsection
