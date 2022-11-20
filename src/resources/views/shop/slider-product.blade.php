<div class="position-relative js-product-item" data-gtm-product='{!! $product['dataLayer']->toJson() !!}'>
    @include('partials.buttons.favorite', [
        'favoriteProductId' => $product['id'],
        'favoriteState' => $product['favorite'],
    ])
    <div class="product-labels">
        @if ($product['is_new'])
            <div class="product-label product-label-new">
                new
            </div>
        @endif
        @if ($product['sale_percentage'])
            <div class="product-label product-label-sale">
                -{{ $product['sale_percentage'] }}%
            </div>
        @endif
    </div>
    <a href="{{ $product['url'] }}" data-gtm-click="productClick">
        <img src="{{ $product['image'] }}" alt="{{ $product['sku'] }}" class="img-fluid product-first-image"
            onerror="imageOnError(this)">
        <div>{{ $product['full_name'] }}<br>
            @if ($product['sale_percentage'])
                <span class="old_price">{!! $product['formatted_old_price'] !!}</span>
                <span class="new_price">{!! $product['formatted_price'] !!}</span>
            @else
                <span class="price">{!! $product['formatted_price'] !!}</span>
            @endif
        </div>
    </a>
</div>
