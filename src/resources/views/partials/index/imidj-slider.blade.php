@if ($imidjSlider['title'])
    <h4 class="text-center">{{ $imidjSlider['title'] }}</h4>
@endif

<div class="js-product-carousel row" data-slick='{
    "autoplay": true,
    "autoplaySpeed": {{ $imidjSlider['speed'] }},
    "arrows": false,
    "rows": 2,
    "slidesPerRow": 4,
    "fade": true,
    "cssEase": "ease-out",
    "responsive": [
        {
            "breakpoint": 1400,
            "settings": {
                "slidesPerRow": 3
            }
        },
        {
            "breakpoint": 576,
            "settings": {
                "slidesPerRow": 1
            }
        }
    ]
}'>
    @foreach ($imidjSlider['products'] as $product)
        <div
            class="col mb-main js-product-item"
            data-gtm-product='{!! $product['dataLayer']->toJson() !!}'
        >
            <div class="position-relative">
                @include('partials.buttons.favorite', [
                    'favoriteProductId' => $product['id'],
                    'favoriteState' => $product['favorite']
                ])
                @if ($product['sale_percentage'])
                    <span class="position-absolute text-white font-size-14 px-2" style="top: 0; right: 0px; background: #D22020;">
                        -{{ $product['sale_percentage'] }}%
                    </span>
                @endif
                <a href="{{ $product['url'] }}" data-gtm-click="productClick">
                    <img
                        src="{{ $product['imidj_media'] }}"
                        alt="{{ $product['title'] }}"
                        class="img-fluid product-first-image"
                    >
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
        </div>
    @endforeach
</div>
