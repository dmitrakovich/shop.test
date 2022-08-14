@extends('layouts.app')

@section('title', Seo::getProductTitle($product))
@section('ogImage', $product->getFirstMedia()->getUrl('catalog'))
@section('description', Seo::getProductTitle($product))

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
