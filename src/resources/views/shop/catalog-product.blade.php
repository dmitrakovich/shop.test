<div class="col-3 js-product-item product-item mb-3 text-center text-lg-left">
    <a href="{{ $product->getUrl() }}">
        <div class="mb-3 image position-relative">
            @if ($product->getSalePercentage())
                <span class="position-absolute text-white font-size-14 px-2" style="top: 0; right: 0; background: #D22020;">
                    -{{ $product->getSalePercentage() }}%
                </span>
            @endif
            <img
                src="{{ $product->getFirstMedia()->getUrl('catalog') }}"
                alt="{{ $product->title }}"
                class="img-fluid product-first-image"
            >
            <img
                src="{{ ($product->getMedia()->get(1) ?? $product->getFirstMedia())->getUrl('catalog') }}"
                alt="{{ $product->title }}"
                class="img-fluid product-second-image"
            >
                <div class="quick-link d-none d-lg-block">
                    <a
                        data-src="{{ route('product.quick', $product->id) }}"
                        href="{{ $product->getUrl() }}"
                        class="btn btn-outline-dark">быстрый просмотр
                    </a>
                </div>
        </div>
    </a>
    <b>{{ $product->getFullName() }}</b> <br>
    @if ($product->getPrice() < $product->getOldPrice())
        <s>{!! $product->getFormattedOldPrice() !!}</s>
        <font color="#D22020">{!! $product->getFormattedPrice() !!}</font><br>
    @else
        {!! $product->getFormattedPrice() !!}<br>
    @endif
    <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
</div>
