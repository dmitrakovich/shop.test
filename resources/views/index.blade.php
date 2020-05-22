@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
<div class="container-fluid">
    <div class="row wrapper justify-content-center">
        <div class="col-md-8">
            {{-- <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div> --}}

            <div class="col-12">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
            </div>
            <div class="col-12"><hr></div>
            <div class="col-12 test-roboto">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
            </div>
            <div class="col-12"><hr></div>
            <div class="col-12 test-popins">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
            </div>
            <div class="col-12"><hr></div>
            <div class="col-12 test-gilroy">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
            </div>
            <div class="col-12"><hr></div>
            <div class="col-12 test-gill-sans-mt">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. A, sint dolorum doloremque saepe minus consequuntur beatae fugiat ipsam doloribus, ipsum nemo amet modi mollitia similique maxime possimus minima voluptatibus? Aperiam.
            </div>

            <div class="col-12">
                <hr class="d-none d-sm-block">
                <div class="row mx-2 mx-sm-5 font-size-16 font-weight-bold align-items-center">
                    <div class="col-12 col-sm">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="images/index/conditions-fitting.png" alt="Примерка">
                            </div>
                            <div class="col">
                                Примерка по Беларуси<br>
                                <small class="text-muted">на пункте выдачи или курьером</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="images/index/conditions-installment.png" alt="Рассрочка">
                            </div>
                            <div class="col">
                                Рассрочка<br>                                
                                <small class="text-muted">на 2 месяца без предоплаты</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="images/index/conditions-upon_receipt.png" alt="При получении">
                            </div>
                            <div class="col">
                                Оплата при получении<br>
                                <small class="text-muted">наличными или картой</small>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="d-none d-sm-block">
            </div>

            <div class="col-12 mt-5">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-auto text-center">
                        <h1 class="display-4">#BAROCCO look</h1>
                    </div>
                    <div class="col-12 col-sm-auto text-center ml-auto">
                        <a href="{{ config('social.instagram.link') }}">Подпишись на наш Instagram</a>
                    </div>
                </div>
                <div class="row mx-n2 js-instagram-posts"></div>
                <div class="row mt-4 mb-5">
                    <div class="col text-center">
                        <a href="{{ config('social.instagram.link') }}">Больше образов</a>
                    </div>
                </div>
            </div>



        </div>
    </div>
</div>
@endsection
