@extends('layouts.app')

@section('title', 'Мои заказы')

@section('breadcrumbs', Breadcrumbs::render('dashboard-orders'))

@section('content')
<div class="col-3 d-none d-lg-block">
    @include('includes.dashboard-menu')
</div>


<div class="col-12 col-lg-9 col-xl-8 mr-auto dashboard-pages">
    <div class="row">
        @forelse ($orders as $order)
            <div class="col-12 order-row p-3 mb-3">
                <div class="row">
                    <div class="col-12 col-md-4 order-1">
                        <p><strong>№ {{ $order->id }}</strong></p>
                        <p>{{ $order->created_at->format('от d F Y') }}</p>
                        {{-- <div>
                            @foreach ($order->photos as $photo)
                                <img src="{{ $photo }}" alt="" class="img-fluid">
                            @endforeach
                        </div> --}}
                    </div>
                    <div class="col-12 col-md-2 order-4 order-md-2">
                        {!! Currency::format($order->getTotalPrice(), $order->currency) !!}
                    </div>
                    <div class="col-12 col-md-3 order-3">
                        {{ $order->user_addr }}
                    </div>
                    <div class="col-12 col-md-3 order-2 order-md-4 text-left text-md-right">
                        <span class="text-primary">
                            {{-- <b>{{ $order->status }}</b> --}}
                            <b>Ожидает подтверждения менеджером</b>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <p>Нет заказов</p>
        @endforelse
    </div>
</div>
@endsection
