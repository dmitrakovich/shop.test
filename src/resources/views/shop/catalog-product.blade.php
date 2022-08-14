<?php /** @var \App\Models\Product $product */ ?>

<div
    class="col-3 js-product-item product-item mb-3 text-center text-lg-left"
    data-gtm-product='{!! $product->dataLayer->toJson() !!}'
>
    <div class="mb-3 image position-relative">
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

        <a href="{{ $product->getUrl() }}" data-gtm-click="productClick">
            <img
                src="{{ $product->getFirstMedia()->getUrl('catalog') }}"
                alt="{{ $product->extendedName() }}"
                class="img-fluid product-first-image"
                onerror="imageOnError(this)"
            />
            <img
                src="{{ ($product->getMedia()->get(1) ?? $product->getFirstMedia())->getUrl('catalog') }}"
                alt="{{ $product->extendedName() }}"
                class="img-fluid product-second-image"
                onerror="imageOnError(this)"
            />
        </a>
        <button
            type="button"
            aria-label="быстрый просмотр"
            data-src="{{ route('product.quick', $product->id) }}"
            class="quick-link btn btn-block btn-outline-dark d-none d-lg-block"
        >быстрый просмотр</button>
    </div>

    <b>{{ $product->simpleName() }}</b> <br>
    @if ($product->getPrice() < $product->getOldPrice())
        <span class="old_price">{!! $product->getFormattedOldPrice() !!}</span>
        <span class="new_price">{!! $product->getFormattedPrice() !!}</span>
    @else
        <span class="price">{!! $product->getFormattedPrice() !!}</span>
    @endif
    <br/>
    <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
</div>
