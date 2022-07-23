@extends('layouts.app')

@section('title', $product->extendedName())
@section('ogImage', $product->getFirstMedia()->getUrl('full'))

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
