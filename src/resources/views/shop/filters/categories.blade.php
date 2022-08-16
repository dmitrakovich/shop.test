<ul class="text-uppercase filter-block categories pl-0">
    @foreach ($filters['categories'] as $category)
        <li class="pb-2">
            <b><a href="{{ UrlHelper::generate([$category]) }}">{{ $category->title }}</a></b>
        </li>
        <ul class="pl-0">
            @foreach ($category->childrenCategories as $childCategory)
                @include('shop.filters.child_category', $childCategory)
            @endforeach
        </ul>
    @endforeach
</ul>
