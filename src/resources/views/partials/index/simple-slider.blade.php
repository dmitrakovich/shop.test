<h4 class="text-center">{{ $simpleSlider['title'] }}</h4>
<div class="js-product-carousel" data-slick='{
    "slidesToShow": 5,
    "slidesToScroll": 5,
    "autoplay": true,
    "autoplaySpeed": {{ $simpleSlider['speed'] }},
    "responsive": [
        {
            "breakpoint": 1305,
            "settings": {
                "slidesToShow": 4,
                "slidesToScroll": 4
            }
        },
        {
            "breakpoint": 830,
            "settings": {
                "slidesToShow": 1,
                "slidesToScroll": 1
            }
        }
    ]
}'>
    @foreach ($simpleSlider['products'] as $product)
    <div class="col position-relative">
        <a href="{{ $product['url'] }}">
            @if ($product['sale_percentage'])
                <span class="position-absolute text-white font-size-14 px-2" style="top: 0; right: 10px; background: #D22020;">
                    -{{ $product['sale_percentage'] }}%
                </span>
            @endif
            <img
                src="{{ $product['first_media'] }}"
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
