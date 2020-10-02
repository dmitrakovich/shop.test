@extends('layouts.app')

@section('title', 'Корзина')

@section('breadcrumbs', Breadcrumbs::render('cart'))

@section('content')
    <div class="col-10">
        <div class="row">
            @foreach ($items as $row)
            {{-- {{ dump($row) }} --}}
                <div class="col-12">
                    <div class="row">
                        <div class="col-2">
                            <img src="/images/products/{{ $row->associatedModel->images->first()['img'] }}" 
                                alt="{{ $row->associatedModel->title }}" class="img-fluid">
                        </div>
                        <div class="col-3">
                            {{ $row->associatedModel->getFullName() }} <br>
                           <small>{{ $row->associatedModel->category->title }}</small>
                        </div>
                        <div class="col-1">{{ DeclensionNoun::make($row->quantity, 'пара') }}</div>
                        <div class="col-1">размер 36</div>
                        <div class="col-2">{{ $row->associatedModel->color->name }}</div>
                        <div class="col-3">{{ $row->associatedModel->product_price }} BYN</div>
                    </div>
                </div>
            @endforeach
        </div>
        
    </div>
    
@endsection