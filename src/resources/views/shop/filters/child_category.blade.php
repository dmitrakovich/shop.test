<li class="pb-2 {{ isset($currentFilters['App\Models\Category'][$childCategory['slug']]) ? 'font-weight-bold' : null }}">
    <a href="{{ UrlHelper::generate([$childCategory]) }}">{{ $childCategory['title'] }}</a>
</li>
@if (!empty($childCategory['children_categories']))
    <ul class="pl-3 text-lowercase">
        @foreach ($childCategory['children_categories'] as $childCategory)
            @include('shop.filters.child_category', $childCategory)
        @endforeach
    </ul>
@endif
