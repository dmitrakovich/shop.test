@php
    $discount = \App\Models\User\Group::where('id', \App\Models\User\Group::REGISTERED)->first()?->discount;
@endphp
<div class="col-12 offer-to-register-popup py-3" style="width: 640px; max-width: 90%;">
    <button data-fancybox-close type="button" title="Close"
        class="fancybox-button fancybox-close-small bg-danger rounded-circle m-2">
        <svg width="45" height="44" viewBox="0 0 45 44" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="2.23223" y1="42.2322" x2="42.2322" y2="2.23223" stroke="white" stroke-width="5" />
            <line x1="2.76777" y1="2.23223" x2="42.7678" y2="42.2322" stroke="white" stroke-width="5" />
        </svg>
    </button>
    <div class="row" style="line-height: 1;">
        @if ($discount)
            <a href="{{ route('login') }}" class="col-12 col-md-6 text-danger">
                <span style="font-size: 10rem; font-weight: 600;">{{ $discount }}%</span>
            </a>
        @endif
        <a href="{{ route('login') }}" class="col-12 col-md-6 text-danger mt-3">
            <span class="h1">скидка на первый заказ</span><br />
            <span class="h2">за регистрацию</span>
        </a>
        <a href="{{ route('login') }}">
            <img src="{{ asset('/images/offers/register.png') }}" alt="register" class="img-fluid">
        </a>
        <div class="col-12 font-size-12 text-left">
            * скидка не суммируется с акциями и промокодами
        </div>
        <div class="col-12 mt-2 text-center">
            <a href="{{ route('login') }}" class="btn btn-danger" style="font-size: 22px">
                зарегистрироваться
            </a>
        </div>
    </div>
</div>
