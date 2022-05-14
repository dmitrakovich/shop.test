@extends('layouts.app')

@section('title', 'Корзина')

@section('breadcrumbs', Breadcrumbs::render('cart'))

@section('content')
    <div class="col-10 my-5">
        @if ($cart->items->isNotEmpty())
            <form action="{{ route('orders.store', '#user-data-validate') }}" id="cartData" method="post">
                <div class="row">
                    @foreach ($cart->items as $item)
                        <div class="col-12 py-3 border-bottom border-secondary">
                            <div class="row">
                                <div class="pl-0 col-6 col-md-2 ">
                                    <a href="{{ $item->product->getUrl() }}" target="_blank">
                                        <img src="{{ $item->product->getFirstMedia()->getUrl('catalog') }}"
                                            alt="{{ $item->product->sku }}" class="img-fluid">
                                    </a>
                                </div>
                                <div class="col-6 col-md-10">
                                    <div class="row position-relative h-100">
                                        <div class="col-12 col-md-3">
                                            <a href="{{ $item->product->getUrl() }}" target="_blank">
                                                {{ $item->product->brand->name }} {{ $item->product->id }}
                                            </a><br>
                                            <small>{{ $item->product->category->title }}</small>
                                        </div>
                                        <div class="col-12 col-md-2 mt-md-2">{{ DeclensionNoun::make($item->count, 'пара') }}</div>
                                        <div class="col-12 col-md-2 mt-md-2">размер {{ $item->size->name}}</div>
                                        <div class="col-12 col-md-2 mt-md-2">{{ $item->product->color_txt }}</div>
                                        <div class="col-12 col-md-3 mt-md-2 mb-4">
                                            @if ($item->product->getPrice() < $item->product->getOldPrice())
                                                <span class="old_price text-muted">{!! $item->product->getFormattedOldPrice() !!}</span>&nbsp;
                                                <span class="product-label product-label-sale px-1">
                                                    -{{ $item->product->getSalePercentage() }}%
                                                </span><br>
                                                <span class="new_price">{!! $item->product->getFormattedPrice() !!}</span>
                                            @else
                                                <span class="price">{!! $item->product->getFormattedPrice() !!}</span>
                                            @endif
                                        </div>

                                        <div class="col-12 col-auto mt-auto position-absolute fixed-bottom">
                                            <div class="row">
                                                <div class="col-auto">
                                                    <a href="{{ route('cart-delete', $item->id) }}" class="text-muted text-decoration-underline">
                                                        Удалить из корзины
                                                    </a>
                                                </div>
                                                <div class="col-auto d-none d-md-block">
                                                    <a href="" class="text-muted text-decoration-underline">В избранное</a>
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
                        @foreach ($deliveriesList as $key => $value)
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="radio"
                                    name="delivery_id"
                                    id="delivery-{{ $key }}"
                                    value="{{ $key }}" {{ $loop->first ? 'checked' : null }}
                                />
                                <label class="form-check-label" for="delivery-{{ $key }}">{{ $value }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-12 col-md-6 mt-3 mt-md-5">
                        <p class="font-size-18"><b>Способ оплаты:</b></p>
                        @foreach ($paymentsList as $key => $value)
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="radio"
                                    name="payment_id"
                                    id="payment-{{ $key }}"
                                    value="{{ $key }}" {{ $loop->first ? 'checked' : null }}
                                />
                                <label class="form-check-label" for="payment-{{ $key }}">
                                    {{ $value }}
                                    @if ($value == 'Оформить рассрочку')
                                        <br>
                                        <span class="text-muted font-size-12">
                                            (Рассрочка на 2 платежа
                                            Первый взнос
                                            <span class="border-bottom border-secondary">{{ Cart::getTotalPrice() - Cart::getTotalPrice() * 0.5 }} руб.</span>
                                            Оставшийся платеж
                                            <span class="border-bottom border-secondary">{{ Cart::getTotalPrice() * 0.5 }} руб.</span>
                                            в месяц)
                                    </span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>


                <div class="row my-5">
                    <div class="col-12 col-md-6">
                        <div class="row">
                            {{-- <div class="col-auto">
                                <input type="text" class="form-control" value="#ПРОМОКОД">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-secondary px-5">Применить</button>
                            </div> --}}
                        </div>
                    </div>
                    <div class="col-12 offset-md-1 col-md-5">
                        <div class="row justify-content-between mb-2">
                            <div class="col-auto">
                                СТОИМОСТЬ ЗАКАЗА
                            </div>
                            <div class="col-auto text-right">
                                @if (Cart::getTotalPrice() < Cart::getTotalOldPrice())
                                    <span class="old_price text-muted">{!! Currency::format(Cart::getTotalOldPrice()) !!}</span>
                                    <strong class="price">{!! Currency::format(Cart::getTotalPrice()) !!}</strong><br>
                                    <span class="new_price">Вы экономите {!! Currency::format(Cart::getTotalOldPrice() - Cart::getTotalPrice()) !!}</span>
                                @else
                                    <strong class="price">{!! Currency::format(Cart::getTotalPrice()) !!}</strong>
                                @endif
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
                                {!! Currency::format(Cart::getTotalPrice()) !!}
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
                            value="{{ old('city', $user->getFirstAddress()->city) }}">
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
                            value="{{ old('user_addr', $user->getFirstAddress()->address) }}">
                        @error('user_addr')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4 form-group">
                        <label for="first_name">Имя</label>
                        <input id="first_name" type="text" name="first_name"
                            autocomplete="given-name"
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
                            class="form-control @error('comment') is-invalid @enderror"
                        >{{ old('comment') }}</textarea>
                        @error('comment')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                </div>
                <div class="row mt-3 my-md-5 justify-content-center">
                    <button type="submit" form="cartData" class="btn btn-dark col-12 col-sm-6 col-md-4 col-lg-3 py-2">
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
