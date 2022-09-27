<?php /** @var \App\Models\Product $product */ ?>

<div class="col-12 product-page">
    <div class="row">
        <div class="col-12 col-md-6 col-xl-7">
            <div class="position-relative">
                @include('partials.buttons.favorite', [
                    'favoriteProductId' => $product->id,
                    'favoriteState' => isset($product->favorite)
                ])

                <div class="product-labels">
                    @if ($product->isNew())
                        <div class="product-label product-label-new">
                            new
                        </div>
                    @endif
                    @if ($product->getSalePercentage())
                        <div class="product-label product-label-sale">
                            -{{ $product->getSalePercentage() }}%
                        </div>
                    @endif
                </div>

                <div class="slider-for">
                    @foreach ($product->getMedia() as $image)
                        @if ($image->hasCustomProperty('video'))
                            <div>
                                <iframe src="{{ UrlHelper::getEmbedVideoUrl($image->getCustomProperty('video')) }}"
                                    class="w-100"
                                    style="min-height: 55vh"
                                    allowfullscreen
                                    frameborder="0">
                                </iframe>
                            </div>
                        @else
                            <a href="{{ $image->getUrl('full') }}" data-fancybox="images">
                                <img
                                    src="{{ $image->getUrl('normal') }}"
                                    alt="{{ $product->shortName() }}"
                                    class="img-fluid"
                                    onerror="imageOnError(this)"
                                >
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="slider-nav mb-3 row" style="max-width: 720px">
                    @foreach ($product->getMedia() as $key => $image)
                        <div class="col-auto">
                            <div class="position-relative d-inline-block">
                                <img
                                    src="{{ $image->getUrl('thumb') }}"
                                    alt="{{ $product->shortName() }} миниатюра {{ ++$key }}"
                                    class="img-fluid"
                                    onerror="imageOnError(this)"
                                >
                                @if ($image->hasCustomProperty('video'))
                                    <span class="youtube-play-icon"></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-5">
            <div class="col-12">
                <div class="row">
                    <div class="col-6 text-muted">
                        {{ $product->shortName() }}
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
                            @if ($product->getPrice() < $product->getOldPrice())
                                <div class="col-auto price price-old">
                                    {!! $product->getFormattedOldPrice() !!}
                                </div>
                            @endif
                            <div class="col-auto price price-new">
                                {!! $product->getFormattedPrice() !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 text-right">
                        <button type="button" class="btn installment-help-block" data-toggle="dropdown">
                            <span class="border-bottom border-secondary">Условия рассрочки</span>
                            <div class="tooltip-trigger ml-2">?</div>
                        </button>

                        <div class="dropdown-menu p-3">
                            <p class="font-size-15">
                                <b>РАССРОЧКА НА 2 ПЛАТЕЖА</b>
                            </p>
                            <p>
                                Первый взнос<br>
                                <b>{{ $product->getPrice() - $product->getPrice() * 0.6 }} руб.</b><br>
                                Оставшиеся 2 платежа по<br>
                                <b class="border-bottom border-danger font-size-14">
                                    {{ $product->getPrice() * 0.3 }} руб. в месяц
                                </b>
                            </p>
                            &#9989; Без увеличения цены <br>
                            &#9989; Без справки о доходах
                        </div>
                    </div>
                </div>

                <div class="row my-4">
                    @if (!empty($product->sale['label']))
                        <div class="col-12 py-3 py-xl-4 text-center">
                            <div class="row">
                                <div class="col-6 text-left">
                                    @if($product->getDiscountPercentage())
                                        <span class="text-danger fw-bold">
                                            скидка <span style="font-size: 20px;">{{ $product->getDiscountPercentage() }}% </span>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-6 bg-danger py-1">
                                <span style="font-size: 20px;">+  {{ $product->getOnlySalePercentage() }}% </span> по акции                                </div>
                            </div>
                            <div class="row py-3 align-items-center bg-danger">
                                <div class="col-12 mb-2">
                                    <div class="flex-fill">{{ $product->sale['label'] }}</div>
                                </div>
                                @if(!empty($product->sale['end_datetime']))
                                    <div class="col-12 text-danger">
                                        @include('includes.timer', ['end_time' => $product->sale['end_datetime'], 'badgeCountdown' => true ])
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row mb-4">
                    <div class="col-12 product-size">
                        <div class="row justify-content-between">
                            <div class="col-auto">
                                <span class="text-muted">Выберите размер:</span>
                            </div>
                            <div class="col-auto">
                                <a class="text-decoration-underline" data-fancybox data-src="#size-table" href="javascript:;">
                                    Таблица размеров
                                </a>
                            </div>
                        </div>
                        <ul class="p-0 mt-3 js-sizes">
                            @foreach ($product->sizes as $size)
                                <li class="d-inline-block pr-3">
                                    <label for="input-size-{{ $size->id }}" class="check">
                                        <span class="checkmark">{{ $size->name }}</span>
                                    </label>
                                    <input
                                        type="checkbox"
                                        id="input-size-{{ $size->id }}"
                                        class="visually-hidden"
                                        name="sizes[{{ $size->id }}]"
                                    />
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
                        <img src="/images/icons/installments.svg" alt="" role="presentation" class="pr-2">
                        Без переплат в рассрочку
                    </p>
                    <p>
                        <img src="/images/icons/delivery.svg" alt="" role="presentation" class="pr-2">
                        Примерка по Беларуси
                    </p>
                    <p>
                        <img src="/images/icons/return.svg" alt="" role="presentation" class="pr-2">
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
            @if(!empty($product->tags))
                <div class="font-size-15 mb-1">
                   ТЕГИ
                </div>
                <div>
                    @foreach($product->tags as $tag)
                        <a
                            href="{{ (isset($product->category->path) ? ('/' . $product->category->path) : route('shop')) . '/' . $tag->slug }}"
                            class="bg-dark text-white py-0 px-2 m-1 d-inline-flex alight-items-center"
                            title="{{ ($product->category->name ?? '') . ' ' . ($tag->seo ?? $tag->name) }}"
                        >{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 offset-lg-1">
            <div class="font-size-15 mb-1">
                ХАРАКТЕРИСТИКИ
            </div>
            @if (!empty($product->brand->name))
                Бренд - {{ $product->brand->name }} <br>
            @endif

            @if (!empty($product->color_txt))
                Цвет - {{ $product->color_txt }} <br>
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

            @if (!empty($product->bootleg_height_txt))
                Высота голенища - {{ $product->bootleg_height_txt }} <br>
            @endif

            @if (!empty($product->heel_txt))
                Высота каблука - {{ $product->heel_txt }} <br>
            @endif

            @if (!empty($product->sku))
                Артикул - {{ $product->sku }} <br>
            @endif

        </div>
    </div>
    @if(!empty($similarProducts) && count($similarProducts))
        <div class="col-md-12 mt-3 mb-5">
            @include('partials.index.simple-slider', ['simpleSlider' => $similarProducts])
        </div>
    @endif
    @if(!empty($recentProductsSlider['products']) && count($recentProductsSlider['products']))
        <div class="col-md-12 my-3">
            @include('partials.index.simple-slider', ['simpleSlider' => $recentProductsSlider])
        </div>
    @endif
</div>

{{-- modals --}}
<div style="display: none;" id="buy-one-click" class="row">

    <form action="{{ route('orders.store') }}" method="post" class="col-12 text-center" id="oneclick-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <h3 class="mx-5 mb-4">Купить в один клик</h3>
        <div class="form-group">
            <input type="text" class="form-control" name="first_name" placeholder="Имя" autocomplete="given-name" required>
        </div>
        <div class="form-group">
            @include('partials.inputs.phone')
        </div>
        <div class="form-group">
            <input type="text" class="form-control" name="user_addr" placeholder="Населенный пункт" autocomplete="address" required>
        </div>
        <button type="button" class="btn btn-dark my-3 px-5" id="buy-one-click-submit">
            Купить
        </button>
        <p class="text-muted font-size-12">
            После заказа менеджер перезвонит Вам и уточнит <br>
            адрес доставки
        </p>
    </form>
</div>


<div style="display: none;" id="size-table" class="row">

    <div class="col-12 text-center">
        <table><tbody>
            <tr><th>Размер</th><th>Длина стельки, см</th></tr>
            <tr><td>33</td><td>21</td></tr>
            <tr><td>34</td><td>21,5</td></tr>
            <tr><td>35</td><td>22,5</td></tr>
            <tr><td>36</td><td>23</td></tr>
            <tr><td>37</td><td>23,5</td></tr>
            <tr><td>38</td><td>24,5</td></tr>
            <tr><td>39</td><td>25</td></tr>
            <tr><td>40</td><td>25,5</td></tr>
            <tr><td>41</td><td>26,5</td></tr>
            <tr><td>42</td><td>27</td></tr>
            <tr><td>43</td><td>27,5</td></tr>
        </tbody></table>
    </div>
</div>

<script>
    var productDetail = {!! $dataLayer->toJson() !!};
</script>
