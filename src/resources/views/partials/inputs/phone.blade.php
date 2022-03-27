@php
$countries = $countries ?? App\Models\Country::getAll();
$currentCountry = $currentCountry ?? App\Models\Country::getCurrent();
@endphp

<div class="js-phone-component">
    <div class="input-group">
        <div class="input-group-prepend">
            <button
                class="btn btn-outline-secondary dropdown-toggle py-0 px-3 js-phone-select-country"
                type="button"
                data-toggle="dropdown"
                aria-expanded="false"
            >
                <img src="{{ $currentCountry->img }}" width="40" height="30" alt="{{ $currentCountry->name }}">
            </button>
            <div class="dropdown-menu h-auto overflow-auto" style="max-height: 200px;">
                @foreach ($countries as $country)
                    <a class="dropdown-item px-3 js-phone-country" href="{{ $country->code }}" data-mask="{{ $country->mask }}">
                        <img src="{{ $country->img }}" width="40" height="30" alt="{{ $country->name }}">
                        <span class="px-2">{{ $country->name }}</span>
                        <span class="text-muted">{{ $country->prefix }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <input id="phone" type="tel" name="phone"
            placeholder="{{ $currentCountry->mask }}"
            class="form-control js-phone-input @error('phone') is-invalid @enderror"
            value="{{ old('phone', optional(auth()->user())->phone) }}"
            data-code="{{ $currentCountry->code }}"
        >
        @error('phone')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
