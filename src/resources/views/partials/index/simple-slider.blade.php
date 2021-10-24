@forelse ($simpleSliders as $simpleSlider)

    @if ($loop->index == 1 || ($loop->last && $loop->index <= 1))
        <hr class="d-none d-sm-block my-4">
        @include('includes.advantages-block')
        <hr class="d-none d-sm-block my-4">
    @endif

    <h4 class="text-center mt-3">{{ $simpleSlider['title'] }}</h4>
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
                <div>{{ $product['full_name'] }}</div>
            </a>
        </div>
        @endforeach
    </div>
@empty
    <hr class="d-none d-sm-block my-4">
    @include('includes.advantages-block')
    <hr class="d-none d-sm-block my-4">
@endforelse
