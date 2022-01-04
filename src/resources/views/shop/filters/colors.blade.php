<div class="filter-block colors">
    <div class="title"><span>ЦВЕТА</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['colors'] as $slug => $filter)
                <li>
                    <a href="{{ isset($currentFilters[$filter['model']][$slug]) ? UrlHelper::generate([], [$filter]) : UrlHelper::generate([$filter]) }}">
                        <label class="check {{ isset($currentFilters[$filter['model']][$slug]) ? 'checked' : null }} p-1">
                            <i class="checkmark" title="{{ $filter['name'] }}" style="background: {{ $filter['value'] }}"></i>
                        </label>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
