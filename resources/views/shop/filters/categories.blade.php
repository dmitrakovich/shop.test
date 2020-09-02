<ul>
    @foreach ($categoriesTree as $category)
        <li>
            <a href="{{ route('catalog', $category) }}">{{ $category->title }}</a>
        </li>
        <ul>
            @foreach ($category->childrenCategories as $childCategory)
                @include('shop.filters.child_category', $childCategory)
            @endforeach
        </ul>
    @endforeach
</ul>