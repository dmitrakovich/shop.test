<div class="inc-instagram row wrapper">
    <div class="inc-instagram__info col-12 col-lg-5">
        <h2 class="inc-instagram__info-title">Подписывайтесь на наш инстаграм</h2>
        <a class="inc-instagram__btn d-none d-lg-inline-flex" target="_blank"
            href="{{ config('contacts.instagram.link') }}">
            Подписаться
        </a>
    </div>
    <div class="col-12 col-lg-7">
        <div class="inc-instagram__posts">
            @foreach ($instagramPosts as $key => $post)
                <a class="inc-instagram__posts-item" href="{{ $post['permalink'] }}" id="{{ $post['id'] }}"
                    rel="noopener" target="_blank">
                    <img src="{{ $post['thumbnail_url'] ?? $post['media_url'] }}" title="{{ $post['caption'] }}"
                        class="img-fluid" />
                </a>
            @endforeach
        </div>
    </div>
    <div class="col-12 d-block d-lg-none mt-4 text-center">
        <a class="inc-instagram__btn" target="_blank" href="{{ config('contacts.instagram.link') }}">
            Подписаться
        </a>
    </div>
</div>
