@if (isset($mobCatalogBanner))
    <div class="col-12 d-md-none my-3">
        <a href="{{ $mobCatalogBanner->url }}">
            @if ($mobCatalogBanner->getMedia()->first()->hasCustomProperty('videos'))
                <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                    poster="{{ $mobCatalogBanner->getFirstMediaUrl() }}">

                    @foreach ($mobCatalogBanner->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                        <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                    @endforeach
                </video>
            @else
                <img src="{{ $mobCatalogBanner->getFirstMediaUrl() }}" alt="{{ $mobCatalogBanner->title }}"
                    title="{{ $mobCatalogBanner->title }}" class="img-fluid" />
            @endif
        </a>
        @include('banners.banner-timer', ['banner' => $mobCatalogBanner])
    </div>
@endif
