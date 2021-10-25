@foreach ($linksBlocks as $linksBlock)
    <div class="my-3" id="index-link-{{ $linksBlock->id }}">
        <h4 class="display-4 text-center mb-4">{{ $linksBlock->title }}</h4>
        <ul class="nav flex-column flex-sm-row justify-content-center text-center">
            @foreach ($linksBlock->links as $link)
                <li class="nav-item">
                    <a href="{{ $link['href'] ?? '' }}">{{ $link['text'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
