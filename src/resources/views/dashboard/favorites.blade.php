@extends('layouts.app')

@section('title', 'Мои заказы')

@section('breadcrumbs', Breadcrumbs::render('dashboard-favorites'))

@section('content')
<div class="col-3 d-none d-lg-block">
    @include('includes.dashboard-menu')
</div>

<div class="col-12 col-lg-9 static-page favorites-page">
    <div class="row justify-content-start">
        @foreach ($products as $product)
            @include('shop.catalog-product', compact('product'))
        @endforeach
    </div>
</div>
@endsection
