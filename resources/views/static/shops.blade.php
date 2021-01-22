@extends('layouts.app')

@section('title', 'Магазины')

@section('breadcrumbs', Breadcrumbs::render('static-shops'))

@section('content')
<div class="col-12 static-page">
    <div class="row mt-4">
        <div class="col-md-8 mb-5">
            <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ab642935229b2f4e923884c82a4de9c7c5f983779f4b37c73c1229af726f79d2f&amp;width=100%25&amp;height=469&amp;lang=ru_RU&amp;scroll=true"></script>
        </div>
        <div class="col-md-4 mb-5">
            <div class="col-12 border-bottom mb-2">
                <p>
                    ул. Советская 72
                    <a href="tel:+375292465824" class="float-right text-decoration-underline">
                        <b>Позвонить</b>
                    </a><br>
                    Брест
                </p>
                <p>10.00 - 21.00 ежедневно</p>
            </div>
            <div class="col-12 border-bottom mb-2">
                <p>
                    ТЦ Моcква
                    <a href="tel:+375298357797" class="float-right text-decoration-underline">
                        <b>Позвонить</b>
                    </a><br>
                    Брест
                </p>
                <p>10.30 - 18.00 ежедневно</p>
            </div>
            <div class="col-12 border-bottom mb-2">
                <p>
                    ул. Гоголя 67
                    <a href="tel:+375298367797" class="float-right text-decoration-underline">
                        <b>Позвонить</b>
                    </a><br>
                    Брест
                </p>
                <p>10.00 - 21.00 ежедневно
            </div>
            <div class="col-12 mb-2">
                <p>
                    ул. Советская 49
                    <a href="tel:+375292465824" class="float-right text-decoration-underline">
                        <b>Позвонить</b>
                    </a><br>
                    Брест
                </p>
                <p>10.00 - 21.00 ежедневно</p>
            </div>
        </div>
    </div>
    <h1 class="display-4 text-center" style="font-size: 26px;">
        Почему Вам стоит заказать на barocco.by
    </h1>
    <div class="col-12 mt-4 mb-5">
        @include('includes.advantages-block')
    </div>
</div>
@endsection
