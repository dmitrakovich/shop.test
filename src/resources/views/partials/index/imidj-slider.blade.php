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
        <div class="col position-relative mb-main">
            <a href="{{ $product['url'] }}">
                @if ($product['sale_percentage'])
                    <span class="position-absolute text-white font-size-14 px-2" style="top: 0; right: 10px; background: #D22020;">
                        -{{ $product['sale_percentage'] }}%
                    </span>
                @endif
                <img
                    src="{{ $product['imidj_media'] }}"
                    alt="{{ $product['title'] }}"
                    class="img-fluid product-first-image"
                >
                <div>{{ $product['full_name'] }}<br>
                    @if ($product['sale_percentage'])
                        <s>{!! $product['formatted_old_price'] !!}</s>
                        <font color="#D22020">{!! $product['formatted_price'] !!}</font><br>
                    @else
                        {!! $product['formatted_price'] !!}<br>
                    @endif
                </div>
            </a>
        </div>
    @endforeach
</div>
