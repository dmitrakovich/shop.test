@extends('layouts.app')

@section('title', "Купить {$categoryTitle} с примеркой по Беларуси")

@section('breadcrumbs', Breadcrumbs::render('category', $category))

@section('content')
    <div class="row d-flex d-lg-none mb-2">
        <div class="col-6 align-self-center">
            <button class="btn btn-outline-dark rounded" type="button" data-toggle="collapse" data-target="#sidebarFilters">
                Фильтр
            </button>
            <span class="text-muted font-size-12">
                {{ DeclensionNoun::make($products->total(), 'модель') }}
            </span>
        </div>
        <select onchange="window.location.href = this.value" class="form-control col-6">
            @foreach ($sortingList as $key => $value)
                <option value="{{ URL::current() . "?sort=$key" }}" {{ $sort == $key ? 'selected' : null }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="collapse col-12 col-lg-3 col-xl-2 d-lg-block filters-sidebar" id="sidebarFilters">
        @include('shop.filters.all')
    </div>
    <div class="col-12 col-lg-9 col-xl-10 catalog-page">

        {{ Banner::getCatalogTop() }}

        <div class="col-12 my-4 d-none d-lg-block">
            <div class="row justify-content-end ">
                <div class="col-auto align-self-center mr-3">
                    Сортировка:
                </div>
                <select onchange="window.location.href = this.value" class="form-control col-6 col-md-4 col-lg-3 col-xl-2">
                    @foreach ($sortingList as $key => $value)
                        <option value="{{ URL::current() . "?sort=$key" }}" {{ $sort == $key ? 'selected' : null }}>
                            {{ $value }}
                        </option>
                    @endforeach
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
