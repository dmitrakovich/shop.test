@extends('layouts.app')

@section('title', 'Мои данные')

@section('breadcrumbs', Breadcrumbs::render('dashboard-profile'))

@section('content')
    <div class="col-3 d-none d-lg-block">
        @include('includes.dashboard-menu')
    </div>


    <div class="col-12 col-lg-9 static-page">
        @if ($emailVerified)
            <div class="alert alert-success" role="alert">
                Электронная почта успешно подтверждена!
            </div>
        @endif

        <h3>Мои данные</h3>

        @include('includes.result-messages')

        <form method="post" action="{{ route('dashboard-profile-update', $user) }}" class="mt-4">
            @method('PATCH')
            @csrf
            <div class="form-group row">
                <div class="col-12 col-md mt-2">
                    <input id="last_name" type="text" name="last_name"
                        class="form-control @error('last_name') is-invalid @enderror" placeholder="Фамилия"
                        value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md mt-2">
                    <input id="first_name" type="text" name="first_name"
                        class="form-control @error('first_name') is-invalid @enderror" placeholder="Имя"
                        value="{{ old('first_name', $user->first_name) }}">
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-12 col-md mt-2">
                    <input id="patronymic_name" type="text" name="patronymic_name"
                        class="form-control @error('patronymic_name') is-invalid @enderror"
                        placeholder="Отчество" value="{{ old('patronymic_name', $user->patronymic_name) }}">
                    @error('patronymic_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col d-none d-xl-block"></div>
            </div>
            <div class="form-group row">
                <label for="email" class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Ваш
                    e-mail</label>
                <div class="col-12 col-md-8 col-lg-4 col-xl-3">
                    <input id="email" type="email" name="email"
                        class="form-control @error('email') is-invalid @enderror" placeholder="email"
                        value="{{ $user->email }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>


            {{-- <div class="form-group row">
                <label for="password"
                    class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Пароль</label>
                <div class="col-12 col-md-8 col-lg-4 col-xl-3">
                    <input id="password" type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div> --}}

            <div class="form-group row">
                <div class="col-12">
                    <hr>
                </div>
            </div>

            {{-- <div class="form-group row">
                <label for="phone"
                    class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Телефон</label>
                <div class="col-12 col-md-8 col-lg-4 col-xl-3">
                    @include('partials.inputs.phone',
                        compact('countries', 'currentCountry'))
                </div>
            </div> --}}

            <div class="form-group row">
                <label for="birth_date" class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Дата
                    рождения</label>
                <div class="col-12 col-md-8 col-lg-4 col-xl-3">
                    <input id="birth_date" type="date" name="birth_date" min="1900-01-01"
                        max="{{ date('Y-m-d') }}"
                        class="form-control @error('birth_date') is-invalid @enderror">
                    @error('birth_date')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="country_id"
                    class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Страна</label>
                <div class="col-12 col-md-8 col-lg-4 col-xl-3">
                    <select id="country_id" name="country_id"
                        class="form-control @error('country_id') is-invalid @enderror">
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected($country->id == $currentCountry->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="city" class="d-none d-md-block col-md-4 col-xl-3 col-form-label">
                    Город
                </label>
                <div class="col-12 col-md-8 col-xl-6">
                    <input id="city" type="text"
                        class="form-control @error('city') is-invalid @enderror" name="city"
                        value="{{ old('city', $user->getFirstAddress()?->city) }}" required
                        autocomplete="address-level2" placeholder="Город" />
                    @error('city')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="address"
                    class="d-none d-md-block col-md-4 col-xl-3 col-form-label">Адрес</label>
                <div class="col-12 col-md-8 col-xl-6">
                    <input id="address" type="text" name="address"
                        class="form-control @error('address') is-invalid @enderror" placeholder="Адрес"
                        value="{{ old('address', $user->getFirstAddress()?->address) }}">
                    @error('address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row my-5">
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-dark btn-lg btn-block">Сохранить</button>
                </div>
            </div>

        </form>

    </div>
@endsection
