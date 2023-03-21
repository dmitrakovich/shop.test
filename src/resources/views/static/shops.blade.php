@extends('layouts.app')

@php SeoFacade::setTitle('Магазины') @endphp

@section('breadcrumbs', Breadcrumbs::render('static-shops'))

@section('content')
    {{-- blade-formatter-disable --}}
    <script src="https://api-maps.yandex.ru/2.1/?apikey=f8c761a5-ac2c-4665-a146-d4c23407c140&lang=ru_RU"
        type="text/javascript"></script>
    {{-- blade-formatter-enable --}}
    <div class="col-12 static-page">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                @foreach ($cities as $city)
                    <a class="nav-item nav-link @if ($loop->first) active @endif"
                        id="js-cityTab-{{ $city->id }}-tab" data-toggle="tab"
                        href="#js-cityTab-{{ $city->id }}" role="tab" aria-controls="nav-home"
                        aria-selected="true">{{ $city->name }}</a>
                @endforeach
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            @foreach ($cities as $city)
                <div class="tab-pane fade show @if ($loop->first) active @endif"
                    id="js-cityTab-{{ $city->id }}" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row mt-4">
                        <div class="col-md-8 mb-5">
                            <div id="js-yandexMap-{{ $city->id }}" style="width: 100%; height: 400px;">
                            </div>
                            {{-- blade-formatter-disable --}}
                            <script>
                                ymaps.ready(function() {
                                    objectManager = null;
                                    let yandexMap = new ymaps.Map('js-yandexMap-{{ $city->id }}', {
                                        center: [
                                            '{{ $city->stocks->first()->geo_latitude }}',
                                            '{{ $city->stocks->first()->geo_longitude }}'
                                        ],
                                        zoom: 10
                                    }, {
                                        searchControlProvider: 'yandex#search'
                                    });
                                    objectManager = new ymaps.ObjectManager({
                                        clusterize: true,
                                        gridSize: 180,
                                        clusterDisableClickZoom: false
                                    });
                                    objectManager.objects.options.set('preset', 'islands#redDotIcon');
                                    objectManager.clusters.options.set('preset', 'islands#redClusterIcons');
                                    yandexMap.geoObjects.add(objectManager);
                                });
                            </script>
                            {{-- blade-formatter-enable --}}
                        </div>
                        <div class="col-md-4 mb-5">
                            @foreach ($city->stocks as $shop)
                                <div
                                    class="col-12 @if (!$loop->last) border-bottom @endif mb-2">
                                    <p>
                                        <b>{{ $shop->name }}</b>
                                        <a href="tel:{{ $shop->phone }}"
                                            class="text-decoration-underline float-right">
                                            <b>Позвонить</b>
                                        </a><br>
                                        {{ $shop->address }}
                                    </p>
                                    <p>{{ $shop->worktime }}</p>
                                    <p>
                                        <a data-fancybox data-src="#js-shopPhotos-{{ $shop->id }}"
                                            href="javascript:;" class="text-primary cursor-pointer">
                                            смотреть фото
                                        </a>
                                    </p>
                                </div>
                                <div style="display: none;" id="js-shopPhotos-{{ $shop->id }}"
                                    style="max-width: 400px">
                                    @foreach ($shop->photos as $photo)
                                        <img src="{{ $photo }}" alt="{{ $shop->name }}"
                                            class="img-fluid mb-2">
                                    @endforeach
                                </div>
                                {{-- blade-formatter-disable --}}
                                <script>
                                    ymaps.ready(function() {
                                        objectManager.add({
                                            'type': 'Feature',
                                            'id': '{{ $shop->id }}',
                                            'geometry': {
                                                'type': 'Point',
                                                'coordinates': ['{{ $city->stocks->first()->geo_latitude }}',
                                                    '{{ $city->stocks->first()->geo_longitude }}'
                                                ],
                                            },
                                            'properties': {
                                                'balloonContentHeader': '{{ $shop->address }}',
                                                'balloonContentBody': "<br/> @if ($shop->phone) Тел.: {{ $shop->phone }}<br/><br/> @endif @if ($shop->worktime) Режим работы: <br/> {{ $shop->worktime }} @endif",
                                                'hintContent': '{{ $shop->name }}'
                                            }
                                        });
                                    });
                                </script>
                                {{-- blade-formatter-enable --}}
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <h1 class="display-4 text-center" style="font-size: 26px;">
            Почему Вам стоит заказать на barocco.by
        </h1>
        <div class="col-12 mt-4 mb-5">
            @include('includes.advantages-block')
        </div>
    </div>

    <style>
        .fancybox-content {
            max-width: 1020px;
        }
    </style>
@endsection
