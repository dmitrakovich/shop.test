@extends('layouts.app')

@section('title', 'Корзина')

@section('breadcrumbs', Breadcrumbs::render('cart'))

@section('content')
    <div class="col-10 my-5">
        @if ($cart->items->isNotEmpty())
            <div class="row">
                @foreach ($cart->items as $item)
                    <div class="col-12 py-3 border-bottom border-secondary">
                        <div class="row">
                            <div class="pl-0 col-6 col-md-2 ">
                                <img src="{{ $item->product->getFirstMedia()->getUrl('catalog') }}"
                                    alt="{{ $item->product->title }}" class="img-fluid">
                            </div>
                            <div class="col-6 col-md-10">
                                <div class="row position-relative h-100">
                                    <div class="col-12 col-md-3">
                                        {{ $item->product->getFullName() }} <br>
                                    <small>{{ $item->product->category->title }}</small>
                                    </div>
                                    <div class="col-12 col-md-2 mt-2">{{ DeclensionNoun::make($item->count, 'пара') }}</div>
                                    <div class="col-12 col-md-2 mt-2">размер {{ $item->size->name}}</div>
                                    <div class="col-12 col-md-2 mt-2">{{ $item->product->color->name }}</div>
                                    <div class="col-12 col-md-3 mt-2">{{ $item->product->price }} BYN</div>

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
                        <div class="col-auto">
                            {{ Cart::getTotalPrice() }} BYN
                        </div>
                    </div>
                    <div class="row justify-content-between mb-2">
                        <div class="col-auto">
                            ДОСТАВКА
                        </div>
                        <div class="col-auto">
                            0,00 BYN
                        </div>
                    </div>
                    <div class="row justify-content-between mb-2 border-top border-secondary">
                        <div class="col-auto">
                            К оплате
                        </div>
                        <div class="col-auto">
                            {{-- {{ Cart::getTotal() }} BYN --}}
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('cart-submit') }}" id="cartData" method="post" class="row my-5">
                @csrf
                <div class="col-12 mb-4">
                    <h5>ЗАПОЛНИТЕ ДАННЫЕ ДОСТАВКИ</h5>
                </div>
                <div class="col-12 col-md-4 form-group">
                    <label for="city">Город</label>
                    <select name="city" id="city"
                        class="form-control @error('city') is-invalid @enderror">
                        <option value="1">Брест</option>
                        <option value="2">Витебск</option>
                        <option value="3">Гомель</option>
                        <option value="4">Гродно</option>
                        <option value="5">Минск</option>
                        <option value="6">Могилев</option>
                    </select>
                    @error('city')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md-8 form-group">
                    <label for="address">Адрес</label>
                    <input id="city" type="text" name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        value="{{ old('address', $user->address) }}">
                    @error('address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md-4 form-group">
                    <label for="fio">ФИО</label>
                    <input id="fio" type="text" name="fio"
                        class="form-control @error('fio') is-invalid @enderror"
                        value="{{ old('fio', $user->full_name) }}">
                    @error('fio')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md-4 form-group">
                    <label for="city">Телефон</label>
                    <input id="phone" type="tel" name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $user->phone ?? '+375') }}">
                    @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md-4 form-group">
                    <label for="city">E-mail</label>
                    <input id="email" type="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </form>
            <div class="row mb-5 justify-content-center">
                <button type="submit" form="cartData" class="btn btn-dark col-12 col-sm-6 col-md-4 col-lg-3 py-2">
                    Подтвердить заказ
                </button>
            </div>
        @else
            <div class="row my-5">
                <h2>В корзине нет товаров</h2>
            </div>
        @endif

    </div>

@endsection
