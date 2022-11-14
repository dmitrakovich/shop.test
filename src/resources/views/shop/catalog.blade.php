@extends('layouts.app')

@section('title', Seo::getCatalogTitle($currentFilters))
@if ($products->isNotEmpty())
    @section('ogImage', $products->first()->getFirstMedia()->getUrl('catalog'))
@endif
@section('description', Seo::getCatalogDescription($currentFilters))
@section('metaForRobots', Seo::metaForRobotsForCatalog($currentFilters))

@section('breadcrumbs', Breadcrumbs::render('category', $category))

@section('content')
    <div class="col-12">
        <div class="row d-flex d-lg-none mb-2">
            <div class="col-6 align-self-center">
                <button class="btn btn-outline-dark rounded" type="button" data-toggle="collapse" data-target="#sidebarFilters">
                    Фильтр
                </button>
                <span class="text-muted font-size-12">
                    {{ DeclensionNoun::make($products->totalCount, 'модель') }}
                </span>
            </div>
            <div class="col-6">
                <select onchange="window.location.href = this.value" class="form-control">
                    @foreach ($sortingList as $key => $value)
                        <option value="{{ URL::current() . "?sort=$key" }}" @selected($sort == $key)>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="collapse col-12 col-lg-3 col-xl-2 d-lg-block filters-sidebar" id="sidebarFilters">
        @include('shop.filters.all')
    </div>
    <div class="col-12 col-lg-9 col-xl-10 catalog-page">
        {{ Banner::getCatalogTop() }}
        {{ Banner::getCatalogMob() }}
        @if(!empty($badges))
          <div class="d-flex flex-wrap my-3">
            @foreach($badges as $badge)
              <a href="{{ $badge->url ?? '/catalog' }}" class="border py-2 px-4 m-1 d-inline-flex alight-items-center">{{ $badge->name }} <span class="ml-1">@include('svg.close')</span></a>
            @endforeach
          </div>
        @endif
        <div class="col-12 my-4 d-none d-lg-block">
            <div class="row justify-content-end align-items-center">

                {{ Currency::getSwitcher() }}

                <label for="select-sorting" class="mb-0 ml-3 mr-2">
                    Сортировка:
                </label>
                <div class="m-0 col-md-4 col-lg-3 col-xl-2">
                    <select onchange="window.location.href = this.value" id="select-sorting" class="form-control">
                        @foreach ($sortingList as $key => $value)
                            <option value="{{ URL::current() . "?sort=$key" }}" @selected($sort == $key)>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-12 scrolling-pagination px-0">
            <input type="hidden" name="cursor" value="{{{ optional($products->nextCursor())->encode() }}}">
            <input type="hidden" name="has_more" value="@json($products->hasMorePages())">
            <input type="hidden" name="gtm_category_name" value="{{ $category->getNameWithParents() }}">
            <input type="hidden" name="gtm_search_query" value="{{ $searchQuery }}">
                @if($products->isNotEmpty())
                    <div class="col-12">
                        <div class="row justify-content-start" id="catalog-endless-scroll">
                            @foreach($products as $product)
                                @include('shop.catalog-product', compact('product'))
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="px-2 px-md-5 my-4 my-md-5">
                        <h1 class="text-center mb-4">ТОВАРЫ НЕ НАЙДЕНЫ</h1>
                        <p class="text-center">
                            К сожалению, нет товаров, соответствующих запросу, но...<br>
                            Вы можете вернутся назад, воспользоваться меню или выбрать из популярных товаров
                        </p>
                        <div class="text-center my-5">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-dark mx-2 mb-2 py-1 px-3" style="min-width: 134px;">
                                Назад
                            </a>
                            <a href="{{ route('shop') }}" class="btn btn-dark mx-2 mb-2 py-1 px-3" style="min-width: 134px;">
                                В каталог
                            </a>
                        </div>
                        <div class="col-md-12 my-4">
                            @includeWhen(isset($simpleSliders[0]), 'partials.index.simple-slider', ['simpleSlider' => ($simpleSliders[0] ?? null)])
                        </div>
                    </div>
                @endif
                {{-- {{ $products->links() }} --}}
        </div>

    </div>

    <button type="button" class="btn btn-secondary scroll-top-btn" aria-label="Back to top">
        <svg width="21" height="12" viewBox="0 0 21 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.4065 11.7137L10.2637 4.57087L3.12081 11.7137L0.263672 10.2852L10.2637 0.285156L20.2637 10.2852L17.4065 11.7137Z" fill="white"/>
        </svg>
    </button>

@endsection
