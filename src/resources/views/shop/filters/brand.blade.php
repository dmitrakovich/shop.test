<div class="filter-block brand">
    <div class="title"><span>БРЕНДЫ</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['brands'] as $slug => $filter)
                <li class="check @checked(isset($currentFilters[$filter['model']][$slug]))">
                    <a href="{{ isset($currentFilters[$filter['model']][$slug]) ? UrlHelper::generate([], [$filter]) : UrlHelper::generate([$filter]) }}">
                        <span>{{ $filter['name'] }}</span>
                        <i class="checkmark"></i>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
