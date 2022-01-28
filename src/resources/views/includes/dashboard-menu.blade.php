<a class="col static-pages-menu-item" href="{{ route('orders.index') }}">
    <img src="/images/icons/cart.svg" class="img-fluid mr-3" alt="Мои заказы">
    Мои заказы
</a>
<a class="col static-pages-menu-item" href="{{ route('dashboard.favorites') }}">
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
@auth
<a class="col static-pages-menu-item" href="{{ route('logout') }}"
    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <img src="/images/icons/logout.svg" class="img-fluid mr-3" alt="Выйти">
    {{ __('auth.Logout') }}
</a>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endauth
