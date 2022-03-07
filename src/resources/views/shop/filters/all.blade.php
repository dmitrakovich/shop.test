@include('shop.filters.categories')
<hr>

@include('shop.filters.status')
<hr>

@include('shop.filters.fabric')
<hr>

@include('shop.filters.collection')
<hr>

@include('shop.filters.sizes')
<hr>

@include('shop.filters.colors')
<hr>

@include('shop.filters.heel')
<hr>

@include('shop.filters.season')
<hr>

@include('shop.filters.tag')
<hr>

@include('shop.filters.brand')
<br>

<a href="{{ route('shop') }}" class="btn btn-dark btn-block" style="margin-bottom: 60px">
    Очистить фильтр
</a>
