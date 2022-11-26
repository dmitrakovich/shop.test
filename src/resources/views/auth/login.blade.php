@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-xl-5">

                @if (session('status'))
                    <div class="mb-4 alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="card my-5">
                    <div class="card-header">
                        <h4>Войти/Зарегистрироваться</h4>
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
                                    @include('partials.inputs.phone', ['readonly' => !!old('phone')])
                                </div>
                            </div>

                            @if (old('phone'))
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
                                                window.onload = function () {
                                                    var timer = document.querySelector('#sms-throttle-timer');
                                                    var duration = parseInt(timer.textContent, 10);
                                                    var interval = setInterval(function () {
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
                                        <input type="submit" id="resend-otp-button"
                                            class="btn btn-link p-0 text-muted @if (session('smsThrottle') > 0) d-none @endif"
                                            style="border-bottom: 1px dashed #999999" value="Запросить код еше раз"
                                            onclick="document.getElementById('otp')?.remove()">
                                    </div>
                                </div>
                            @endif

                            @include('includes.captcha-privacy-policy')
                            <br />

                            <div class="form-group row">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-dark px-4">
                                        @if (old('phone'))
                                            Войти
                                        @else
                                            Получить код
                                        @endif
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
