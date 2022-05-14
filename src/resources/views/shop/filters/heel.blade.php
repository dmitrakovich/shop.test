<div class="filter-block sizes">
    <div class="title"><span>ВЫСОТА КАБЛУКА</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['heels'] as $slug => $filter)
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
