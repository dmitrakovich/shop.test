<ul>
    @foreach ($categoriesTree as $category)
        <li>{{ $category->title }}</li>
        <ul>
            @foreach ($category->childrenCategories as $childCategory)
                @include('shop.filters.child_category', $childCategory)
            @endforeach
        </ul>
    @endforeach
</ul>