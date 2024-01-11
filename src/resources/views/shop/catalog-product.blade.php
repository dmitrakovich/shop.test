<?php /** @var \App\Models\Product $product */ ?>

<div class="col-3 js-product-item product-item text-lg-left mb-3 text-center"
    data-gtm-product='{!! $product->dataLayer->toJson() !!}'>
    <div class="image position-relative">
        <a class="position-relative product-item-link js-productItemImages mb-3"
            href="{{ $product->getUrl() }}" data-gtm-click="productClick">
            <div class="product-item-link-container">
                @include('partials.buttons.favorite', [
                    'favoriteProductId' => $product->id,
                    'favoriteState' => isset($product->favorite),
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
                    @if (!empty($product->getSale('general_sale')))
                        <div class="product-label product-label-sale">
                            акция
                        </div>
                    @endif
                </div>
                <div class="product-item-images js-productItemImagesContainer">
                    @foreach ($product->getMedia()->take(5) as $key => $image)
                        <div style="@if ($key > 0) display: none; @endif">
                            <img src="{{ $image->getUrl('catalog') }}" alt="{{ $product->shortName() }}"
                                onerror="imageOnError(this)" loading="lazy" decoding="async" />
                        </div>
                    @endforeach
                </div>
                <button type="button" aria-label="быстрый просмотр"
                    data-src="{{ route('product.quick', $product->id) }}"
                    class="quick-link btn btn-block btn-outline-dark d-none d-lg-block">
                    быстрый просмотр
                </button>
            </div>
        </a>
    </div>

    <b>{{ $product->shortName() }}</b> <br>
    @if ($product->getPrice() < $product->getOldPrice())
        <span class="old_price">{!! $product->getFormattedOldPrice() !!}</span>
        <span class="new_price">{!! $product->getFormattedPrice() !!}</span>
    @else
        <span class="price">{!! $product->getFormattedPrice() !!}</span>
    @endif
    <br />
    <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
</div>
