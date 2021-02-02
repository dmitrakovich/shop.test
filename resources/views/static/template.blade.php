@extends('layouts.app')

@section('title', $currentInfoPage['name'])

@section('breadcrumbs', Breadcrumbs::render('info-page', $currentInfoPage))

@section('content')
<div class="col-3 d-none d-lg-block">
    @include('includes.static-pages-menu')
</div>
<div class="col-12 col-lg-9 static-page">
    {!! $currentInfoPage['html'] !!}
</div>
@endsection
