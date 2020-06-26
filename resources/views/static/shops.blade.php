@extends('layouts.app')

@section('title', 'Магазины')

@section('breadcrumbs', Breadcrumbs::render('static-shops'))

@section('content')
<div class="col-12 static-page">
    <div class="row mt-4">
        <div class="col-md-8">
            {{-- !!! --}}
            <img src="images/temp/map_temp.png" alt="" class="img-fluid">
        </div>
        <div class="col-md-4">
            <div class="col-12 border-bottom mb-2">
                <p>
                    ул. Советская 72<br>
                    Брест
                </p>
                <p>1,3 км. от вас</p>
                <p>10.00 - 21.00 ежедневно</p>
            </div>
            <div class="col-12 border-bottom mb-2">
                <p>
                    ТЦ Моcква<br>
                    Брест
                </p>
                <p>1,3 км. от вас</p>
                <p>10.00 - 21.00 ежедневно</p>
            </div>
            <div class="col-12 border-bottom mb-2">
                <p>
                    ул. Гоголя 63<br>
                    Брест
                </p>
                <p>1,3 км. от вас</p>
                <p>10.00 - 21.00 ежедневно</p>
            </div>
        </div>
    </div>
    <h1 class="display-4 text-center mt-5" style="font-size: 26px;">Почему Вам стоит заказать на barocco.by</h1>
    <div class="col-12 mt-4 mb-5">
        @include('includes.advantages-block')
    </div>
</div>
@endsection