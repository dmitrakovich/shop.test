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
    <div class="col">
        @include('shop.slider-product', compact('product'))
    </div>
    @endforeach
</div>
