@extends('layouts.app')

@section('title', 'Женская обувь')

@section('content')
<div class="col-3 d-none d-lg-block">
    @include('includes.catalog-filters')
</div>
<div class="col-12 col-lg-9 static-page">
    {{ Breadcrumbs::render('index') }}
    {{ Breadcrumbs::render('static-payment') }}


    <div class="row">
        @forelse($products as $product)
            @php /** @var App\Models\Product $product */ @endphp
            <div class="col-3 border">
                <p>{{ $product->id }}</p>
                <p>{{ $product->category->title }}</p>
                <p>
                    <a href="{{ route('product', $product->id) }}" class="btn btn-primary">
                        {{ $product->title }}
                    </a>
                </p>
            </div>
            {{-- <pre> --}}
            {{-- {{ dd($product) }} --}}
            {{-- </pre> --}}
        @empty
            <p>Нет товаров</p>
        @endforelse
    </div>

    <div class="row mt-5">
        @if ($products->total() > $products->count())
            {{ $products->links() }}
        @endif
    </div>


</div>
@endsection