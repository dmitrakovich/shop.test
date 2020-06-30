<header class="header pt-3 mb-2 pt-lg-4 mb-lg-3">
    <div class="container-fluid">
        <div class="row wrapper align-items-center">
            <button class="btn navbar-toggler js-show-main-menu" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>

            


            <div class="account-btns">
                <button class="btn p-0 d-inline-block d-lg-none js-mobile-search" type="button" data-toggle="collapse" data-target="#collapseExample">
                    <img src="/images/icons/search.svg" alt="Поиск" class="img-fluid">
                </button>
            </div>
            <div class="col-auto d-none d-lg-block">
                <p style="line-height: 1.15rem;"><small>
                    Ежедневно&nbsp;с 10:00&nbsp;до&nbsp;22:00<br>
                    +375&nbsp;44&nbsp;728&nbsp;66&nbsp;06<br>
                    Viber / Telegram / What’s App
                </small></p>
                
            </div>
            <div class="col-auto ml-auto">
                <a href="{{ route('index-page') }}">
                    <h2 class="text-uppercase m-0">{{ config('app.name') }}</h2>
                </a>
            </div>
            <div class="col-auto ml-auto account-btns d-flex">
                <a href="{{ route('dashboard-orders') }}" class="btn border-right-0 p-0">
                    <img src="/images/icons/account.svg" alt="личный кабинет" class="img-fluid">
                </a><!--
                --><button class="btn border-right-0  p-0 d-none d-md-inline-block">
                    <img src="/images/icons/favorites.svg" alt="избранное" class="img-fluid">
                </button><!--
                --><button class="btn p-0 ml-2 ml-md-0">
                    <div class="position-relative">
                        <img src="/images/icons/cart.svg" alt="корзина" class="img-fluid">
                        <div class="cart-count position-absolute d-flex justify-content-center align-items-center">
                            <span class="js-cart-count">4</span>
                        </div>
                    </div>
                </button>
            </div>

            
            <div class="col-12 navbar-main-menu" id="mainMenu">
                <div class="row justify-content-center align-items-center text-uppercase font-size-15 position-relative">
                    <div class="col-12 col-lg-auto hover-dropdown">
                        <a href="{{ route('catalog') }}">Каталог</a>
                        <div class="col-12 dropdown-menu position-absolute">
                            <a class="dropdown-item" href="#">Лоферы</a>
                            <a class="dropdown-item" href="#">Балетки</a>
                            <a class="dropdown-item" href="#">Сабо</a>
                            <a class="dropdown-item" href="#">Ботинки</a>
                            <a class="dropdown-item" href="#">Туфли</a>
                            <a class="dropdown-item" href="#">Сандали</a>
                            <a class="dropdown-item" href="#">Босоножки</a>
                            <a class="dropdown-item" href="#">Сапоги</a>
                            <a class="dropdown-item" href="#">Ботильоны</a>
                            <a class="dropdown-item" href="#">Слипоны</a>
                            <a class="dropdown-item" href="#">Кеды</a>
                            <a class="dropdown-item" href="#">Эспадрильи</a>
                        </div>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('static-shops') }}">Магазины</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto hover-dropdown">
                        <a href="{{ route('static-instruction') }}">Онлайн покупки</a>
                        <div class="col-12 dropdown-menu position-absolute">
                            @include('includes.static-pages-menu')
                        </div>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('static-installments') }}">Рассрочка</a>
                    </div>
                    <div class="col-12 d-lg-none"><hr></div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('reviews') }}">Отзывы</a>
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
                        <div class="form-inline">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <button class="btn p-0 js-search">
                                <img src="/images/icons/search.svg" alt="Поиск" class="img-fluid">
                            </button>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-12 collapse" id="collapseExample">
                <div class="row">
                    <input type="text" class="form-control border-0" placeholder="Поиск">
                </div>
            </div>


        </div>


    </div>

</header>