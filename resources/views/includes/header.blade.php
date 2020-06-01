<header class="header">
    <div class="container-fluid">
        <div class="row wrapper align-items-center">
            <button class="btn navbar-toggler" type="button" data-toggle="collapse" data-target="#mainMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="col-auto d-none d-lg-block">
                <p style="line-height: 1.15rem;"><small>
                    Ежедневно&nbsp;с 10:00&nbsp;до&nbsp;22:00<br>
                    +375&nbsp;44&nbsp;728&nbsp;66&nbsp;06<br>
                    Viber / Telegram / What’s App
                </small></p>
                
            </div>
            <div class="col-auto ml-md-auto">
                <a href="{{ route('index-page') }}">
                    <h2 class="text-uppercase m-0">{{ config('app.name') }}</h2>
                </a>
            </div>
            <div class="col-auto ml-auto">
                личный кабинет
                избранное
                корзина
            </div>
            <div class="col-8">
                <div class="row justify-content-center">
                    <div class="col">Каталог</div>
                    <div class="col">Магазины</div>
                    <div class="col">Как заказать</div>
                    <div class="col">Рассрочка</div>
                    <div class="col">Отзывы</div>
                    <div class="col">Карта клиента</div>
                    <div class="col">Контакты</div>
                    <div class="col">Поиск</div>
                </div>
            </div>
        </div>





        <div class="collapse navbar-collapse navbar-main-menu" id="mainMenu">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
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
            </ul>




            <div class="row justify-content-center">
                <div class="col-12 col">Каталог</div>
                <div class="col-12 col">Магазины</div>
                <div class="col-12 col">Как заказать</div>
                <div class="col-12 col">Рассрочка</div>
                <div class="col-12 col">Отзывы</div>
                <div class="col-12 col">Карта клиента</div>
                <div class="col-12 col">Контакты</div>
                <div class="col-12 col">Поиск</div>
            </div>








        </div>








    </div>




    {{-- <section class="extras alternative">
        <nav class="container extras-items">
            <span>Регистрация на <a href="https://phprussia.ru" target="_blank" rel="nofollow">PHPRussia 2020</a> уже открыта:</span>
            <a href="https://phprussia.ru/moscow/2020/abstracts" target="_blank" rel="nofollow">Доклады</a>
            <span>·</span>
            <a href="https://phprussia.ru/moscow/2020/meetups" target="_blank" rel="nofollow">Митапы</a>
            <span>·</span>
            <a href="https://phprussia.ru/moscow/2020#prices" target="_blank" rel="nofollow">Цены</a>
        </nav>
    </section>

    

    <section class="menu">
        <section class="container menu-content">
            <a href="https://laravel.su" class="logo">
                <h1>Laravel Framework Russian Community</h1>
            </a>

            <aside class="menu-aside">
                

                <nav class="menu-items">
                    
                    <a href="https://laravel.su" class="">Главная</a>
                    <a href="https://laravel.su/docs" class="active">Документация</a>
                    <a href="https://laravel.su/status" class="">Перевод</a>
                    <span>Статьи</span>
                    <span>Пакеты</span>
                </nav>
            </aside>
        </section>
    </section> --}}




</header>