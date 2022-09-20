@extends('layouts.app')
@section('content')
<div class="w-100 my-4">
    {{ Banner::getCatalogTop() }}
    {{ Banner::getCatalogMob() }}
</div>
<div>
    <h2 class="text-center my-5">
        404<br>
        К сожалению такой страницы не существует.<br>
        Посмотрите популярные товары
    </h2>
</div>
<div class="col-md-12 my-5">
    @php
    $simpleSliders = method_exists('\App\Services\SliderService', 'getSimple') ? (new \App\Services\SliderService)->getSimple() : [];
    $simpleSliders = $simpleSliders[0] ?? null;
    @endphp
    @includeWhen(isset($simpleSliders), 'partials.index.simple-slider', ['simpleSlider' => ($simpleSliders)])
</div>
<div class="my-5">
    <a href="{{ config('contacts.viber.link') }}" data-gtm-user-event="callViber">
        {{ config('contacts.viber.name') }}
    </a> /
    <a href="{{ config('contacts.telegram.link') }}" data-gtm-user-event="callTelegram">
        {{ config('contacts.telegram.name') }}
    </a> /
    <a href="{{ config('contacts.whats-app.link') }}" data-gtm-user-event="callWhatsApp">
        {{ config('contacts.whats-app.name') }}
    </a>
</div>
@endsection