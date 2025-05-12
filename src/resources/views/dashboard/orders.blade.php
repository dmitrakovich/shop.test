@extends('layouts.app')

@section('title', 'Мои заказы')

@section('breadcrumbs', Breadcrumbs::render('dashboard-orders'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.dashboard-menu')
    </div>
    <div class="col-12 col-lg-9 col-xl-8 dh_orders">
        <div class="dh_orders__tabs nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link dh_orders__tab active" id="js-allOrdersTab" data-toggle="tab" href="#js-allOrders"
                role="tab" aria-controls="js-allOrders" aria-selected="true">Все
                заказы ({{ count($allOrders) }})</a>
            @if (count($expectedOrders))
                <a class="nav-item nav-link dh_orders__tab" id="js-expectedOrdersTab" data-toggle="tab"
                    href="#js-expectedOrders" role="tab" aria-controls="js-expectedOrders" aria-selected="false">Ожидают
                    отправки ({{ count($expectedOrders) }})</a>
            @endif
            @if (count($sentOrders))
                <a class="nav-item nav-link dh_orders__tab" id="js-sentOrdersTab" data-toggle="tab" href="#js-sentOrders"
                    role="tab" aria-controls="js-sentOrders" aria-selected="false">Отправленные
                    ({{ count($sentOrders) }})</a>
            @endif
            @if (count($completedOrders))
                <a class="nav-item nav-link dh_orders__tab" id="js-completedOrderTab" data-toggle="tab"
                    href="#js-completedOrder" role="tab" aria-controls="js-completedOrder"
                    aria-selected="false">Завершенные ({{ count($completedOrders) }})</a>
            @endif
            @if (count($canceledOrders))
                <a class="nav-item nav-link dh_orders__tab" id="js-canceledOrderTab" data-toggle="tab"
                    href="#js-canceledOrder" role="tab" aria-controls="js-canceledOrder"
                    aria-selected="false">Отмененные ({{ count($canceledOrders) }})</a>
            @endif
        </div>
        <div class="tab-content" id="nav-tabContent">
            <div class="dh_orders__list tab-pane fade show active" id="js-allOrders" role="tabpanel"
                aria-labelledby="js-allOrdersTab">
                @include('dashboard.orders_list', ['orders' => $allOrders])
            </div>
            @if (count($expectedOrders))
                <div class="dh_orders__list tab-pane fade" id="js-expectedOrders" role="tabpanel"
                    aria-labelledby="js-expectedOrdersTab">
                    @include('dashboard.orders_list', ['orders' => $expectedOrders])
                </div>
            @endif
            @if (count($sentOrders))
                <div class="dh_orders__list tab-pane fade" id="js-sentOrders" role="tabpanel"
                    aria-labelledby="js-sentOrdersTab">
                    @include('dashboard.orders_list', ['orders' => $sentOrders])
                </div>
            @endif
            @if (count($completedOrders))
                <div class="dh_orders__list tab-pane fade" id="js-completedOrder" role="tabpanel"
                    aria-labelledby="js-completedOrderTab">
                    @include('dashboard.orders_list', ['orders' => $completedOrders])
                </div>
            @endif
            @if (count($canceledOrders))
                <div class="dh_orders__list tab-pane fade" id="js-canceledOrder" role="tabpanel"
                    aria-labelledby="js-canceledOrderTab">
                    @include('dashboard.orders_list', ['orders' => $canceledOrders])
                </div>
            @endif
        </div>
    </div>

    <div style="display: none;" id="leave-feedback-modal">
        <form id="leave-feedback-form" action="{{ route('feedbacks.store') }}" method="post">
            @csrf
            <h3 class="mb-4">Оставить отзыв</h3>
            <input type="hidden" name="product_id" value="{{ $product->id ?? 0 }}">
            <div class="row form-group">
                <label for="textareaText" class="col-12 col-md-4 col-form-label">
                    <b>Оставьте комментарий</b>&nbsp;<font color="red">*</font>
                </label>
                <div class="col-12 col-md-8">
                    <textarea rows="5" class="form-control" name="text" id="textareaText"
                        placeholder="Что вам понравилось в этом товаре?"></textarea>
                </div>
            </div>

            <div class="row form-group">
                <label for="inputName" class="col-12 col-md-4 col-form-label">
                    <b>Представьтесь, пожалуйста</b>&nbsp;<font color="red">*</font>
                </label>
                <div class="col-12 col-md-8">
                    <input type="text" name="user_name" id="inputName" class="form-control"
                        value="{{ optional(auth()->user())->first_name }}" autocomplete="given-name" placeholder="Имя"
                        required>
                </div>
            </div>

            <div class="row form-group">
                <label for="inputCity" class="col-12 col-md-4 col-form-label">
                    <b>Город</b>&nbsp;<font color="red">*</font>
                </label>
                <div class="col-12 col-md-8">
                    <input type="text" name="user_city" id="inputCity" class="form-control"
                        value="{{ optional(auth()->user())->getFirstAddress()?->city }}" autocomplete="address-level2"
                        placeholder="Город" required>
                </div>
            </div>

            <div class="row form-group">
                <label for="inputPhotos" class="col-12 col-md-4 col-form-label">
                    <b>Загрузите фотографии</b>
                </label>
                <div class="col-12 col-md-8">
                    <input type="file" accept="image/*" name="photos[]" id="inputPhotos" class="form-control-file"
                        multiple>
                </div>
            </div>

            <div class="row form-group">
                <label for="inputVideos" class="col-12 col-md-4 col-form-label">
                    <b>Загрузите видео</b>
                </label>
                <div class="col-12 col-md-8">
                    <input type="file" accept="video/*" name="videos[]" id="inputVideos" class="form-control-file"
                        multiple>
                </div>
            </div>

            @include('includes.captcha-privacy-policy')

            <div class="row form-group justify-content-end mt-4 mb-0">
                <button type="button" id="leave-feedback-btn" class="btn btn-dark px-4">
                    Оставить отзыв
                </button>
            </div>

        </form>
    </div>
@endsection
