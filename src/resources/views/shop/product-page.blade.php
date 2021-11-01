@extends('layouts.app')

@section('title', $product->category->title . ' ' . $product->extendedName())

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
