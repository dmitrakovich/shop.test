
<div class="col-12 product-page">
    <div class="row">
        <div class="col-12 col-md-6 col-xl-7">

            <div class="slider-for">
                @foreach ($product->getMedia() as $image)
                    <a href="{{ $image->getUrl('full') }}" data-fancybox="images">
                        <img src="{{ $image->getUrl('normal') }}" class="img-fluid">
                    </a>
                @endforeach
            </div>
            <div class="slider-nav mb-3 row" style="max-width: 720px">
                @foreach ($product->getMedia() as $image)
                    <div class="col-auto">
                        <img src="{{ $image->getUrl('thumb') }}" class="img-fluid">
                    </div>
                @endforeach
            </div>

        </div>
        <div class="col-12 col-md-6 col-xl-5">
            <div class="col-12">
                <div class="row">
                    <div class="col-6 text-muted">
                        {{ $product->getFullName() }}
                    </div>
                    <div class="col-6 text-right rating-result">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="star {{-- $frating >= $i ? 'active' : '' --}}"></span>
                        @endfor

                        <span class="ml-2 align-text-bottom">(0)</span>
                    </div>
                </div>
            </div>

            <form class="col-12 col-xl-10" id="product-info" action=" {{ route('cart-add') }}" method="post">
                <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
                <div class="row mt-4">
                    <div class="col-12 col-lg-6 price-block">
                        <div class="row">
                            {{-- @if ($product->price < $product->old_price)
                                <div class="col-auto price price-old">
                                    <s>{{ $product->old_price }}</s>
                                </div>
                                <div class="col-auto price price-new">
                                    {{ $product->price }}
                                </div>
                                @else
                                <div class="col-auto price price-new">
                                    {{ $product->price }}
                                </div>
                            @endif --}}

                            @if ($product->price < $product->old_price)
                                <div class="col-auto price price-old">
                                    {{ $product->old_price }}
                                </div>
                            @endif
                            <div class="col-auto price price-new">
                                {{ $product->price }} руб.
                            </div>
                        </div>

                    </div>
                    <div class="col-12 col-lg-6 text-right">
                        Условия рассрочки
                        <div class="tooltip-trigger ml-2">?</div>
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-12 py-3 py-xl-4 text-center" style="background: #EEF6FC;">
                        -30% на вторую и -50% на третью пару до конца апреля
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 py-3">
                        Цвет / {{ $product->color->name }}
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 product-size">
                        <div class="row justify-content-between">
                            <div class="col-auto">
                                <span class="text-muted">Выберите размер:</span>
                            </div>
                            <div class="col-auto">
                                <a class="text-decoration-underline" href="#">Таблица размеров</a>
                            </div>
                        </div>
                        <ul class="p-0 mt-3 js-sizes">
                            @foreach ($product->sizes as $size)
                                <li class="d-inline-block pr-3">
                                    <label class="check">
                                        <input type="checkbox" class="d-none" name="sizes[{{ $size->id }}]">
                                        <span class="checkmark">{{ $size->name }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12">
                        <button type="button" class="btn btn-dark btn-lg btn-block py-3 js-add-to-cart">
                            В КОРЗИНУ
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-lg btn-block py-3 js-buy-one-click">
                            КУПИТЬ В ОДИН КЛИК
                        </button>
                    </div>
                </div>

                <div class="col-12 text-center text-muted mt-5">
                    <p>
                        <img src="/images/icons/installments.svg" class="pr-2">
                        Без переплат в рассрочку
                    </p>
                    <p>
                        <img src="/images/icons/delivery.svg" class="pr-2">
                        Примерка по Беларуси
                    </p>
                    <p>
                        <img src="/images/icons/return.svg" class="pr-2">
                        Возврат 14 дней
                    </p>
                </div>
            </form>

        </div>
    </div>

    <div class="row my-5 product-description">
        <div class="col-12 font-size-15 mb-1">
            ОПИСАНИЕ
        </div>
        <div class="col-12 col-lg-7">
            {!! $product->description !!}
        </div>
        <div class="col-12 col-lg-4 offset-lg-1">
            @if (!empty($product->color->name))
                Цвет - {{ $product->color->name }} <br>
            @endif

            @if (!empty($product->season))
                Сезон - {{ $product->season->name }} <br>
            @endif

            @if (!empty($product->fabric_top_txt))
                Материал верха - {{ $product->fabric_top_txt }} <br>
            @endif

            @if (!empty($product->fabric_inner_txt))
                Внутренний материал - {{ $product->fabric_inner_txt }} <br>
            @endif

            @if (!empty($product->fabric_insole_txt))
                Материал стельки - {{ $product->fabric_insole_txt }} <br>
            @endif

            @if (!empty($product->fabric_outsole_txt))
                Материал подошвы - {{ $product->fabric_outsole_txt }} <br>
            @endif

            @if (!empty($product->heel_txt))
                Высота каблука - {{ $product->heel_txt }} <br>
            @endif

            @if (!empty($product->title))
                Артикул - {{ $product->title }} <br>
            @endif

        </div>
    </div>

</div>

{{-- modals --}}
<div style="display: none;" id="buy-one-click" class="row">

    <div class="col-12 text-center">
        <h3 class="mx-5 mb-4">Купить в один клик</h3>
        <div class="form-group">
            <input type="text" class="form-control" name="name" placeholder="Имя" required>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" name="phone" placeholder="Телефон" required>
        </div>
        <button type="button" class="btn btn-dark my-3 px-5" id="buy-one-click-submit">
            Купить
        </button>
        <p class="text-muted font-size-12">
            После заказа менеджер перезвонит Вам и уточнит <br>
            адрес доставки
        </p>
    </div>
</div>

<div style="display: none;" id="product-no-size" class="p-5">
    <h3>Не выбран размер</h3>
</div>
