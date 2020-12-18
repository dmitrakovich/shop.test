@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    @include('shop.product')
@endsection
