<div class="filter-block brand">
    <div class="title"><span>БРЕНДЫ</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['brands'] as $slug => $filter)
                <a href="{{ isset($currentFilters[$filter['model']][$slug]) ? UrlHelper::generate([], [$filter]) : UrlHelper::generate([$filter]) }}">
                    <li>
                        <label class="check {{ isset($currentFilters[$filter['model']][$slug]) ? 'checked' : null }}">
                            <span>{{ $filter['name'] }}</span>
                            <i class="checkmark"></i>
                        </label>
                    </li>
                </a>
            @endforeach
        </ul>
    </div>
</div>
