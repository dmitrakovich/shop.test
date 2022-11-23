@if (isset($catalogBanner))
    <div class="col-12 d-none d-lg-block">
        <a href="{{ $catalogBanner->url }}">
            @if ($catalogBanner->getMedia()->first()->hasCustomProperty('videos'))
                <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                    poster="{{ $catalogBanner->getFirstMediaUrl() }}">

                    @foreach ($catalogBanner->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                        <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                    @endforeach
                </video>
            @else
                <img src="{{ $catalogBanner->getFirstMediaUrl() }}" alt="{{ $catalogBanner->title }}"
                    title="{{ $catalogBanner->title }}" class="img-fluid" />
            @endif
        </a>
        @include('banners.banner-timer', ['banner' => $catalogBanner])
    </div>
@endif
