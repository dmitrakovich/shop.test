@extends('layouts.app')

@section('title', $product->extendedName())

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
