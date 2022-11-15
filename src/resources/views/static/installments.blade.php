@extends('layouts.app')

@section('title', 'Рассрочка')

@section('breadcrumbs', Breadcrumbs::render('static-installments'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.static-pages-menu')
    </div>
    <div class="col-12 col-lg-9 static-page">
        <p class="mt-5"><span class="title">1. Порядок оплаты</span></p>
        <div class="row">
            <div class="col-4 col-md-auto text-center text-md-left">
                1-й платеж - 40% стоимости<span class="d-none d-md-inline">;</span>
            </div>
            <div class="col-4 col-md-auto text-center text-md-left">
                2-й платеж - 30% стоимости<span class="d-none d-md-inline">;</span>
            </div>
            <div class="col-4 col-md-auto text-center text-md-left">
                3-й платеж - 30% стоимости
            </div>
        </div>
        <p class="mt-5"><span class="title">2. Преимущества и условия</span></p>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">1</div>
                    <div class="col"><strong>0% комиссий и переплат</strong></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-2 mt-lg-0">
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">2</div>
                    <div class="col"><strong>Срок рассрочки 3 месяца</strong></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-2 mt-lg-0">
                <div class="col-12 row align-items-center">
                    <div class="col-auto step-num border rounded-circle">3</div>
                    <div class="col"><strong>Минимальная сумма заказа с оплатой в рассрочку от 100
                            р.</strong></div>
                </div>
            </div>
        </div>
        <p class="mt-5"><span class="title">3. Процесс оформления рассрочки</span></p>
        <p>
            При оформлении заказа выберите в корзине "Оформить рассрочку". После чего с Вами свяжется
            наш специалист и уточнит Ваши паспортные и анкетные данные. После их обработки принимается
            решение о возможности предоставления рассрочки. Договор о рассрочке курьер доставит вместе с
            товаром.
        </p>
        <p class="text-muted font-italic">
            *Оформление рассрочки не является публичной офертой. Продавец оставляет за собой право
            отказать в предоставлении рассрочки без объяснения причин.
        </p>
        <p class="mt-5"><span class="title">4. Вопросы и ответы</span></p>
        <p>
            <b>Что необходимо, чтобы купить товар в рассрочку?</b><br>
            Необходимо предоставить паспортные данные и ответить на вопросы оператора.
        </p>
        <p>
            <b>Какие документы необходимы для покупки в рассрочку?</b><br>
            Только паспор
        </p>
        <p>
            <b>Справку о доходах необходимо предоставлять?</b><br>
            Нет
        </p>
        <p>
            <b>Существуют ли возрастные ограничения на получение рассрочки?</b><br>
            Да. Рассрочка предоставляется лицам в возрасте от 21 года до 64 лет (включительно).
        </p>
        <p>
            <b>Как предоставляются паспортные данные?</b><br>
            Паспортные данные предоставляются отправки по e-mail или через Viber.
        </p>
        <p>
            <b>Я могу оформить две рассрочки одновременно?</b><br>
            Да, но после того, как вы выплатите половину от предыдущей рассрочки.
        </p>
        <p>
            <b>Какой банк предоставляет рассрочку?</b><br>
            Рассрочку предоставляет непосредственно интернет-магазин.
        </p>
        <div class="row mt-5">
            <div class="col-12 my-4">
                <h2>Оформить рассрочку</h2>
            </div>
            <div class="col-12 col-md-auto">
                <input type="text" name="last_name" class="form-control" placeholder="Фамилия">
            </div>
            <div class="col-12 col-md-auto mt-2 mt-md-0">
                <input type="text" name="first_name" class="form-control" placeholder="Имя">
            </div>
            <div class="col-12 col-md-auto mt-2 mt-md-0">
                <input type="text" name="patronymic_name" class="form-control" placeholder="Отчество">
            </div>
            <div class="col-12 my-3">
                Копия паспорта Выбрать файл
            </div>
            <div class="col-12">
                <button class="col-12 col-md-auto px-md-5 btn btn-dark btn-lg">Отправить заявку</button>
            </div>
        </div>
    </div>
@endsection
