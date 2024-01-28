<div class="inc_feedbacks row">
    @forelse ($feedbacks as $feedback)
        <div class="col-12 item px-md-5 bg-light mb-3 px-2 py-4">
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="row">
                        <div class="col-12">{{ $feedback->user_name }}</div>
                        @if (!empty($feedback->user_city))
                            <div class="col-12">{{ $feedback->user_city }}</div>
                        @endif
                        @if (empty($product) && !empty($feedback->product))
                            <div class="col-12 mt-2">
                                <div>{{ $feedback->product->shortName() }}</div>
                                <div style="max-width: 150px">
                                    <img src="{{ $feedback->product->getFirstMediaUrl('default', 'catalog') }}"
                                        alt="{{ $feedback->product->sku }}" class="img-fluid">
                                </div>
                                <div class="mt-2">
                                    <a href="{{ $feedback->product->getUrl() }}"
                                        class="btn btn-outline-dark btn-sm px-3">
                                        ПЕРЕЙТИ К ТОВАРУ
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
                <div class="col-12 col-md-7">
                    <div class="inc_feedbacks__rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <span
                                @if ($feedback->rating >= $i) class="active" @endif>@include('svg.star')</span>
                        @endfor
                    </div>
                    <p>{{ $feedback->text }}</p>
                    @foreach ($feedback->getMedia('photos') as $image)
                        <a href="{{ $image->getUrl('full') }}" data-fancybox="images">
                            <img src="{{ $image->getUrl('thumb') }}" class="img-fluid">
                        </a>
                    @endforeach
                    @foreach ($feedback->getMedia('videos') as $video)
                        <a href="{{ $video->getUrl() }}" data-fancybox="video-gallery"
                            class="position-relative d-inline-block">
                            <img src="{{ $video->getUrl('thumb') }}" class="img-fluid">
                            <span class="video-play-button"></span>
                        </a>
                    @endforeach
                </div>
                <div class="col-12 col-md-2 text-right">
                    <span>{{ $feedback->created_at->format('d.m.Y') }}</span>
                </div>
            </div>
        </div>
    @empty
        @if (isset($product))
            Еще никто не оставил отзыв о товаре
        @endif
    @endforelse
</div>

@if ($feedbacks instanceof \Illuminate\Pagination\Paginator)
    <div class="row justify-content-center justify-content-md-end mb-5">
        <div class="col-md-auto">
            {{ $feedbacks->links() }}
        </div>
    </div>
@endif

<div class="inc_feedbacks__form" style="display: none;" id="leave-feedback-modal">
    <form id="leave-feedback-form" action="{{ route('feedbacks.store') }}" method="post">
        @csrf
        <h3 class="mb-4">Оставить отзыв</h3>

        <div class="row form-group inc_feedbacks__form-rating">
            <div class="col-12 col-md-4">
                <b>Оцените товар</b>
            </div>

            <div class="col-12 col-md-8 inc_feedbacks__form-rating_stars">
                <input type="radio" name="rating" value="5" id="5" checked>
                <label for="5">@include('svg.star')</label>
                <input type="radio" name="rating" value="4" id="4">
                <label for="4">@include('svg.star')</label>
                <input type="radio" name="rating" value="3" id="3">
                <label for="3">@include('svg.star')</label>
                <input type="radio" name="rating" value="2" id="2">
                <label for="2">@include('svg.star')</label>
                <input type="radio" name="rating" value="1" id="1">
                <label for="1">@include('svg.star')</label>
            </div>
        </div>
        <input type="hidden" name="product_id" value="{{ $product->id ?? 0 }}">
        <div class="row form-group">
            <label for="textareaText" class="col-12 col-md-4 col-form-label">
                <b>Оставьте комментарий</b>&nbsp;<font color="red">*</font>
            </label>
            <div class="col-12 col-md-8">
                <textarea rows="5" class="form-control" name="text" id="textareaText"
                    placeholder="Что вам понравилось в этом товаре?"></textarea>
            </div>
        </div>

        <div class="row form-group">
            <label for="inputName" class="col-12 col-md-4 col-form-label">
                <b>Представьтесь, пожалуйста</b>&nbsp;<font color="red">*</font>
            </label>
            <div class="col-12 col-md-8">
                <input type="text" name="user_name" id="inputName" class="form-control"
                    value="{{ optional(auth()->user())->first_name }}" autocomplete="given-name"
                    placeholder="Имя" required>
            </div>
        </div>

        <div class="row form-group">
            <label for="inputCity" class="col-12 col-md-4 col-form-label">
                <b>Город</b>&nbsp;<font color="red">*</font>
            </label>
            <div class="col-12 col-md-8">
                <input type="text" name="user_city" id="inputCity" class="form-control"
                    value="{{ optional(auth()->user())->getFirstAddress()?->city }}"
                    autocomplete="address-level2" placeholder="Город" required>
            </div>
        </div>

        <div class="row form-group">
            <label for="inputPhotos" class="col-12 col-md-4 col-form-label">
                <b>Загрузите фотографии</b>
            </label>
            <div class="col-12 col-md-8">
                <input type="file" accept="image/*" name="photos[]" id="inputPhotos"
                    class="form-control-file" multiple>
            </div>
        </div>

        <div class="row form-group">
            <label for="inputVideos" class="col-12 col-md-4 col-form-label">
                <b>Загрузите видео</b>
            </label>
            <div class="col-12 col-md-8">
                <input type="file" accept="video/*" name="videos[]" id="inputVideos"
                    class="form-control-file" multiple>
            </div>
        </div>

        @include('includes.captcha-privacy-policy')

        <div class="row form-group justify-content-end mb-0 mt-4">
            <button type="button" id="leave-feedback-btn" class="btn btn-dark px-4">
                Оставить отзыв
            </button>
        </div>

    </form>
</div>
