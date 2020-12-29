@extends('layouts.app')

@section('title', 'Отзывы')

@section('breadcrumbs', Breadcrumbs::render('feedbacks'))

@section('content')

<div class="col-12">

    <div class="row justify-content-between mb-3">
        <div class="col-auto align-self-center d-none d-md-block">
            <h2>ОЦЕНКИ И ОТЗЫВЫ</h2>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-danger btn-block py-3">
                ОСТАВИТЬ ОТЗЫВ О СВОЕЙ ПОКУПКЕ
            </button>
        </div>
    </div>

    <div class="row justify-content-between px-5">
        @foreach ($feedbacks as $feedback)
            <div class="col-12 item my-4">
                <div class="row">
                    <div class="col-3">
                        {{-- @if ($feedback->rating > 0)
                            <ul>
                                @for ($i = 1; $i <= 5; $i++)
                                    <li>
                                        <label class="check">
                                            <input type="checkbox" {{ $feedback->rating >= $i ? 'checked' : '' }}>
                                            <i class="checkmark icon ic-star"></i>
                                        </label>
                                    </li>
                                @endfor
                            </ul>
                        @endif --}}
                        <b>{{ $feedback->user_name }}</b>
                    </div>
                    <div class="col-7">
                        <p>{{ $feedback->text }}</p>
                        @foreach ($feedback->getMedia() as $image)
                            <a href="{{ $image->getUrl('full') }}" data-fancybox="images">
                                <img src="{{ $image->getUrl('thumb') }}" class="img-fluid">
                            </a>
                        @endforeach
                    </div>
                    <div class="col-2 text-center">
                        <span>{{ $feedback->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
                {{-- {{ dump($feedback) }} --}}
            </div>
        @endforeach

    </div>

    <div class="row justify-content-center justify-content-md-end mb-5">
        <div class="col-md-auto">
            {{ $feedbacks->links() }}
        </div>
    </div>

</div>

@endsection
