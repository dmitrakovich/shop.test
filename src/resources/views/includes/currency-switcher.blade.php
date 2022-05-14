<label for="currency-switcher" class="mb-0 mr-2">
    Валюта:
</label>
<form action="{{ route('currency-switcher') }}" method="post" class="m-0 col-auto col-lg-3 col-xl-2">
    @csrf
    <select onchange="this.form.submit()" id="currency-switcher" name="currency" class="form-control">
        @foreach ($currenciesList as $key => $value)
            <option value="{{ $key }}" @selected($currentCurrency == $key)>
                {{ $key }}
            </option>
        @endforeach
    </select>
</form>
