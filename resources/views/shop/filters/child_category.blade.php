<li>
    <a href="{{ route('catalog', $childCategory) }}">{{ $childCategory->title }}</a>
</li>
@if ($childCategory->childrenCategories)
    <ul>
        @foreach ($childCategory->childrenCategories as $childCategory)
            @include('shop.filters.child_category', $childCategory)
        @endforeach
    </ul>
@endif