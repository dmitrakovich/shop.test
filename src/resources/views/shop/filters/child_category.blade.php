<li class="pb-2 {{ isset($currentFilters['App\Models\Category'][$childCategory->slug]) ? 'font-weight-bold' : null }}">
    <a href="{{ $childCategory->getUrl() }}">{{ $childCategory->title }}</a>
</li>
@if ($childCategory->childrenCategories)
    <ul class="pl-3 text-lowercase">
        @foreach ($childCategory->childrenCategories as $childCategory)
            @include('shop.filters.child_category', $childCategory)
        @endforeach
    </ul>
@endif
