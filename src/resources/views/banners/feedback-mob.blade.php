@if (isset($feedbackBannerMob))
  <div class="col-12 d-md-none my-3">
        <a href="{{ $feedbackBannerMob->url }}">
            <img src="{{ $feedbackBannerMob->getFirstMediaUrl() }}"
                alt="{{ $feedbackBannerMob->title }}"
                title="{{ $feedbackBannerMob->title }}"
                class="img-fluid"
            />
        </a>
    </div>
@endif