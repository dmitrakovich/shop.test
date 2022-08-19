@include('shop.filters.categories')
<hr>

@include('shop.filters.checkbox', ['filterName' => 'statuses', 'filterTitle' => 'СТАТУСЫ'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'fabrics', 'filterTitle' => 'МАТЕРИАЛ'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'collections', 'filterTitle' => 'КОЛЛЕКЦИЯ'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'sizes', 'filterTitle' => 'РАЗМЕРЫ'])
<hr>

@include('shop.filters.colors')
<hr>

@include('shop.filters.checkbox', ['filterName' => 'heels', 'filterTitle' => 'ВЫСОТА КАБЛУКА'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'seasons', 'filterTitle' => 'СЕЗОН'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'styles', 'filterTitle' => 'СТИЛЬ'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'tags', 'filterTitle' => 'ТЕГИ'])
<hr>

@include('shop.filters.checkbox', ['filterName' => 'brands', 'filterTitle' => 'БРЕНДЫ'])
<br>

<a href="{{ route('shop') }}" class="btn btn-dark btn-block" style="margin-bottom: 60px">
    Очистить фильтр
</a>
