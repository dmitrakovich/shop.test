@extends('layouts.app')

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
