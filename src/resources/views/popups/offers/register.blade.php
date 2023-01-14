<div class="col-12 py-3 offer-to-register-popup" style="width: 640px; max-width: 90%;">
    <button data-fancybox-close type="button" title="Close"
        class="fancybox-button fancybox-close-small bg-danger rounded-circle m-2">
        <svg width="45" height="44" viewBox="0 0 45 44" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="2.23223" y1="42.2322" x2="42.2322" y2="2.23223" stroke="white" stroke-width="5"/>
            <line x1="2.76777" y1="2.23223" x2="42.7678" y2="42.2322" stroke="white" stroke-width="5"/>
        </svg>
    </button>
    <a href="{{ route('login') }}" class="full-link row" style="line-height: 1;">
        <div class="col-12 col-md-6 text-danger">
            <span style="font-size: 10rem; font-weight: 600;">10%</span>
        </div>
        <div class="col-12 col-md-6 mt-3 text-danger">
            <span class="h1">скидка на первый заказ</span><br />
            <span class="h2">за регистрацию</span>
        </div>
        <img src="{{ asset('/images/offers/register.png') }}" alt="register" class="img-fluid">
        <div class="col-12 text-center mt-2">
            <span class="btn btn-danger" style="font-size: 22px">
                зарегистрироваться
            </span>
        </div>
    </a>
</div>

<style>
    .offer-to-register-popup .full-link:focus-visible {
        outline: unset;
    }
</style>
