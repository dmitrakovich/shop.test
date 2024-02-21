<div class="container-fluid">
    <div class="wrapper">
        <header class="inc-header">
            <div class="inc-header__top">
                <div class="inc-header__top-left">
                    <div class="inc-header__top-btns">
                        <button class="js-showMainMenu" type="button">
                            @include('svg.burger')
                        </button>
                        <button class="js-mobileSearch" type="button" data-toggle="collapse"
                            data-target="#js-headerSearchInput">
                            @include('svg.search')
                        </button>
                    </div>
                    <p class="inc-header__top-info d-none d-md-block">
                        Ежедневно с 08:00 до 21:00<br>
                        <a href="{{ config('contacts.phone.link') }}" data-gtm-user-event="callPhone">
                            {{ config('contacts.phone.name') }}
                        </a>
                        <a href="{{ config('contacts.phone2.link') }}"
                            data-gtm-user-event="callPhone">{{ config('contacts.phone2.name') }}</a>
                        <br />
                        <a href="{{ config('contacts.viber.link') }}" data-gtm-user-event="callViber">
                            {{ config('contacts.viber.name') }}
                        </a> /
                        <a href="{{ config('contacts.telegram.link') }}"
                            data-gtm-user-event="callTelegram">
                            {{ config('contacts.telegram.name') }}
                        </a> /
                        <a href="{{ config('contacts.whats-app.link') }}"
                            data-gtm-user-event="callWhatsApp">
                            {{ config('contacts.whats-app.name') }}
                        </a>
                    </p>
                </div>
                <a href="{{ route('index-page') }}" class="inc-header__top-logo">
                    <img src="/images/icons/barocco.svg" alt="Barocco" loading="lazy">
                </a>
                <div class="inc-header__top-right">
                    <div class="inc-header__top-btns">
                        <a href="{{ route('orders.index') }}">
                            @include('svg.account')
                        </a>
                        <a href="{{ route('favorites.index') }}" class="d-none d-md-flex">
                            @include('svg.favorites')
                        </a>
                        <a href="{{ route('cart') }}">
                            <span class="position-relative">
                                @include('svg.cart')
                                <span class="inc-header__top-btns_count js-cartCount">
                                    {{ Cart::itemsCount() }}
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="inc-header__menu" id="mainMenu">
                <div class="inc-header__menu-content">
                    <nav class="inc-header__menu-nav">
                        <ul class="inc-header__menu-nav_list">
                            @if (!empty($g_navCategories))
                                <li class="inc-header__menu-nav_dropdown">
                                    <a href="{{ route('shop') }}">Каталог</a>
                                    <div class="inc-header__menu-nav_collapse collapse">
                                        <div class="inc-header__menu-catalog">
                                            <div>
                                                <a href="{{ route('shop') }}">Обувь ></a>
                                                @foreach ($g_navCategories->take(ceil(count($g_navCategories) / 2)) as $category)
                                                    <a
                                                        href="{{ route('shop', $category) }}">{{ $category->title }}</a>
                                                @endforeach
                                            </div>
                                            <div>
                                                <a>&nbsp;</a>
                                                @foreach ($g_navCategories->skip(floor(count($g_navCategories) / 2)) as $category)
                                                    <a
                                                        href="{{ route('shop', $category) }}">{{ $category->title }}</a>
                                                @endforeach
                                            </div>
                                            <div>
                                                <a href="{{ route('shop') }}">Коллекции ></a>
                                                <a href="/catalog/heel-low">
                                                    На низком каблуке
                                                </a>
                                                <a href="/catalog/sport">
                                                    Спортивная обувь
                                                </a>
                                                <a href="/catalog/office">
                                                    Офисная обувь
                                                </a>
                                                <a href="/catalog?sort=rating">
                                                    Бестселлеры
                                                </a>
                                                <a href="/catalog/heel-stiletto">
                                                    На шпильке
                                                </a>
                                                <a href="/catalog/evening">
                                                    Вечерняя коллекция
                                                </a>
                                            </div>
                                            <div>
                                                {{ Banner::getMainMenuCatalog() }}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-block d-md-none">
                                    <a data-toggle="collapse" href="#mainMenuCategoryCollapse"
                                        role="button" aria-expanded="false"
                                        aria-controls="mainMenuCategoryCollapse"
                                        class="inc-header__menu-nav_collapse-btn collapsed">
                                        Категории
                                    </a>
                                    <div class="inc-header__menu-nav_collapse collapse"
                                        id="mainMenuCategoryCollapse">
                                        @foreach ($g_navCategories as $category)
                                            <a
                                                href="{{ route('shop', $category) }}">{{ $category->title }}</a>
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                            <li><a href="{{ route('shop', ['st-new']) }}">New!</a></li>
                            <li class="d-md-none"><a href="{{ route('shop', ['st-sale']) }}">Sale</a>
                            </li>
                            <li><a href="{{ route('static-shops') }}">Магазины</a></li>
                            <li class="inc-header__menu-nav_dropdown">
                                <a href="{{ route('info') }}" class="d-none d-md-block">Условия</a>
                                <a data-toggle="collapse"
                                    class="d-flex d-md-none inc-header__menu-nav_collapse-btn collapsed"
                                    href="#mainMenuInfoCollapse" role="button" aria-expanded="false"
                                    aria-controls="mainMenuInfoCollapse">
                                    Условия
                                </a>
                                @if (!empty($g_navInfoPages))
                                    <div class="inc-header__menu-nav_collapse collapse"
                                        id="mainMenuInfoCollapse">
                                        <div class="inc-header__menu-info">
                                            @foreach ($g_navInfoPages as $item)
                                                <a href="{{ route('info', $item['slug']) }}">
                                                    {{ $item['name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </li>
                            <li><a href="{{ route('info', 'installments') }}">Рассрочка</a></li>
                            <li><a href="{{ route('feedbacks') }}">Отзывы</a></li>
                            <li class="d-none d-lg-block"><a href="{{ route('dashboard-card') }}">Карта
                                    клиента</a></li>
                            <li class="inc-header__menu-nav_sale"><a
                                    href="{{ route('shop', ['st-sale']) }}">Sale</a>
                            </li>
                            <li class="inc-header__menu-nav_backdrop"></li>
                        </ul>
                    </nav>
                    <form action="{{ route('shop') }}" method="get" class="inc-header__menu-search">
                        <input type="text" name="search" value="{{ request()->get('search') }}"
                            placeholder="Поиск">
                        <button type="submit" class="btn p-0">
                            @include('svg.search')
                        </button>
                    </form>
                    <div class="d-block d-md-none mt-3">
                        {{ Currency::getSwitcher() }}
                    </div>
                </div>
            </div>
            <form class="inc-header__search collapse" action="{{ route('shop') }}" method="get"
                id="js-headerSearchInput">
                <input type="text" name="search" value="{{ request()->get('search') }}"
                    placeholder="Поиск">
                <button type="submit" class="btn p-0">
                    @include('svg.search')
                </button>
            </form>
        </header>
    </div>
</div>
