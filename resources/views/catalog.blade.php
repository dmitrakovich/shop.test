@extends('layouts.app')

@section('title', 'Женская обувь')

@section('content')
<div class="col-3 d-none d-lg-block">
    @include('includes.catalog-filters')
</div>
<div class="col-12 col-lg-9 static-page">
    {{ Breadcrumbs::render('index') }}
    {{ Breadcrumbs::render('static-payment') }}
</div>
@endsection