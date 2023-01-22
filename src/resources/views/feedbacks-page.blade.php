@extends('layouts.app')

@section('title', 'Отзывы')

@section('breadcrumbs', Breadcrumbs::render('feedbacks'))

@section('content')
    <div class="col-12">

        <div class="row justify-content-between">
            <div class="col-auto align-self-center d-none d-md-block">
                <h2>ОЦЕНКИ И ОТЗЫВЫ</h2>
            </div>
            <div class="col-12 col-md-auto">
                <button type="button" class="btn btn-danger btn-block py-3" data-fancybox
                    data-src="#leave-feedback-modal">
                    ОСТАВИТЬ ОТЗЫВ О СВОЕЙ ПОКУПКЕ
                </button>
            </div>
        </div>
        <div class="row my-4">
            {{ Banner::getFeedback() }}
            {{ Banner::getFeedbackMob() }}
        </div>

        @include('includes.feedbacks')
    </div>
@endsection
