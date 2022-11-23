@if (!empty($mainMenuCatalog))
    <a href="{{ $mainMenuCatalog->url }}">
        @if ($mainMenuCatalog->getMedia()->first()->hasCustomProperty('videos'))
            <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline poster="{{ $mainMenuCatalog->getFirstMediaUrl() }}">

                @foreach ($mainMenuCatalog->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                    <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                @endforeach
            </video>
        @else
            <img src="{{ $mainMenuCatalog->getFirstMediaUrl() }}" alt="{{ $mainMenuCatalog->title }}" title="{{ $mainMenuCatalog->title }}" class="img-fluid" />
        @endif
    </a>
@endif
