<div class="filter-block colors">
    <div class="title"><span>ЦВЕТА</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['colors'] as $filter)
                <a href="{{ \App\Models\Url::generate($filter['slug']) }}">
                    <li>
                        <label class="check">
                            {{-- <input type="checkbox"> --}}
                            <i class="checkmark" style="background: {{ $filter['value'] }}"></i>
                        </label>
                    </li>
                </a>
            @endforeach
        </ul>
    </div>
</div>