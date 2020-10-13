<div class="filter-block sizes">
    <div class="title"><span>РАЗМЕРЫ</span></div>
    <div class="list" {{-- style="display: none" --}}>
        <ul>
            @foreach ($filters['sizes'] as $filter)
                <a href="{{ \App\Models\Url::generate($filter['slug']) }}">
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