<div class="filter-block fabric">
    <div class="title"><span>КОЛЛЕКЦИЯ</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['collections'] as $slug => $filter)
                <li>
                    <a href="{{ isset($currentFilters[$filter['model']][$slug]) ? UrlHelper::generate([], [$filter]) : UrlHelper::generate([$filter]) }}">
                        <label class="check {{ isset($currentFilters[$filter['model']][$slug]) ? 'checked' : null }}">
                            <span>{{ $filter['name'] }}</span>
                            <i class="checkmark"></i>
                        </label>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
