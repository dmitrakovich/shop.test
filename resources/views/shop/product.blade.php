@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('static-delivery'))

@section('content')
    <pre>
        {{ print_r($product->getAttributes()) }}
    </pre>
@endsection