<?php /** @var $product \App\Models\Product */ ?>

<div class="col-3 js-product-item product-item mb-3 text-center text-lg-left">
   
    <div class="mb-3 image position-relative">
        <a href="{{ $product->getUrl() }}">
            @if ($product->getSalePercentage())
                <span class="position-absolute text-white font-size-14 px-2" style="top: 0; right: 0; background: #D22020;">
                    -{{ $product->getSalePercentage() }}%
                </span>
            @endif
            <img
                src="{{ $product->getFirstMedia()->getUrl('catalog') }}"
                alt="{{ $product->title }}"
                class="img-fluid product-first-image"
            />
            <img
                src="{{ ($product->getMedia()->get(1) ?? $product->getFirstMedia())->getUrl('catalog') }}"
                alt="{{ $product->title }}"
                class="img-fluid product-second-image"
            />
        </a>
        <button
            type="button"
            data-src="{{ route('product.quick', $product->id) }}"
            class="quick-link btn btn-block btn-outline-dark d-none d-lg-block"
        >быстрый просмотр</button>
    </div>
    
    <b>{{ $product->simpleName() }}</b> <br>
    @if ($product->getPrice() < $product->getOldPrice())
        <s>{!! $product->getFormattedOldPrice() !!}</s>
        <font color="#D22020">{!! $product->getFormattedPrice() !!}</font><br>
    @else
        {!! $product->getFormattedPrice() !!}<br>
    @endif
    <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
</div>
