<header class="header pt-3 mb-2 pt-lg-4 mb-lg-3">
    <div class="container-fluid">
        <div class="row wrapper align-items-center">
            <button class="btn navbar-toggler js-show-main-menu" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>


            <div class="account-btns">
                <button class="btn p-0 d-inline-block d-lg-none js-mobile-search" type="button"
                    data-toggle="collapse" data-target="#search-input">
                    <img src="/images/icons/search.svg" alt="Поиск" class="img-fluid">
                </button>
            </div>
            <div class="col-auto d-none d-lg-block">
                <p style="line-height: 1.15rem;"><small>
						Ежедневно&nbsp;с 08:00&nbsp;до&nbsp;21:00<br>
                        <a href="{{ config('contacts.phone.link') }}" data-gtm-user-event="callPhone">
                            {{ config('contacts.phone.name') }}
                        </a>&nbsp;
                        <a href="tel:+375295227722" data-gtm-user-event="callPhone">+375 (29) 522 77
                            22</a>
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
                    </small></p>

            </div>
            <div class="col col-sm-auto ml-auto">
                <a href="{{ route('index-page') }}">
                    <img src="/images/icons/barocco.svg" alt="Barocco" class="img-fluid w-100">
                </a>
            </div>
            <div class="col-auto ml-auto account-btns d-flex">
                <a href="{{ route('orders.index') }}" class="btn border-right-0 p-0">
                    <img src="/images/icons/account.svg" alt="личный кабинет" class="img-fluid">
                </a><!--
                --><a href="{{ route('favorites.index') }}"
                    class="btn border-right-0 p-0 d-none d-md-flex">
                    <img src="/images/icons/favorites.svg" alt="избранное" class="img-fluid">
                </a><!--
                --><a href="{{ route('cart') }}" class="btn p-0 ml-2 ml-md-0">
                    <div class="position-relative">
                        <img src="/images/icons/cart.svg" alt="корзина" class="img-fluid">
                        <div
                            class="cart-count position-absolute d-flex justify-content-center align-items-center">
                            <span class="js-cart-count">
                                {{ Cart::itemsCount() }}
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            @php
                // $catalogUrl = strpos(url()->current(), 'catalog') !== false ? url()->current() : route('shop');
                $catalogUrl = route('shop');
            @endphp

            <div class="col-12">
                <div class="col-12 navbar-main-menu" id="mainMenu">
                    <div
                        class="navbar-container row justify-content-center align-items-center font-size-15 position-relative">
                        <div class="col-12 col-lg-auto hover-dropdown position-static">
                            <a href="{{ route('shop') }}">КАТАЛОГ</a>
                            <div class="col-12 custom-dropdown-menu">
                                <div class="row h-100 p-4">
                                    <div class="col-4 font-size-16" style="height: 13%; color: #000000;">
                                        <a href="{{ route('shop') }}">Обувь ></a>
                                    </div>
                                    <div class="col-4 font-size-16" style="height: 13%; color: #000000">
                                        <a href="{{ route('shop') }}">Коллекции ></a>
                                    </div>
                                    <div class="col-4"></div>
                                    <div class="col-4 d-flex flex-wrap flex-column font-size-14"
                                        style="height: 87%; column-gap: 6px">
                                        @foreach (App\Models\Category::where('parent_id', 1)->get(['id', 'slug', 'title']) as $category)
                                            <a class="dropdown-menu-item"
                                                href="{{ route('shop', $category) }}">{{ $category->title }}</a>
                                        @endforeach
                                    </div>
                                    <div class="col-4 d-flex flex-wrap flex-column font-size-14"
                                        style="height: 87%">
                                        <a class="dropdown-menu-item" href="/catalog/heel-low">
                                            На низком каблуке
                                        </a>
                                        <a class="dropdown-menu-item" href="/catalog/krossovki">
                                            Спортивная обувь
                                        </a>
                                        <a class="dropdown-menu-item" href="/catalog/eco">
                                            Экокожа
                                        </a>
                                        <a class="dropdown-menu-item" href="/catalog?sort=rating">
                                            Бестселлеры
                                        </a>
                                        <a class="dropdown-menu-item" href="/catalog/heel-stiletto">
                                            На шпильке
                                        </a>
                                        <a class="dropdown-menu-item"
                                            href="/catalog/tufli/tufli-na-spilke">
                                            Свадебная коллекция
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        {{ Banner::getMainMenuCatalog() }}
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="backdrop"></div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <a href="{{ route('static-shops') }}">МАГАЗИНЫ</a>
                        </div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto hover-dropdown position-static">
                            <a href="{{ route('info') }}">ОНЛАЙН ПОКУПКИ</a>
                            <div class="col-12 p-0 custom-dropdown-menu online-shopping-dropdown">
                                <div class="mx-auto py-4">
                                    <div class="d-flex flex-wrap flex-column font-size-16">
                                        @foreach (App\Models\InfoPage::getMenu() as $item)
                                            <a class="dropdown-menu-item"
                                                href="{{ route('info', $item['slug']) }}">
                                                {{ $item['name'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="backdrop"></div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <a href="{{ route('info', 'installments') }}">РАССРОЧКА</a>
                        </div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <a href="{{ route('feedbacks') }}">ОТЗЫВЫ</a>
                        </div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 d-lg-none">
                            <strong class="text-danger">
                                РАСПРОДАЖА
                            </strong>
                        </div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <a href="{{ route('dashboard-card') }}">КАРТА КЛИЕНТА</a>
                        </div>
                        <div class="col-12 d-lg-none">
                            <hr>
                        </div>
                        <div class="col-12 col-lg-auto d-none d-lg-inline-block">
                            <form action="{{ $catalogUrl }}" method="get" class="form-inline">
                                <input type="text" name="search" class="search-input"
                                    value="{{ request()->get('search') }}" placeholder="Поиск">
                                <button type="submit" class="btn p-0 js-search">
                                    <img src="/images/icons/search.svg" alt="Поиск"
                                        class="img-fluid">
                                </button>
                            </form>
                        </div>
                        <div class="col-12 d-block d-lg-none">
                            <div class="row">
                                {{ Currency::getSwitcher() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 collapse" id="search-input">
                <form action="{{ $catalogUrl }}" method="get" class="row">
                    <input type="text" name="search" class="form-control border-0"
                        value="{{ request()->get('search') }}" placeholder="Поиск">
                </form>
            </div>


        </div>


    </div>

</header>
