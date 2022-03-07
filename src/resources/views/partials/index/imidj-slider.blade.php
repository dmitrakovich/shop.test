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
        <div class="col mb-main">
            @include('shop.slider-product', compact('product'))
        </div>
    @endforeach
</div>
