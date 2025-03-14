@extends('layouts.app')

@section('title', 'Barocco | интернет-магазин модной обуви и кожгалантерии')

@section('content')
    {{-- wrapper close --}}
    </div>

    <div class="p-index">

        {{ Banner::getIndexMain() }}

        <div class="p-index__slider">
            @includeWhen(isset($simpleSliders[0]), 'partials.index.simple-slider', [
                'simpleSlider' => $simpleSliders[0],
            ])
        </div>

        <div class="p-index__slider">
            @includeWhen(isset($simpleSliders[1]), 'partials.index.simple-slider', [
                'simpleSlider' => $simpleSliders[1],
            ])
        </div>

        <div class="p-index__slider">
            @includeWhen(isset($simpleSliders[2]), 'partials.index.simple-slider', [
                'simpleSlider' => $simpleSliders[2],
            ])
        </div>

        <div class="col-md-12 my-4">
            @include('partials.index.imidj-slider')
        </div>

        <div class="row wrapper mb-5">
            {{ Banner::getIndexBottom() }}
        </div>

        <div class="p-index__follow">
            <h2 class="p-index__follow-title">Войдите в мир BAROCCO</h2>
            <p class="p-index__follow-text">Будьте вкурсе событий, коллекций и эксклюзивных новостей.</p>
            <a href="{{ route('dashboard-card') }}" class="p-index__follow-btn">
                Присоединиться
            </a>
        </div>

        <div class="wrapper">
            <div class="p-index__about">
                <div class="p-index__about-img">
                    <img src="/images/index/about.png" alt="Barocco" loading="lazy" decoding="async">
                </div>
                <div class="p-index__about-info">
                    <div class="p-index__about-text">
                        <p>
                            В нашем каталоге – обувь и аксессуары в рассрочку с доставкой по Минску и
                            Беларуси. Уже не
                            первый
                            год мы
                            привозим в Беларусь продукцию VITACCI.
                        </p>
                        <p>
                            Если вы знакомы не понаслышке с дизайнерской обувью, то хорошо помните то
                            ощущение легкости,
                            невесомости
                            и комфорта, что она дарит.
                        </p>
                    </div>
                    <ul class="p-index__about-list">
                        <li>
                            <span>Примерка по Беларуси</span>
                            <span>на пункте выдачи или курьером</span>
                        </li>
                        <li>
                            <span>Рассрочка</span>
                            <span>на 2 месяца без предоплаты</span>
                        </li>
                        <li>
                            <span>Оплата при получении</span>
                            <span>наличными или картой</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('includes.instagram-block')

        <div class="p-index__text col-12">
            <p>BAROCCO.BY - ведущий интернет магазин по продаже обуви из натуральной кожи и замши в
                Беларуси.</p>
            <p>В нашем интернет магазине представлены только качественные модели обуви, которые отвечают
                современным тенденциям моды.</p>
            <ul class="mb-4">
                <li>Мы работаем с 2015 года</li>
                <li>Гарантия на всю продукцию</li>
                <li>Широкий размерный ряд</li>
                <li>100% оригинальные бренды</li>
            </ul>
            <p>BAROCCO.BY - единственный официальный интернет-магазин бренда BAROCCO в Беларуси, России и
                Казахстане. БАРОККО - это итальянский дизайн воплощенный в изделиях из натуральных
                материалов, таких как кожа, мех и замша.</p>
            <p>В каталоге Вы найдете женские коллекции обуви согласно последним веяниям моды.</p>
            <p class="mb-4">Обувь BAROCCO идеальна в носке и подойдёт каждому, так как она подобрана с
                учетом
                особенностей строения женской ступни.</p>
            <p class="mb-4">Также BAROCCO.BY является официальным поставщиком именитых обувных брендов
                VITACCI, Basconi,
                Sasha Fabiani. Благодаря многолетнему сотрудничеству с производителями у нас лучшие цены в
                Беларуси.</p>
            <p>Мы предлагаем широкий ассортимент обуви для каждого сезона и случая:</p>
            <ul class="mb-4">
                <li>для лета босоножки, сандалии и сабо</li>
                <li>для демисезона и зимы: ботильоны, ботинки, сапоги и ботфорты</li>
                <li>спортивные и повседневные кроссовки, слипоны и кеды</li>
                <li>офисные туфли и лоферы</li>
                <li>вечерние модели туфель, ботильон и босоножек для юбилеев, свадеб, свиданий и др.
                    торжественных случаев</li>
            </ul>
            <p>Барокко бай - это не только ассортимент, но и сервис.</p>
            <p>Для Беларуси доступна рассрочка, курьерская доставка и примерка.</p>
            <p class="mb-4">В Россию возможна доставка до отделения СДЭК или EMS</p>
            <p>У нас можно купить обувь из натуральных материалов по приемлемым ценам.</p>
            <p class="mb-4">Покупайте качественную обувь - быстро и надежно с BAROCCO.BY!</p>
            <p>Номер контактного телефона и адрес электронной почты лица, уполномоченного продавцом
                рассматривать обращения покупателей о нарушении их прав, предусмотренных законодательством о
                защите прав потребителей</p>
            <p>+375 29 522-77-22, info@barocco.by</p>
            <p>Номер контактного телефона работников Брестского городского исполнительного комитета,
                уполномоченных рассматривать обращения покупателей в соответствии с законодательством об
                обращениях граждан и юридических лиц</p>
            <p>+375 162 21-04-75</p>
        </div>

    </div>

    <div class="row wrapper justify-content-center">
        {{-- wrapper open --}}
    @endsection
