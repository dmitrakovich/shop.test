@extends('layouts.app')

@section('title', 'Женская обувь')

@section('breadcrumbs', Breadcrumbs::render('product', $product))

@section('content')
    <div class="col-12">
        <div class="row">
            <div class="col-12 col-md-6 col-xl-7">
                <img src="{{ $product->getFirstMedia()->getUrl('full') }}" alt="{{ $product->title }}" class="img-fluid"><br>
                @foreach ($product->getMedia() as $image)
                    <img src="{{ $image->getUrl('thumb') }}" alt="{{ $product->title }}" class="img-fluid pr-3" style="max-width: 70px">
                @endforeach
            </div>
            <div class="col-12 col-md-6 col-xl-5">
                <div class="col-12">
                    <input type="hidden" id="product_id" value="{{ $product->id }}">

                    <div class="row">
                        <div class="col-6 text-muted">
                            {{ $product->getFullName() }}
                        </div>
                        <div class="col-6">
                            рейтинг
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="col-auto">
                            <s>{{ $product->old_price }}</s>
                            {{ $product->price }}
                        </div>
                        <div class="col-auto">
                            Условия рассрочки
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="col-12 py-3">
                            -30% на вторую и -50% на третью пару до конца апреля
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="col-12 py-3">
                            Цвет / {{ $product->color->name }}
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="product-size">
                            <p><b>Выберите размер:</b> <a href="#">Таблица размеров</a></p>
                            <ul>
                                @foreach ($product->sizes as $size)
                                    <li>
                                        <label class="check">
                                            <input type="checkbox"><i class="checkmark">{{ $size->name }}</i>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
    
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-8">
                            <button class="btn btn-dark btn-lg btn-block py-3 js-add-to-cart">
                                В КОРЗИНУ
                            </button>
                            <button class="btn btn-outline-dark btn-lg btn-block py-3">
                                КУПИТЬ В ОДИН КЛИК
                            </button>
                        </div>
                    </div>
    
                    <div class="col-12 text-center mt-5">
                        <p>
                            Без переплат в рассрочку
                        </p>
                        <p>
                            Примерка по Беларуси
                        </p>
                        <p>
                            Возврат 14 дней
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <div class="row my-5">
            <div class="col-7">
                ОПИСАНИЕ <br>
                {!! $product->description !!}
            </div>
            <div class="col-4 offset-1">
                Цвет - {{ $product->color->name }} <br>
                Сезон - {{ $product->season->name }} <br>
                Материал верха - {{ $product->fabric_top_txt }} <br>
                Внутренний материал - {{ $product->fabric_inner_txt }} <br>
                Материал стельки - {{ $product->fabric_insole_txt }} <br>
                Материал подошвы - {{ $product->fabric_outsole_txt }} <br>
                Высота каблука - {{ $product->heel_txt }} <br>
                Артикул - {{ $product->title }} <br>
            </div>
        </div>

    </div>
    
@endsection