@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
<div class="col-12 px-0 px-md-main py-main">
    <img src="/images/banners/main2x.png" alt="" class="img-fluid">
</div>
<div class="col-12 col-md-4 p-main">
    <img src="/images/banners/main2x1.png" alt="" class="img-fluid">
</div>
<div class="col-12 col-md-4 p-main">
    <img src="/images/banners/main2x1.png" alt="" class="img-fluid">
</div>
<div class="col-12 col-md-4 p-main">
    <img src="/images/banners/main2x1.png" alt="" class="img-fluid">
</div>


<div class="col-12 text-center mt-5">
    <h1 class="display-4">Популярные категории</h1>
    
</div>


<div class="col-md-12">


    {{-- <div class="card">
        <div class="card-header">Dashboard</div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            You are logged in!
        </div>
    </div> --}}

    <div class="col-12">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
    </div>
    <div class="col-12"><hr></div>
    <div class="col-12 test-roboto">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
    </div>
    <div class="col-12"><hr></div>
    <div class="col-12 test-popins">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
    </div>
    <div class="col-12"><hr></div>
    <div class="col-12 test-gilroy">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
    </div>
    <div class="col-12"><hr></div>
    <div class="col-12 test-gill-sans-mt">
        Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
    </div>

    <div class="col-12">
        <hr class="d-none d-sm-block">
        @include('includes.advantages-block')
        <hr class="d-none d-sm-block">
    </div>

    <div class="col-12 mt-5">
        <div class="row align-items-center">
            <div class="col-12 col-sm-auto text-center">
                <h1 class="display-4">#BAROCCO look</h1>
            </div>
            <div class="col-12 col-sm-auto text-center ml-auto">
                <a href="{{ config('contacts.instagram.link') }}">Подпишись на наш Instagram</a>
            </div>
        </div>
        <div class="row mx-n2 js-instagram-posts"></div>
        <div class="row mt-4 mb-5">
            <div class="col text-center">
                <a href="{{ config('contacts.instagram.link') }}">Больше образов</a>
            </div>
        </div>
    </div>


</div>


{{-- wrapper close --}}
</div>
<div class="row">
    <div class="col-12 bg-danger py-5">
        <div class="row wrapper">
            <div class="col-12 col-md-6 text-center text-md-left">
                <h1 class="display-4">BAROCCO club</h1>
                <p class="font-size-18">Зарегистрируйся в программе лояльности и получи приветственный бонус</p>
            </div>
            <div class="col-12 col-md-6 mt-4 mt-md-0">
                <div class="row justify-content-center align-items-center h-100">
                    <button class="btn btn-white col-10 col-lg-8 col-xl-6 p-2">Присоединиться</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row wrapper justify-content-center">
{{-- wrapper open --}}


<div class="col-12 text-justify my-5">
    В нашем каталоге – обувь и аксессуары в рассрочку с доставкой по Минску и Беларуси. Уже не первый год мы привозим в Беларусь продукцию VITACCI.
    Если вы знакомы не понаслышке с дизайнерской обувью, то хорошо помните то ощущение легкости, невесомости и комфорта, что она дарит. VITACCI – это настоящие аристократы среди обуви. Их высокое происхождение угадывается в каждой детали: это все лучшее, что воплощает в себе итальянский стиль, - небрежная роскошь, лаконичный дизайн, надежность и удобство в использовании. Ценители признаются: обувь VITACCI хочется коллекционировать. Ведь хорошей обуви, как известно, много не бывает!<br>
    Женская обувь VITACCI – это туфли, босоножки и балетки, ботильоны и сапоги для самых избалованных и стройных женских ножек. Замша и кожа рептилии, впечатляющие шпильки и небольшие устойчивые каблучки, круглые пряжки и заостренные мысы, лакировка и перфорация, - в таких туфлях хочется не ходить, а летать!<br>
    Мужская обувь VITACCI – это воплощение солидности и внушительности, как для классического делового стиля, так и для расслабленного casual. Какую бы пару обуви вы ни выбрали, она красноречиво расскажет о вашем статусе, высоком доходе и хорошем настроении. Потому что у человека в итальянской дизайнерской обуви просто не может быть плохого настроения!<br>
    Женские сумки от VITACCI – это выстрел, бьющий прямо в цель: они настолько заметные, эффектные и удобные, что становятся не просто ярким акцентом, а настоящей изюминкой вашего образа.<br>
    Приобщитесь к итальянскому стилю прямо сейчас вместе с Модны Бай! Подарите себе аристократическую элегантность и молчаливую роскошь брендовой кожаной обуви и аксессуаров. Специально для вас – БЕСПЛАТНАЯ доставка по Беларуси с примеркой! Выбирайте и заказывайте, а остальное – наша работа. И поверьте, мы сделаем все, чтобы вы остались довольны!
</div>
@endsection
