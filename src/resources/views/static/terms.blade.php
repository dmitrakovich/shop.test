@extends('layouts.app')

@section('title', 'Публичная оферта')

@section('breadcrumbs', Breadcrumbs::render('terms'))

@section('content')
    <div class="col-12 col-lg-9 static-page">
        Публичная оферта
        <p>{{ route('info.terms') }}</p>
        <p>{{ route('info.policy') }}</p>
    </div>
@endsection
