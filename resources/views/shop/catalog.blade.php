@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('category', $currentCategory))

@section('content')
    <div class="col-3 col-xl-2 d-none d-lg-block filters-sidebar">
        @include('shop.filters.all')
    </div>
    <div class="col-12 col-lg-9 col-xl-10 catalog-page">

        {{ Banner::getCatalogTop() }}

        <div class="col-12 my-4">
            <div class="row justify-content-end">
                <div class="d-block d-lg-none col-6 align-self-center">
                    Фильтр
                    <span class="text-muted">
                        {{ DeclensionNoun::make($products->total(), 'модель') }}
                    </span>
                </div>
                <div class="col-auto align-self-center mr-3 d-none d-md-block">
                    Сортировка:
                </div>
                <select name="sorting" class="form-control col-6 col-md-4 col-lg-3 col-xl-2">
                    <option data-href="{{ URL::current() . '?' . http_build_query(['sort' => 'rating']) }}"
                        {{ $sort == 'rating' ? 'selected' : null }}>
                        по популярности
                    </option>
                    <option data-href="{{ URL::current() . '?' . http_build_query(['sort' => 'newness']) }}"
                        {{ $sort == 'newness' ? 'selected' : null }}>
                        новинки
                    </option>
                    <option data-href="{{ URL::current() . '?' . http_build_query(['sort' => 'price-up']) }}"
                        {{ $sort == 'price-up' ? 'selected' : null }}>
                        по возрастанию цены
                    </option>
                    <option data-href="{{ URL::current() . '?' . http_build_query(['sort' => 'price-down']) }}"
                        {{ $sort == 'price-down' ? 'selected' : null }}>
                        по убыванию цены
                    </option>
                </select>
            </div>
        </div>

        <div class="col-12 scrolling-pagination px-0">
            <div class="row jscroll-inner justify-content-center justify-content-lg-between">
                @forelse($products as $product)
                    <div class="col-3 js-product-item product-item mb-3 text-center text-lg-left">
                        <a href="{{ $product->getUrl() }}">
                            <div class="mb-3 image position-relative">
                                <img src="{{ $product->getFirstMedia()->getUrl('catalog') }}"
                                    alt="{{ $product->title }}" class="img-fluid">
                                    <div class="quick-link">
                                        <a data-src="{{ route('product.quick', $product->id) }}"
                                            href="{{ $product->getUrl() }}" class="btn btn-light border">быстрый просмотр</a>
                                    </div>
                            </div>
                        </a>
                        <b>{{ $product->getFullName() }}</b> <br>
                        @if ($product->price < $product->old_price)
                            <s>{{ round($product->old_price, 2) }} руб.</s>
                            <font color="#D22020">{{ round($product->price, 2) }} руб.</font><br>
                        @else
                            {{ round($product->price, 2) }} руб.<br>
                        @endif
                        <span class="text-mutted">{{ $product->sizes->implode('name', ' | ') }}</span>
                    </div>
                @empty
                    <p>Нет товаров</p>
                @endforelse
                {{ $products->links() }}
            </div>
        </div>

    </div>

@endsection
