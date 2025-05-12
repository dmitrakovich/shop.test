@if (isset($indexMainBanner))
    <div class="banners_index">
        <a href="{{ $indexMainBanner->url }}">
            @if ($indexMainBanner->getMedia()->first()->hasCustomProperty('videos'))
                <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                    poster="{{ $indexMainBanner->getFirstMediaUrl() }}">

                    @foreach ($indexMainBanner->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                        <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                    @endforeach
                </video>
            @else
                <img src="{{ $indexMainBanner->getFirstMediaUrl() }}" alt="{{ $indexMainBanner->title }}"
                    title="{{ $indexMainBanner->title }}" class="img-fluid" />
            @endif
            {{-- <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                poster="/videos/201016_vitacci{{ Agent::isMobile() ? '_m' : null }}.jpg">
                <source src="/videos/201016_vitacci.mp4" type="video/mp4" />
                <source src="/videos/201016_vitacci.webm" type="video/webm" />
                <source src="/videos/201016_vitacci.ogv" type="video/ogg" />
            </video> --}}
        </a>
        @include('banners.banner-timer', ['banner' => $indexMainBanner])
    </div>
@endif

<div class="banners_index__min mb-5">
    @foreach ($indexTopBanners as $banner)
        <a href="{{ $banner->url }}">
            <img src="{{ $banner->getFirstMediaUrl() }}" alt="{{ $banner->title }}" title="{{ $banner->title }}"
                class="img-fluid" />
        </a>
    @endforeach
</div>
