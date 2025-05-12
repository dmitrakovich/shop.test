@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header">
                        <h4>Проверьте свой адрес электронной почты</h4>
                    </div>

                    <div class="card-body">
                        @if (session('status') == 'verification-link-sent')
                            <div class="mb-4 alert alert-success" role="alert">
                                На адрес электронной почты, который вы указали при регистрации, была
                                отправлена ​​новая ссылка для подтверждения.
                            </div>
                        @endif

                        <div class="mb-4 text-sm text-gray-600">
                            Спасибо за регистрацию!
                            Прежде чем начать, не могли бы вы подтвердить свой адрес электронной почты,
                            щелкнув ссылку, которую мы только что отправили вам по электронной почте?
                            Если вы не получили письмо, мы с радостью отправим вам его еще раз.
                        </div>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-muted text-decoration-underline">
                                Выслать повторно письмо для подтверждения
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf

                            <button type="submit" class="btn btn-secondary">
                                Выйти с аккаунта
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
