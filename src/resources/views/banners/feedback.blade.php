@if (isset($feedbackBanner))
    <div class="col-12 d-none d-lg-block">
        <a href="{{ $feedbackBanner->url }}">
            @if ($feedbackBanner->getMedia()->first()->hasCustomProperty('videos'))
                <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
                    poster="{{ $feedbackBanner->getFirstMediaUrl() }}">

                    @foreach ($feedbackBanner->getMedia()->first()->getCustomProperty('videos') as $type => $video)
                        <source src="/uploads/files/{{ $video }}" type="{{ $type }}" />
                    @endforeach
                </video>
            @else
                <img src="{{ $feedbackBanner->getFirstMediaUrl() }}" alt="{{ $feedbackBanner->title }}"
                    title="{{ $feedbackBanner->title }}" class="img-fluid" />
            @endif
        </a>
    </div>
@endif
