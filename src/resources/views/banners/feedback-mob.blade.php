@if (isset($feedbackBannerMob))
    <div class="col-12 d-md-none my-3">
        <a href="{{ $feedbackBannerMob->url }}">
            @if ($feedbackBannerMob->getMedia()->first()->hasCustomProperty('videos'))
                <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                    poster="{{ $feedbackBannerMob->getFirstMediaUrl() }}">

                    @foreach ($feedbackBannerMob->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                        <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                    @endforeach
                </video>
            @else
                <img src="{{ $feedbackBannerMob->getFirstMediaUrl() }}" alt="{{ $feedbackBannerMob->title }}"
                    title="{{ $feedbackBannerMob->title }}" class="img-fluid" />
            @endif
        </a>
    </div>
@endif
