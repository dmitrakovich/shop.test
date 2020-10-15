@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('category', $currentCategory))

@section('content')
    <div class="col-3 col-xl-2 d-none d-lg-block filters-sidebar">
        @include('shop.filters.all')
    </div>
    <div class="col-12 col-lg-9 col-xl-10 catalog-page">

        {{ Banner::getCatalogTop() }}

        <div class="col-12 scrolling-pagination">
            <div class="row jscroll-inner justify-content-between">
                @forelse($products as $product)
                    @php /** @var App\Product $product */ @endphp
                    <div class="col-12 col-md-auto js-product-item product-item mb-3 px-3">
                        <a href="{{ $product->category->getUrl() . '/' . $product->slug }}">
                            <p>
                                <img src="/images/products/{{ $product->images->first()['img'] }}" alt="{{ $product->title }}"
                                    class="img-fluid">
                            </p>
                        </a>
                        <b>{{ $product->getFullName() }}</b> <br>
                        @if ($product->product_price < $product->product_old_price)
                            <s>{{ round($product->product_old_price, 2) }} руб.</s>
                            <font color="#D22020">{{ round($product->product_price, 2) }} руб.</font><br>
                        @else
                            {{ round($product->product_price, 2) }} руб.<br>
                        @endif
                        {{-- <p><b>Категория: </b>{{ $product->category->title }}</p> --}}
                        {{-- <p><b>Размеры: </b>{{ $product->sizes->implode('name', ',') }}</p> --}}
                        <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
                        
                        {{-- <p><b>Цвет: </b>{{ $product->color->name ?? '' }}</p> --}}
                        {{-- <p><b>Материал: </b>{{ $product->fabrics->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Каблук: </b>{{ $product->heels->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Стиль: </b>{{ $product->styles->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Сезон: </b>{{ $product->season->name }}</p> --}}
                        {{-- <p><b>Теги: </b>{{ $product->tags->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Бренд: </b>{{ $product->brand->name }}</p> --}}
                    </div>
                @empty
                    <p>Нет товаров</p>
                @endforelse
                {{ $products->links() }}
            </div>
        </div>

    </div>

@endsection
