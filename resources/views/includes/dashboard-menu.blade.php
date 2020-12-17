<a class="col static-pages-menu-item" href="{{ route('orders.index') }}">
    <img src="/images/icons/cart.svg" class="img-fluid mr-3" alt="Мои заказы">
    Мои заказы
</a>
<a class="col static-pages-menu-item" href="{{ route('dashboard-saved') }}">
    <img src="/images/icons/favorites.svg" class="img-fluid mr-3" alt="Избранное">
    Избранное
</a>
<a class="col static-pages-menu-item" href="{{ route('dashboard-profile') }}">
    <img src="/images/icons/account.svg" class="img-fluid mr-3" alt="Мои данные">
    Мои данные
</a>
<a class="col static-pages-menu-item" href="{{ route('dashboard-card') }}">
    <img src="/images/icons/gift-card.svg" class="img-fluid mr-3" alt="Карта клиента">
    Карта клиента
</a>
<a class="col static-pages-menu-item" href="{{ route('logout') }}"
    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <img src="/images/icons/logout.svg" class="img-fluid mr-3" alt="Выйти">
    {{ __('auth.Logout') }}
</a>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>


{{-- <ul class="navbar-nav ml-auto">
    <!-- Authentication Links -->
    @guest
        <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
        </li>
        @if (Route::has('register'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
            </li>
        @endif
    @else
        <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    @endguest
</ul> --}}
