@if (isset($mobCatalogBanner))
  <div class="col-12 d-md-none my-3">
    <a href="{{ $mobCatalogBanner->url }}">
      <img src="{{ $mobCatalogBanner->getFirstMediaUrl() }}"
        alt="{{ $mobCatalogBanner->title }}"
        title="{{ $mobCatalogBanner->title }}"
        class="img-fluid"
      />
    </a>
    @include('banners.banner-timer', ['banner' => $mobCatalogBanner])
  </div>
@endif