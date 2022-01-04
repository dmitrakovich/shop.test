<div class="filter-block colors">
    <div class="title"><span>ЦВЕТА</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['colors'] as $slug => $filter)
                <li class="check {{ isset($currentFilters[$filter['model']][$slug]) ? 'checked' : null }}">
                    <a href="{{ isset($currentFilters[$filter['model']][$slug]) ? UrlHelper::generate([], [$filter]) : UrlHelper::generate([$filter]) }}">
                        <i class="checkmark" title="{{ $filter['name'] }}" style="background: {{ $filter['value'] }}"></i>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
