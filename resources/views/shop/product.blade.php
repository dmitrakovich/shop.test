@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('product', $product))
{{-- {{ Breadcrumbs::render('product', $product) }} --}}

@section('content')
    <pre>
        {{ print_r($product->getAttributes()) }}
    </pre>
@endsection