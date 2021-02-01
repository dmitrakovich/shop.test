<ul class="text-uppercase filter-block categories pl-0">
    @foreach ($filters['categories'] as $category)
        <li>
            <b><a href="{{ $category->getUrl() }}">{{ $category->title }}</a></b>
        </li>
        <ul class="pl-0">
            @foreach ($category->childrenCategories as $childCategory)
                @include('shop.filters.child_category', $childCategory)
            @endforeach
        </ul>
    @endforeach
</ul>
