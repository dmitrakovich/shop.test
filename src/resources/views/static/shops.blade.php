@extends('layouts.app')

@php SeoFacade::setTitle('Магазины') @endphp

@section('breadcrumbs', Breadcrumbs::render('static-shops'))

@section('content')
    {{-- blade-formatter-disable --}}
    <script src="https://api-maps.yandex.ru/2.1/?apikey=f8c761a5-ac2c-4665-a146-d4c23407c140&lang=ru_RU"
        type="text/javascript"></script>
    {{-- blade-formatter-enable --}}
    <div class="col-12 static-page">
        <div class="row mt-4">
            <div class="col-md-8 mb-5">
                <div id="js-yandexMap" style="width: 100%; height: 400px;">
                </div>
                {{-- blade-formatter-disable --}}
                    <script>
                        ymaps.ready(function() {
                            objectManager = null;
                            let yandexMap = new ymaps.Map('js-yandexMap', {
                                center: [
                                    '53.9084571',
                                    '27.4324877'
                                ],
                                zoom: 6
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
                @foreach ($shops as $shop)
                    <div class="col-12 @if (!$loop->last) border-bottom @endif mb-2">
                        <p>
                            @if ($shop->name)
                                <b>{{ $shop->name }}</b>
                            @endif
                            @if ($shop->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+\(\)-]/', '', $shop->phone) }}"
                                    class="text-decoration-underline float-right">
                                    <b>Позвонить</b>
                                </a><br>
                            @endif
                            @if ($shop->address)
                                {{ $shop->address }}
                            @endif
                        </p>
                        @if ($shop->worktime)
                            <p>{{ $shop->worktime }}</p>
                        @endif
                        <p>
                            <a data-fancybox data-src="#js-shopPhotos-{{ $shop->id }}" href="javascript:;"
                                class="text-primary cursor-pointer">
                                смотреть фото
                            </a>
                        </p>
                    </div>
                    <div style="display: none;" id="js-shopPhotos-{{ $shop->id }}"
                        style="max-width: 400px">
                        @foreach ($shop->photos as $photo)
                            <img src="{{ $photo }}" alt="{{ $shop->name }}" class="img-fluid mb-2">
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
                                    'coordinates': ['{{ $shop->geo_latitude }}',
                                        '{{ $shop->geo_longitude }}'
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
