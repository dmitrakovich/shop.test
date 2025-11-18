@php
    $hasValidPhone = !empty(old('phone')) && !$errors->has('phone');
    $discount = \App\Models\User\Group::where('id', \App\Models\User\Group::REGISTERED)->first()?->discount;
@endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-xl-6 mt-4">

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($discount)
                    <div class="alert alert-primary h4 text-dark text-center" role="alert">
                        {{ $discount }}% скидки
                        <span class="font-weight-normal">
                            на первый заказ за регистрацию!
                        </span>
                        <br>
                        <span class="font-weight-normal font-size-12">
                            * скидка не суммируется с акциями и промокодами
                        </span>
                    </div>
                @endif

                <div class="card mt-2 mb-5">
                    <div class="card-header">
                        <h1 class="h4">Войти/Зарегистрироваться</h1>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}" name="login_form">
                            @csrf

                            <div class="form-group row">
                                <div class="col-auto">
                                    <b>С помощью номера телефона</b>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-12">
                                    @include('partials.inputs.phone', [
                                        'readonly' => $hasValidPhone,
                                    ])
                                </div>
                            </div>

                            @if ($hasValidPhone)
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <input id="otp" type="text"
                                            class="form-control @error('otp') is-invalid @enderror" name="otp"
                                            placeholder="Введите код" required autocomplete="off" />
                                        @error('otp')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        @if (session('smsThrottle') > 0)
                                            <span id="sms-throttle-timer-wrapper">
                                                <span class="text-muted">Запросить код повторно через </span>
                                                <span class="text-danger">
                                                    00:<span id="sms-throttle-timer">{{ session('smsThrottle') }}</span>
                                                </span>
                                            </span>
                                            <script>
                                                window.onload = function() {
                                                    var timer = document.querySelector('#sms-throttle-timer');
                                                    var duration = parseInt(timer.textContent, 10);
                                                    var interval = setInterval(function() {
                                                        if (--duration < 1) {
                                                            clearInterval(interval);
                                                            document.querySelector('#resend-otp-button').classList.remove('d-none');
                                                            document.querySelector('#sms-throttle-timer-wrapper').remove();
                                                        } else {
                                                            timer.textContent = duration < 10 ? '0' + duration : duration;
                                                        }
                                                    }, 1000);
                                                }
                                            </script>
                                        @endif
                                        <button type="button" id="resend-otp-button"
                                            class="btn btn-link text-muted @if (session('smsThrottle') > 0) d-none @endif p-0"
                                            style="border-bottom: 1px dashed #999999">
                                            Запросить код еше раз
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @include('includes.captcha-privacy-policy')
                            <br />

                            <div class="form-group row">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-dark px-4">
                                        {{ $hasValidPhone ? 'Войти' : 'Получить код' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
