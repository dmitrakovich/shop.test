{{-- <div class="col-12 px-main py-main d-none d-lg-block">
    <a href="{{ route('shop') }}">
        <img src="/images/banners/{{ $banners['main'] }}" alt="" class="img-fluid">
    </a>
</div>
<div class="col-12 px-0 d-block d-lg-none">
    <a href="{{ route('shop') }}">
        <img src="/images/banners/{{ $banners['main_mobile'] }}" alt="" class="img-fluid">
    </a>
</div>
<div class="col-4 p-main d-none d-lg-block">
    <a href="{{ route('shop') }}">
        <img src="/images/banners/{{ $banners[0] }}" alt="" class="img-fluid">
    </a>
</div>
<div class="col-4 p-main d-none d-lg-block">
    <a href="{{ route('shop') }}">
        <img src="/images/banners/{{ $banners[1] }}" alt="" class="img-fluid">
    </a>
</div>
<div class="col-4 p-main d-none d-lg-block">
    <a href="{{ route('shop') }}">
        <img src="/images/banners/{{ $banners[2] }}" alt="" class="img-fluid">
    </a>
</div> --}}

<div class="col-12 px-0">
    <a href="{{ route('shop', 'catalog') }}">
        <video id="videoblock" class="img-fluid" autoplay loop preload="metadata" muted playsinline
            poster="/videos/201016_vitacci{{ Agent::isMobile() ? '_m' : null }}.jpg">
            <source src="/videos/201016_vitacci.mp4" type="video/mp4" />
            <source src="/videos/201016_vitacci.webm" type="video/webm" />
            <source src="/videos/201016_vitacci.ogv" type="video/ogg" />
        </video>
    </a>
</div>
