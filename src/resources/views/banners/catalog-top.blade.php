@if (isset($catalogBanner))
    <div class="col-12 d-none d-lg-block">
        <a href="{{ $catalogBanner->url }}">
            <img src="{{ $catalogBanner->getFirstMediaUrl() }}" alt="{{ $catalogBanner->title }}"
                title="{{ $catalogBanner->title }}" class="img-fluid" />
        </a>
        @include('banners.banner-timer', ['banner' => $catalogBanner])
    </div>
@endif
