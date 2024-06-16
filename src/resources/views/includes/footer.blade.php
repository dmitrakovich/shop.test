<footer class="inc-footer">
    <div class="container-fluid">
        <div class="row wrapper">
            <div class="inc-footer__main col-12 col-lg-4 col-xl-3">
                <a href="{{ route('index-page') }}" class="inc-footer__logo">
                    <img src="/images/icons/barocco.svg" alt="Barocco" loading="lazy" decoding="async">
                </a>
                <p>
                    ООО «БароккоСтайл», УНП 291711523,
                    РЕСПУБЛИКА БЕЛАРУСЬ, 224030, г. Брест, ул. Буденного, 17-1
                    Интернет магазин внесен в торговый реестр
                    Республики Беларусь 4 января 2022 года
                    Свидетельство о государственной регистрации № 291711523
                    выдано Администрацией Ленинского р-на г. Бреста
                </p>
            </div>
            <div class="inc-footer__info col-12 col-lg-8 col-xl-9">
                <div class="row">
                    <div class="col-12 col-lg-4 col-xl-6">
                        <div class="row">
                            <div class="col-12 col-xl-6 mb-3">
                                <div class="inc-footer__info-title collapsed" data-toggle="collapse"
                                    data-target="#js-footerWorkTime" aria-controls="js-footerWorkTime">
                                    Время работы
                                </div>
                                <div class="inc-footer__info-text collapse" id="js-footerWorkTime">
                                    с 08.00 до 21.00<br>
                                    ежедневно
                                </div>
                            </div>
                            <div class="col-12 col-xl-6 mb-3">
                                <div class="inc-footer__info-title collapsed" data-toggle="collapse"
                                    data-target="#js-footerInfo" aria-controls="js-footerInfo">
                                    Информация
                                </div>
                                <div class="inc-footer__info-text collapse" id="js-footerInfo">
                                    <nav>
                                        <ul>
                                            <li>
                                                <a href="{{ route('info.terms') }}"
                                                    title="Публичная оферта">
                                                    Публичная оферта
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('info.policy') }}"
                                                    title="Политика конфиденциальности">
                                                    Политика конфиденциальности
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('info', 'certificate') }}"
                                                    title="Сертификаты">
                                                    Сертификаты
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 col-xl-3 mb-3">
                        <div class="inc-footer__info-title collapsed" data-toggle="collapse"
                            data-target="#js-footerContacts" aria-controls="js-footerContacts">
                            Контакты</div>
                        <div class="inc-footer__info-text collapse" id="js-footerContacts">
                            <ul>
                                <li><a href="tel:+375291793790">+375 (29) 179-37-90</a></li>
                                <li><a href="tel:+375295227722">+375 (29) 522-77-22</a></li>
                                <li><a href="tel:88001007769">8-800-100-77-69(РФ)</a></li>
                                <li><a href="mailto:info@barocco.by">info@barocco.by</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 col-xl-3 mb-3">
                        <div class="inc-footer__info-title collapsed" data-toggle="collapse"
                            data-target="#js-footerPayments" aria-controls="js-footerPayments">
                            Способы оплаты</div>
                        <div class="inc-footer__info-text collapse" id="js-footerPayments">
                            <img src="/images/footer/payments-all.png" class="img-fluid" alt="Оплата"
                                decoding="async" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
