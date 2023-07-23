@extends('layouts.app')

@section('title', 'Корзина')

@section('breadcrumbs', Breadcrumbs::render('cart'))

@section('content')

    <div class="col-12 col-md-4 col-xl-5 my-5 text-center text-muted">
        <p>
            Ваш заказ принят!
        </p>
        <h3 class="text-danger">Заказ №{{ $order->id }}</h3>
        <p>
            от {{ date('j F') }} 2021 на сумму
            {!! Currency::format($order->getTotalPrice(), $order->currency) !!}
        </p>
        <div class="row px-5 py-4 text-left" style="background: #FBFBFD">

            <div class="col-6 py-2 font-weight-bold">
                Статус заказа
            </div>
            <div class="col-6 py-2">
                Ожидает подтверждения менеджером
            </div>

            <div class="col-6 py-2 font-weight-bold">
                Способ получения
            </div>
            <div class="col-6 py-2">
                @if (!empty($order->delivery))
                    {{ $order->delivery->name }} <br>
                @endif
                {{ $order->user_addr }}
            </div>

            @if (!empty($order->payment))
                <div class="col-6 py-2 font-weight-bold">
                    Способ оплаты
                </div>
                <div class="col-6 py-2">
                    {{ $order->payment->name }}
                </div>
            @endif

        </div>

        <p class="font-weight-light font-size-12 text-center mt-2">
            По указанному номеру с Вами свяжется менеджер для <br>
            подтверждения условий доставки
        </p>
    </div>

    <div class="col-12 my-5 text-center">
        @foreach ($finalSliders as $finalSlider)
            @if (!empty($finalSlider['products']))
                <div class="row">
                    <div class="col-md-12 mt-3">
                        @include('partials.index.simple-slider', [
                            'simpleSlider' => $finalSlider,
                        ])
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="col-12 my-5 text-center">
        <h3 class="font-weight-light">
            Специально для вас / Недавно просмотренные
        </h3>
        <div class="row justify-content-center mt-3 cart-done-recommended">
            @foreach ($recommended as $product)
                @include('shop.catalog-product', compact('product'))
            @endforeach
        </div>
        <div class="col-12 my-5 text-center">
            <a href="{{ route('shop') }}" class="btn btn-dark px-4">
                Продолжить покупки
            </a>
        </div>
    </div>

@endsection
