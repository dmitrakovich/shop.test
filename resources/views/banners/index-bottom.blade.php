@foreach ($indexBottomBanners->chunk(2) as $bannerColumn)
    <div class="col-12 col-md-6">
        <div class="row">
            @foreach ($bannerColumn as $banner)
                <div class="col-12 p-main">
                    <a href="{{ $banner->url }}">
                        <img src="{{ $banner->getFirstMediaUrl() }}"
                            alt="{{ $banner->title }}"
                            title="{{ $banner->title }}"
                            class="img-fluid w-100"
                        />
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
