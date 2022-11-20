@php
    $applyPriceRoute = str_replace('catalog', 'price-filter', request()->fullUrl());
    $priceFilters = $currentFilters['App\Models\ProductAttributes\Price'] ?? [];

    $priceRangeStep = match (Currency::getCurrentCurrency()->code) {
        'RUB' => 100,
        'KZT' => 1000,
        default => 1,
    };

    $priceFrom = $minPrice = floor($products->minPrice / $priceRangeStep) * $priceRangeStep;
    $priceTo = $maxPrice = ceil($products->maxPrice / $priceRangeStep) * $priceRangeStep;

    foreach ($priceFilters as $key => $priceFilter) {
        if (str_starts_with($key, 'price-from-')) {
            $priceFrom = Currency::convert($priceFilter->filters->price);
        } else {
            $priceTo = Currency::convert($priceFilter->filters->price);
        }
    }
@endphp

<div class="filter-block">
    <div class="title"><span>ЦЕНА</span></div>
    <form action="{{ $applyPriceRoute }}" method="post" id="price-range-form" class="form-group row mt-3">
        @csrf
        <input type="hidden" name="price_min" value="{{ $minPrice }}">
        <input type="hidden" name="price_max" value="{{ $maxPrice }}">
        <div class="col">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="price_from"
                    aria-label="Минимальная цена">
                <div class="input-group-append">
                    <span class="input-group-text">
                        {{ Currency::getCurrentCurrency()->symbol }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="price_to"
                    aria-label="Максимальная цена">
                <div class="input-group-append">
                    <span class="input-group-text">
                        {{ Currency::getCurrentCurrency()->symbol }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="col-12">
                <input type="text" class="col js-range-slider" data-step="{{ $priceRangeStep }}"
                    data-from="{{ $priceFrom }}" data-to="{{ $priceTo }}"
                    data-min="{{ $minPrice }}" data-max="{{ $maxPrice }}" />
            </div>
        </div>
        <div class="col-12 btn-group mt-3 px-0">
            <div class="col">
                <a href="{{ UrlHelper::generate([], [['model' => \App\Models\ProductAttributes\Price::class]]) }} "
                    class="btn btn-block btn-secondary">Сбросить</a>
            </div>
            <div class="col">
                <button type="submit" form="price-range-form"
                    class="btn btn-block btn-dark">Применить</button>
            </div>
        </div>
    </form>
</div>
