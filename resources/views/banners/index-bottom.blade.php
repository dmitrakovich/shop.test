<div class="col-12 col-md-6">
    <div class="row">
        <div class="col-12 p-main">
            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 340px; background-color: #E8E8E8;">
                <div class="text-center">
                    <h1 class="display-4">Демисезон</h1>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur
                    </p>
                    <a href="{{ route('shop', 'demi') }}" class="btn btn-dark px-5">купить</a>
                </div>
            </div>
        </div>
        <div class="col-12 p-main">
            <a href="{{ route('shop', 'demi') }}">
                <img src="/images/banners/{{ $banners['left'] }}" alt="" class="img-fluid w-100">
            </a>
        </div>
    </div>
</div>
<div class="col-12 col-md-6">
    <div class="row">
        <div class="col-12 p-main">
            <a href="{{ route('shop', 'winter') }}">
                <img src="/images/banners/{{ $banners['right'] }}" alt="" class="img-fluid w-100">
            </a>
        </div>
        <div class="col-12 p-main">
            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 340px; background-color: #E8E8E8;">
                <div class="text-center">
                    <h1 class="display-4">Зима</h1>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur
                    </p>
                    <a href="{{ route('shop', 'winter') }}" class="btn btn-dark px-5">купить</a>
                </div>
            </div>
        </div>
    </div>
</div>
