<div class="row align-items-center mb-3">
    <div class="col-12 col-md-auto text-center">
        <h1 class="display-4">
            Наш инстаграм
            <a href="{{ config('contacts.instagram.link') }}">@barocco.by</a>
        </h1>
    </div>
    <div class="col-12 col-md-auto text-center ml-auto">
        <a class="btn btn-dark" href="{{ config('contacts.instagram.link') }}">Подпишись</a>
    </div>
</div>
<div class="row mx-n2 js-instagram-posts">
    @foreach ($instagramPosts as $key => $post)
        <div class="col-6 col-lg-4 py-main" id="{{ $post['id'] }}">
            <a href="{{ $post['permalink'] }}" rel="noopener" target="_blank">
                <img src="{{ $post['thumbnail_url'] ?? $post['media_url'] }}" title="{{ $post['caption'] }}" class="img-fluid" />
                {{-- @include('svg.play-button', ['class' => 'position-absolute', 'style' => 'top: 0, left: 0;']) --}}
            </a>
        </div>
    @endforeach
</div>

<div class="row mt-4 mb-5">
    <div class="col text-center">
        <a class="text-decoration-underline" target="_blank" href="{{ config('contacts.instagram.link') }}">
            Больше образов
        </a>
    </div>
</div>
