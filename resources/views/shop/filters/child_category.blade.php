<li>
    <a href="{{ $childCategory->getUrl() }}">{{ $childCategory->title }}</a>
</li>
@if ($childCategory->childrenCategories)
    <ul>
        @foreach ($childCategory->childrenCategories as $childCategory)
            @include('shop.filters.child_category', $childCategory)
        @endforeach
    </ul>
@endif