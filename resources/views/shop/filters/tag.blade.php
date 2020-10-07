<div class="filter-block tags">
    <div class="title"><span>ТЕГИ</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['tags'] as $filter)
                <a href="{{ \App\Url::generate($filter['slug']) }}">
                    <li>
                        <label class="check">
                            <span>{{ $filter['name'] }}</span>
                            {{-- <input type="checkbox"> --}}
                            <i class="checkmark"></i>
                        </label>
                    </li>
                </a>
            @endforeach
        </ul>
    </div>
</div>