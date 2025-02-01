@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card my-5">
                    <div class="card-header">
                        <h4>РЕГИСТРАЦИЯ</h4>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard-profile-update', $user) }}"
                            name="register_form">
                            @method('PATCH')
                            @csrf
                            <div class="form-group row">
                                <div class="col-12 text-center text-md-left">
                                    <b>Для завершения активации аккаунта заполните данные</b>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="last_name" class="col-md-5 col-form-label text-md-right">
                                    Фамилия
                                </label>
                                <div class="col-md-6">
                                    <input id="last_name" type="text"
                                        class="form-control @error('last_name') is-invalid @enderror"
                                        name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                        required autocomplete="family-name" autofocus />
                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="first_name" class="col-md-5 col-form-label text-md-right">
                                    Имя
                                </label>
                                <div class="col-md-6">
                                    <input id="first_name" type="text"
                                        class="form-control @error('first_name') is-invalid @enderror"
                                        name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                        required autocomplete="given-name" />
                                    @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="patronymic_name" class="col-md-5 col-form-label text-md-right">
                                    Отчество (не&nbsp;обязательно)
                                </label>
                                <div class="col-md-6">
                                    <input id="patronymic_name" type="text"
                                        class="form-control @error('patronymic_name') is-invalid @enderror"
                                        name="patronymic_name"
                                        value="{{ old('patronymic_name', $user->patronymic_name) }}"
                                        autocomplete="additional-name" />
                                    @error('patronymic_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="city" class="col-md-5 col-form-label text-md-right">
                                    Город
                                </label>
                                <div class="col-md-6">
                                    <input id="city" type="text"
                                        class="form-control @error('city') is-invalid @enderror"
                                        name="city"
                                        value="{{ old('city', $user->getFirstAddress()?->city) }}" required
                                        autocomplete="address-level2" />
                                    @error('city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="address" class="col-md-5 col-form-label text-md-right">
                                    Адрес (не&nbsp;обязательно)
                                </label>
                                <div class="col-md-6">
                                    <input id="address" type="text"
                                        class="form-control @error('address') is-invalid @enderror"
                                        name="address"
                                        value="{{ old('address', $user->getFirstAddress()?->address) }}"
                                        autocomplete="address-level3" />
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="email" class="col-md-5 col-form-label text-md-right">
                                    Email (не&nbsp;обязательно)
                                </label>
                                <div class="col-md-6">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email', $user->email) }}"
                                        autocomplete="email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @include('includes.captcha-privacy-policy')
                            <br>

                            <div class="form-group row">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-dark px-4">
                                        Сохранить
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
