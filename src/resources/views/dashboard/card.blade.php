@extends('layouts.app')

@section('title', 'Мои заказы')

@section('breadcrumbs', Breadcrumbs::render('dashboard-card'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.dashboard-menu')
    </div>

    <div class="col-12 col-lg-9 static-page">
        <div class="row">
            <div class="col-12 col-md-6 col-lg-12 col-xl-6 mb-5">
                <img src="/images/dashboard/card.svg" alt="" class="img-fluid w-100">
                <div class=" mt-2">
                    <a href="#" class="text-muted text-decoration-underline">Активировать</a>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-12 col-xl-6 mb-5">
                <h2>КАКРТА КЛИЕНТА</h2>
                <p>
                    Для постоянных покупателей BAROCCO действует программа лояльности.
                    Совершая покупку на любую сумму, вы получаете в подарок накопительную карту клиента
                    с первоначальной скидкой - 5% на следующий заказ.
                    test rollback
                </p>
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-2 col-xl-1 font-size-18 font-weight-bold">
                                5%
                            </div>
                            <div class="col">после первой покупки</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-2 col-xl-1 font-size-18 font-weight-bold">
                                7%
                            </div>
                            <div class="col">при накопления покупок на сумму 2000 BYN</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-2 col-xl-1 font-size-18 font-weight-bold">
                                10%
                            </div>
                            <div class="col">при накопления покупок на сумму 3000 BYN</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-2 col-xl-1 font-size-18 font-weight-bold">
                                15%
                            </div>
                            <div class="col">при накопления покупок на сумму 5000 BYN</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <p class="text-muted">
                    Скидка по карте клиента не действует на первую покупку.
                    Карта клиента не действует во время специальных акций и распродаж.
                    BAROCCO оставляет за собой право изменение правил пользования картой в одностороннем порядке
                </p>
            </div>
        </div>

    </div>
@endsection
