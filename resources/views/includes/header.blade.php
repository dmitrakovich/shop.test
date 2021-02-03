<header class="header pt-3 mb-2 pt-lg-4 mb-lg-3">
    <div class="container-fluid">
        <div class="row wrapper align-items-center">
            <button class="btn navbar-toggler js-show-main-menu" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>


            <div class="account-btns">
                <button class="btn p-0 d-inline-block d-lg-none js-mobile-search" type="button" data-toggle="collapse" data-target="#search-input">
                    <img src="/images/icons/search.svg" alt="Поиск" class="img-fluid">
                </button>
            </div>
            <div class="col-auto d-none d-lg-block">
                <p style="line-height: 1.15rem;"><small>
                    Ежедневно&nbsp;с 10:00&nbsp;до&nbsp;22:00<br>
                    +375&nbsp;(29)&nbsp;179&nbsp;37&nbsp;90<br>
                    Viber / Telegram / What’s App
                </small></p>

            </div>
            <div class="col col-sm-auto ml-auto">
                <a href="{{ route('index-page') }}">
                    <img src="/images/icons/barocco.svg" alt="" class="img-fluid w-100">
                </a>
            </div>
            <div class="col-auto ml-auto account-btns d-flex">
                <a href="{{ route('orders.index') }}" class="btn border-right-0 p-0">
                    <img src="/images/icons/account.svg" alt="личный кабинет" class="img-fluid">
                </a><!--
                --><button class="btn border-right-0  p-0 d-none d-md-inline-block">
                    <img src="/images/icons/favorites.svg" alt="избранное" class="img-fluid">
                </button><!--
                --><a href="{{ route('cart') }}" class="btn p-0 ml-2 ml-md-0">
                    <div class="position-relative">
                        <img src="/images/icons/cart.svg" alt="корзина" class="img-fluid">
                        <div class="cart-count position-absolute d-flex justify-content-center align-items-center">
                            <span class="js-cart-count">
                                {{ Cart::itemsCount() }}
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            @php
                $catalogUrl = strpos(url()->current(), 'catalog') !== false ? url()->current() : route('shop');
            @endphp


            <div class="col-12 navbar-main-menu" id="mainMenu">
                <div class="row justify-content-center align-items-center text-uppercase font-size-15 position-relative">
                    <div class="col-12 col-lg-auto hover-dropdown">
                        <a href="{{ route('shop') }}">Каталог</a>
                        <div class="col-12 dropdown-menu position-absolute" style="margin-top: -1px;">
                            @foreach ($categories as $category)
                                <a class="dropdown-item" href="{{ route('shop', $category) }}">{{ $category->title }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('static-shops') }}">Магазины</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto hover-dropdown">
                        <a href="{{ route('info') }}">Онлайн покупки</a>
                        <div class="col-12 dropdown-menu position-absolute" style="margin-top: -1px;">
                            @include('includes.static-pages-menu')
                        </div>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('info', 'installments') }}">Рассрочка</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('feedbacks') }}">Отзывы</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 d-lg-none">
                        <strong class="text-danger">
                            Распродажа
                        </strong>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('dashboard-card') }}">Карта клиента</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto d-none d-lg-inline-block">
                        <form action="{{ $catalogUrl }}" method="get" class="form-inline">
                            <input type="text" name="search" class="search-input" value="{{ request()->get('search') }}" placeholder="Поиск">
                            <button type="submit" class="btn p-0 js-search">
                                <img src="/images/icons/search.svg" alt="Поиск" class="img-fluid">
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <div class="col-12 collapse" id="search-input">
                <form action="{{ $catalogUrl }}" method="get" class="row">
                    <input type="text" name="search" class="form-control border-0" value="{{ request()->get('search') }}" placeholder="Поиск">
                </form>
            </div>


        </div>


    </div>

</header>
