@extends('layouts.app')

@section('title', 'Как заказать')

@section('breadcrumbs', Breadcrumbs::render('static-instruction'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.static-pages-menu')
    </div>
    <div class="col-12 col-lg-9 static-page">
        <p><span class="title">1. На сайте</span></p>
        <p><strong>С помощью корзины</strong></p>
        <p>
            1.&nbsp;&nbsp;Выберите товар в каталоге<br>
            2.&nbsp;Перейдите на страницу изделия<br>
            3.&nbsp;Выберите размер и нажмите кнопку <b>"В КОРЗИНУ"</b><br>
            4.&nbsp;На странице корзины нажмите <b>"ОФОРМИТЬ ЗАКАЗ"</b><br>
            5.&nbsp;Заполните Ваши ФИО, адрес и телефон и нажмите <b>"ЗАКАЗАТЬ"</b>
        </p>
        <p><strong>В один клик</strong></p>
        <p>
            1.&nbsp;&nbsp;выберите товар в каталоге<br>
            2.&nbsp;перейдите на страницу изделия<br>
            3.&nbsp;выберите размер<br>
            4.&nbsp;заполните Ваши ФИО, населенный пункт и телефон в форме <b>"Заказать в один
                клик"</b><br>
            5.&nbsp;нажмите <b>"ЗАКАЗАТЬ"</b>
        </p>
        <p class="mt-5"><span class="title">2. По телефону</span></p>
        {{-- <p>Позвоните на номер {{ config('contacts.phones') }} и продиктуйте менеджеру:</p> --}}
        <p>Позвоните на номер +375 44 728 66 06 или +375 29 728 66 36 и продиктуйте менеджеру:</p>
        <p>
            Бренд, артикул и размер изделия<br>
            Ваши ФИО и адрес.
        </p>
        <p class="mt-5"><span class="title">3. E-mail и мессенджеры</span></p>
        <p>
            Отправьте нам сообщение на email
            <strong><a
                    href="mailto:{{ config('contacts.email.link') }}">{{ config('contacts.email.link') }}</a></strong>
            или в мессенджере
            <a href="{{ config('contacts.viber.link') }}" title="{{ config('contacts.viber.name') }}"
                class="mx-1">
                <img src="/images/icons/viber-str-color.svg" alt="{{ config('contacts.viber.name') }}">
            </a>
            <a href="{{ config('contacts.whats-app.link') }}" title="{{ config('contacts.whats-app.name') }}"
                class="mx-1">
                <img src="/images/icons/whats-app-str-color.svg"
                    alt="{{ config('contacts.whats-app.name') }}">
            </a>
            <a href="{{ config('contacts.telegram.link') }}" title="{{ config('contacts.telegram.name') }}"
                class="mx-1">
                <img src="/images/icons/telegram-str-color.svg"
                    alt="{{ config('contacts.telegram.name') }}">
            </a>
        </p>
        <p>
            В тексте сообщения укажите:<br>
            • Бренд, артикул и размер изделия;<br>
            • Ваши ФИО и адрес.
        </p>
    </div>
@endsection
