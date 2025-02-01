@extends('layouts.app')

@section('title', 'Корзина')

@section('breadcrumbs', Breadcrumbs::render('cart'))

@section('content')
    <div class="col-11 mt-lg-4 mb-5 cart-page">
        @if ($cart->items->isNotEmpty())
            <form action="{{ route('orders.store', '#user-data-validate') }}" id="cartData" method="post">
                <div class="row">
                    @foreach ($cart->items as $item)
                        <div class="col-12 py-3 border-bottom border-secondary">
                            <div class="row">
                                <div class="pl-0 col-6 col-md-2 ">
                                    <a href="{{ $item->product->getUrl() }}" target="_blank">
                                        <img src="{{ $item->product->getFirstMediaUrl('default', 'catalog') }}"
                                            alt="{{ $item->product->sku }}" class="img-fluid">
                                    </a>
                                </div>
                                <div class="col-6 col-md-10">
                                    <div class="row position-relative h-100">
                                        <div class="col-12 col-md-3">
                                            <a href="{{ $item->product->getUrl() }}" target="_blank">
                                                {{ $item->product->brand->name }}
                                                {{ $item->product->id }}
                                            </a><br>
                                            <small>{{ $item->product->category->title }}</small><br>
                                            <small>Размер: {{ $item->size->name }}</small><br>
                                            <small>Цвет: {{ $item->product->color_txt }}</small><br>
                                        </div>
                                        <div class="col-12 col-md-2 mt-md-2">
                                            {{ DeclensionNoun::make($item->count, 'пара') }}
                                        </div>
                                        @if ($item->isAvailable())
                                            <div class="col-12 col-md-2 mt-md-2">
                                                @if ($item->product->hasDiscount())
                                                    <span class="old_price text-muted">
                                                        {!! $item->product->getFormattedOldPrice() !!}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-12 col-md-3 mt-md-2 item-sales">
                                                @if ($item->product->hasDiscount())
                                                    @foreach ($item->product->getSales() as $sale)
                                                        <p class="sale-block">
                                                            <span
                                                                class="product-label product-label-sale px-1">
                                                                {{ $sale->discount_percentage }}%
                                                            </span>&nbsp;
                                                            <span>{{ $sale->label }}</span>
                                                            @if (!empty($sale->end_datetime))
                                                                <span class="text-danger">
                                                                    {{-- blade-formatter-disable-next-line --}}
                                                                @include('includes.timer', ['end_time' => $sale->end_datetime])
                                                                </span>
                                                            @endif
                                                            <br />
                                                            <span>-{{ Currency::convertAndFormat($sale->discount) }}</span>
                                                        </p>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="col-12 col-md-2 mt-md-2 mb-4">
                                                <span
                                                    class="{{ $item->product->hasDiscount() ? 'new_price' : 'price' }}">
                                                    {!! $item->product->getFormattedPrice() !!}
                                                </span>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-2 offset-md-5 mt-md-2 mb-4">
                                                <span class="text-danger text-right">нет в наличии</span>
                                            </div>
                                        @endif

                                        <div class="col-12 col-auto mt-auto">
                                            <div class="row">
                                                <div class="col-auto">
                                                    <a href="{{ route('cart-delete', $item->id) }}"
                                                        class="text-muted text-decoration-underline">
                                                        Удалить из корзины
                                                    </a>
                                                </div>
                                                <div class="col-auto d-none d-md-block">
                                                    <a href=""
                                                        class="text-muted text-decoration-underline">
                                                        В избранное
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row pb-3 pb-md-5 border-bottom border-secondary">
                    <div class="col-12 col-md-6 mt-3 mt-md-5">
                        <p class="font-size-18"><b>Способ доставки:</b></p>
                        @foreach ($deliveryMethods as $deliveryMethod)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_id"
                                    id="delivery-{{ $deliveryMethod->id }}"
                                    value="{{ $deliveryMethod->id }}"
                                    {{ $cart->itemsCount() > 3 && in_array($deliveryMethod->getRawOriginal('instance'), ['BelpostCourierFitting', 'ShopPvz']) ? 'disabled' : null }}
                                    {{ $loop->first ? 'checked' : null }} />
                                <label class="form-check-label" for="delivery-{{ $deliveryMethod->id }}">
                                    {{ $deliveryMethod->name }}
                                </label>
                                {!! $deliveryMethod->instance->getAdditionalInfo() !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="col-12 col-md-6 mt-3 mt-md-5">
                        <p class="font-size-18"><b>Способ оплаты:</b></p>
                        @foreach ($paymentsList as $key => $value)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_id"
                                    id="payment-{{ $key }}" value="{{ $key }}"
                                    {{ $loop->first ? 'checked' : null }} />
                                <label class="form-check-label" for="payment-{{ $key }}">
                                    {{ $value }}
                                </label>
                                {{-- todo: сделать по аналогии с доставками --}}
                                @if ($value == 'Оформить рассрочку')
                                    <div class="additional-form-check-info text-muted font-size-12">
                                        {{-- blade-formatter-disable --}}
                                        @if ($totalPriceWithoutUserSale >= $g_installmentMinPrice3Parts)
                                            Рассрочка на 3 платежа
                                            Первый взнос <span class="border-bottom border-secondary">{{ $totalPriceWithoutUserSale - $totalPriceWithoutUserSale * 0.6 }} руб.</span>
                                            Оставшиеся 2 платежа по <span class="border-bottom border-secondary">{{ $totalPriceWithoutUserSale * 0.3 }} руб.</span> в месяц
                                        @else
                                            Рассрочка на 2 платежа
                                            Первый взнос <span class="border-bottom border-secondary">{{ $totalPriceWithoutUserSale - $totalPriceWithoutUserSale * 0.5 }} руб.</span>
                                            Оставшийся платеж <span class="border-bottom border-secondary">{{ $totalPriceWithoutUserSale * 0.5 }} руб.</span>
                                        @endif
                                        <span class="text-danger">При покупке с рассрочкой скидка клиента не действует!</span>
                                        {{-- blade-formatter-enable --}}
                                    </div>
                                @elseif (($currentCountry->id == 2 || $currentCountry->id == 3) && $value == 'При получении')
                                    <div class="additional-form-check-info text-muted font-size-12">
                                        Условие для РФ и Казахстана! Предоплата 10% перед отправкой,
                                        остальное при получении наложенным платежом.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row my-5">

                    <div class="col-12 col-md-6">
                        <div class="row">
                            <div class="col-auto">
                                <input type="text" class="form-control"
                                    value="{{ $cart->promocode?->code }}" placeholder="#ПРОМОКОД"
                                    name="promocode" @disabled($cart->hasPromocode() || auth()->guest())>
                            </div>
                            <div class="col-auto">
                                <button type="button" id="applyPromoCodeButton" class="btn btn-dark px-5"
                                    @disabled(auth()->guest())>
                                    {{ $cart->hasPromocode() ? 'Отменить' : 'Применить' }}
                                </button>
                            </div>
                            @guest
                                <div class="col-12 mt-1">
                                    <span class="text-danger font-italic">
                                        Для применения промокода авторизуйтесь
                                    </span>
                                </div>
                            @endguest
                        </div>
                    </div>

                    <div class="col-12 offset-md-1 col-md-5">
                        <div class="row justify-content-between mb-2">
                            <div class="col-auto">
                                СТОИМОСТЬ ЗАКАЗА
                            </div>
                            <div class="col-auto text-right">

                                {{-- !! temporary shitcode for price !! --}}
                                <div class="js-normal-price">
                                    @if ($totalPrice < $totalOldPrice)
                                        <span class="old_price text-muted">{!! Currency::format($totalOldPrice) !!}</span>
                                        <strong class="price">{!! Currency::format($totalPrice) !!}</strong><br>
                                        <span class="new_price">Вы экономите {!! Currency::format($totalOldPrice - $totalPrice) !!}</span>
                                    @else
                                        <strong class="price">{!! Currency::format($totalPrice) !!}</strong>
                                    @endif
                                </div>
                                <div class="js-without-user-sale-price" style="display: none">
                                    @if ($totalPriceWithoutUserSale < $totalOldPrice)
                                        <span class="old_price text-muted">{!! Currency::format($totalOldPrice) !!}</span>
                                        <strong class="price">{!! Currency::format($totalPriceWithoutUserSale) !!}</strong><br>
                                        <span class="new_price">Вы экономите {!! Currency::format($totalOldPrice - $totalPriceWithoutUserSale) !!}</span>
                                    @else
                                        <strong class="price">{!! Currency::format($totalPriceWithoutUserSale) !!}</strong>
                                    @endif
                                </div>
                                {{-- !! end temporary shitcode for price !! --}}

                            </div>
                        </div>
                        <div class="row justify-content-between mb-2">
                            <div class="col-auto">
                                ДОСТАВКА
                            </div>
                            <div class="col-auto">
                                {!! Currency::format(0) !!}
                            </div>
                        </div>
                        <div class="row justify-content-between mb-2 border-top border-secondary">
                            <div class="col-auto">
                                К оплате
                            </div>
                            <div class="col-auto">

                                {{-- !! temporary shitcode for price !! --}}
                                <div class="js-normal-price">
                                    {!! Currency::format($totalPrice) !!}
                                </div>
                                <div class="js-without-user-sale-price" style="display: none">
                                    {!! Currency::format($totalPriceWithoutUserSale) !!}
                                </div>
                                {{-- !! end temporary shitcode for price !! --}}

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    @csrf
                    <div class="col-12 mb-4" id="user-data-validate">
                        <h5>ЗАПОЛНИТЕ ДАННЫЕ ДОСТАВКИ</h5>
                    </div>

                    <div class="col-12 col-md-4 form-group">
                        <label for="country_id">Страна</label>
                        <select name="country_id" id="country_id"
                            class="form-control @error('country_id') is-invalid @enderror">
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected($country->id == $currentCountry->id)>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 form-group">
                        <label for="city">Город</label>
                        <input id="city" type="text" name="city"
                            class="form-control @error('city') is-invalid @enderror"
                            value="{{ old('city', $user->getFirstAddress()?->city) }}">
                        @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 form-group">
                        <label for="user_addr">Адрес</label>
                        <input id="user_addr" type="text" name="user_addr"
                            class="form-control @error('user_addr') is-invalid @enderror"
                            value="{{ old('user_addr', $user->getFirstAddress()?->address) }}">
                        @error('user_addr')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4 form-group">
                        <label for="first_name">Имя</label>
                        <input id="first_name" type="text" name="first_name" autocomplete="given-name"
                            class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 form-group">
                        <label for="last_name">Фамилия</label>
                        <input id="last_name" type="text" name="last_name"
                            autocomplete="additional-name"
                            class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 form-group">
                        <label for="patronymic_name">Отчество</label>
                        <input id="patronymic_name" type="text" name="patronymic_name"
                            autocomplete="family-name"
                            class="form-control @error('patronymic_name') is-invalid @enderror"
                            value="{{ old('patronymic_name', $user->patronymic_name) }}" required>
                        @error('patronymic_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 form-group">
                        <label for="phone">Телефон</label>
                        {{-- blade-formatter-disable-next-line --}}
                        @include('partials.inputs.phone', compact('countries', 'currentCountry'))
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label for="email">E-mail</label>
                        <input id="email" type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 form-group">
                        <label for="comment">Комментарий к заказу</label>
                        <textarea id="comment" rows="4" name="comment"
                            class="form-control @error('comment') is-invalid @enderror">
                            {{ old('comment') }}
                        </textarea>
                        @error('comment')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                </div>
                <div class="row mt-3 my-md-5 justify-content-center">
                    <button type="submit" form="cartData"
                        class="btn btn-dark col-12 col-sm-6 col-md-4 col-lg-3 py-2">
                        Подтвердить заказ
                    </button>
                </div>
            </form>
        @else
            <div class="row my-5">
                <h2>В корзине нет товаров</h2>
            </div>
        @endif

    </div>

@endsection
