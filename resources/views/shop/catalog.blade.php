@extends('layouts.app')

@section('title', 'Женская обувь')

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('shop.filters.all')
    </div>
    <div class="col-12 col-lg-9 catalog-page">
        {{ Breadcrumbs::render('index') }}
        {{ Breadcrumbs::render('static-payment') }}

        <div class="col-12 scrolling-pagination">
            <div class="row jscroll-inner">
                @forelse($products as $product)
                    @php /** @var App\Product $product */ @endphp
                    <div class="col-3 border js-product-item">
                        <a href="{{ $product->category->getUrl() . '/' . $product->slug }}">
                            <p>
                                <img src="/images/products/{{ $product->images->first()['img'] }}" alt="{{ $product->title }}"
                                    class="img-fluid">
                            </p>
                        </a>
                        <p>
                            {{ $product->brand->name }} {{ $product['name_ru-RU'] }}
                        </p>
                        <p><b>Категория: </b>{{ $product->category->title }}</p>
                        <p><b>Размеры: </b>{{ $product->sizes->implode('name', ',') }}</p>
                        {{-- <p><b>Цвет: </b>{{ $product->color->name ?? '' }}</p> --}}
                        <p><b>Материал: </b>{{ $product->fabrics->implode('name', ',') }}</p>
                        {{-- <p><b>Каблук: </b>{{ $product->heels->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Стиль: </b>{{ $product->styles->implode('name', ',') }}</p> --}}
                        {{-- <p><b>Сезон: </b>{{ $product->season->name }}</p> --}}
                        {{-- <p><b>Теги: </b>{{ $product->tags->implode('name', ',') }}</p> --}}
                        <p><b>Бренд: </b>{{ $product->brand->name }}</p>
                    </div>
                @empty
                    <p>Нет товаров</p>
                @endforelse
                {{ $products->links() }}
            </div>
        </div>

    </div>

@endsection
