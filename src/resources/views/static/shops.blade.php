@extends('layouts.app')

@php SeoFacade::setTitle('Магазины') @endphp

@section('breadcrumbs', Breadcrumbs::render('static-shops'))

@section('content')
    <div class="col-12 static-page">
        <div class="row mt-4">
            <div class="col-md-8 mb-5">
                <script type="text/javascript" charset="utf-8" async
                    src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ab642935229b2f4e923884c82a4de9c7c5f983779f4b37c73c1229af726f79d2f&amp;width=100%25&amp;height=580&amp;lang=ru_RU&amp;scroll=true">
                </script>
            </div>
            <div class="col-md-4 mb-5">

                <div class="col-12 border-bottom mb-2">
                    <p>
                        <b>BAROCCO</b>
                        <a href="tel:+375292465824" class="float-right text-decoration-underline">
                            <b>Позвонить</b>
                        </a><br>
                        г. Брест, ул. Советская 49
                    </p>
                    <p>10.00 - 21.00 ежедневно</p>
                    <p>
                        <a data-fancybox data-src="#shop-photos-1" href="javascript:;"
                            class="text-primary cursor-pointer">
                            смотреть фото
                        </a>
                    </p>
                </div>

                <div class="col-12 border-bottom mb-2">
                    <p>
                        <b>BAROCCO</b>
                        <a href="tel:+375298357797" class="float-right text-decoration-underline">
                            <b>Позвонить</b>
                        </a><br>
                        г. Брест, пр. Машерова, 17Б, ТЦ Москва
                    </p>
                    <p>10.30 - 18.00 ежедневно</p>
                    <p>
                        <a data-fancybox data-src="#shop-photos-2" href="javascript:;"
                            class="text-primary cursor-pointer">
                            смотреть фото
                        </a>
                    </p>
                </div>

                <div class="col-12 border-bottom mb-2">
                    <p>
                        <b>VITACCI</b>
                        <a href="tel:+375292465824" class="float-right text-decoration-underline">
                            <b>Позвонить</b>
                        </a><br>
                        г. Брест, ул. Советская 72
                    </p>
                    <p>10.00 - 21.00 ежедневно</p>
                    <p>
                        <a data-fancybox data-src="#shop-photos-3" href="javascript:;"
                            class="text-primary cursor-pointer">
                            смотреть фото
                        </a>
                    </p>
                </div>

                <div class="col-12 mb-2">
                    <p>
                        <b>CITY</b>
                        <a href="tel:+375298367797" class="float-right text-decoration-underline">
                            <b>Позвонить</b>
                        </a><br>
                        г. Брест, ул. Гоголя 67
                    </p>
                    <p>10.00 - 21.00 ежедневно</p>
                    <p>
                        <a data-fancybox data-src="#shop-photos-4" href="javascript:;"
                            class="text-primary cursor-pointer">
                            смотреть фото
                        </a>
                    </p>
                </div>

            </div>
        </div>
        <h1 class="display-4 text-center" style="font-size: 26px;">
            Почему Вам стоит заказать на barocco.by
        </h1>
        <div class="col-12 mt-4 mb-5">
            @include('includes.advantages-block')
        </div>
    </div>

    {{-- Modals (photos) --}}
    <div style="display: none;" id="shop-photos-1" style="max-width: 400px">
        <img src="/images/shop_photos/BAROCCO_00.jpg" alt="BAROCCO_00" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_01.jpg" alt="BAROCCO_01" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_02.jpg" alt="BAROCCO_02" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_03.jpg" alt="BAROCCO_03" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_04.jpg" alt="BAROCCO_04" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_05.jpg" alt="BAROCCO_05" class="img-fluid mb-2">
        <img src="/images/shop_photos/BAROCCO_06.jpg" alt="BAROCCO_06" class="img-fluid mb-2">
    </div>

    <div style="display: none;" id="shop-photos-2">
        <img src="/images/shop_photos/Moskva_00.jpg" alt="Moskva_00" class="img-fluid mb-2">
        <img src="/images/shop_photos/Moskva_01.jpg" alt="Moskva_01" class="img-fluid mb-2">
        <img src="/images/shop_photos/Moskva_02.jpg" alt="Moskva_02" class="img-fluid mb-2">
        <img src="/images/shop_photos/Moskva_03.jpg" alt="Moskva_03" class="img-fluid mb-2">
        <img src="/images/shop_photos/Moskva_04.jpg" alt="Moskva_04" class="img-fluid mb-2">
        <img src="/images/shop_photos/Moskva_05.jpg" alt="Moskva_05" class="img-fluid mb-2">
    </div>

    <div style="display: none;" id="shop-photos-3">
        <img src="/images/shop_photos/VITACCI_00.jpg" alt="VITACCI_00" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_01.jpg" alt="VITACCI_01" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_02.jpg" alt="VITACCI_02" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_03.jpg" alt="VITACCI_03" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_04.jpg" alt="VITACCI_04" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_05.jpg" alt="VITACCI_05" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_06.jpg" alt="VITACCI_06" class="img-fluid mb-2">
        <img src="/images/shop_photos/VITACCI_07.jpg" alt="VITACCI_07" class="img-fluid mb-2">
    </div>

    <div style="display: none;" id="shop-photos-4">
        <img src="/images/shop_photos/CITY_00.jpg" alt="CITY_00" class="img-fluid mb-2">
        <img src="/images/shop_photos/CITY_01.jpg" alt="CITY_01" class="img-fluid mb-2">
        <img src="/images/shop_photos/CITY_02.jpg" alt="CITY_02" class="img-fluid mb-2">
        <img src="/images/shop_photos/CITY_03.jpg" alt="CITY_03" class="img-fluid mb-2">
        <img src="/images/shop_photos/CITY_04.jpg" alt="CITY_04" class="img-fluid mb-2">
        <img src="/images/shop_photos/CITY_05.jpg" alt="CITY_05" class="img-fluid mb-2">
    </div>

    <style>
        .fancybox-content {
            max-width: 1020px;
        }
    </style>
@endsection
