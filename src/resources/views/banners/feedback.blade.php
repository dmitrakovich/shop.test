@if (isset($feedbackBanner))
    <div class="col-12 d-none d-lg-block">
        <a href="{{ $feedbackBanner->url }}">
            <img src="{{ $feedbackBanner->getFirstMediaUrl() }}"
                alt="{{ $feedbackBanner->title }}"
                title="{{ $feedbackBanner->title }}"
                class="img-fluid"
            />
        </a>
    </div>
@endif
