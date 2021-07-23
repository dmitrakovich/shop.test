<div class="col-auto align-self-center ml-0 ml-lg-3 mr-2">
    Валюта:
</div>
<form action="{{ route('currency-switcher') }}" method="post" class="m-0 col-auto col-lg-3 col-xl-2">
    @csrf
    <select onchange="this.form.submit()" name="currency" class="form-control">
        @foreach ($currenciesList as $key => $value)
            <option value="{{ $key }}" {{ $currentCurrency == $key ? 'selected' : null }}>
                {{ $key }}
            </option>
        @endforeach
    </select>
</form>
