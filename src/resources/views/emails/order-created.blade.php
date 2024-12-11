<html>

<head>
    <title>Заказ</title>
</head>

<body style="margin: 0px; padding: 0px; background: rgb(255, 255, 255); cursor: auto;">
    <div
        style="display:none;font-size:1px;color:#FFFFFF;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
        Здравствуйте, {{ $order->first_name }}. Спасибо за заказ!</div>
    <table border="0" cellpadding="0" cellspacing="0" style="border:none; border-collapse:collapse; border-spacing:0; "
        width="100%">
        <tbody>
            <tr>
                <td align="center" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0"
                        style="border:none; border-collapse:collapse; border-spacing:0;" width="600">
                        <tbody>
                            <tr>
                                <td height="25"></td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">

                                    {{-- HEADER --}}
                                    <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 0 auto;"
                                        width="600">
                                        <tbody>
                                            <tr>
                                                {{-- Phones --}}
                                                <td align="left" style="letter-spacing: -0.1ex;" valign="middle">
                                                    <a href="tel:+375291793790"
                                                        style="font-family:Roboto, Verdana; font-size:12px; color:#111111 !important; text-decoration:none;">+375
                                                        (29) 179 37 90</a> <span
                                                        style="font-family:Roboto, Verdana; font-size:10px; color:#111111 !important;">(РБ
                                                        и другие страны)</span><br>
                                                    <a href="tel:88001007769"
                                                        style="font-family:Roboto, Verdana; font-size:12px; color:#111111 !important; text-decoration:none;">8
                                                        (800) 100 77 69</a> <span
                                                        style="font-family:Roboto, Verdana; font-size:10px; color:#111111 !important;">(бесплатно
                                                        для РФ)</span>
                                                </td>
                                                <td align="center" valign="middle" width="25">
                                                    <a href="https://wa.me/375447286606"
                                                        style="border: none; text-decoration: none;" target="_blank"
                                                        title="WhatsApp">
                                                        <img alt="WhatsApp"
                                                            src="https://barocco.by/images/emails/whatsapp.png"
                                                            width="20">
                                                    </a>
                                                </td>
                                                <td align="center" valign="middle" width="10"></td>
                                                <td align="center" valign="middle" width="25">
                                                    <a href="viber://add?number=375447286606"
                                                        style="border: none; text-decoration: none;" target="_blank"
                                                        title="Viber">
                                                        <img alt="Viber"
                                                            src="https://barocco.by/images/emails/viber.png"
                                                            width="20">
                                                    </a>
                                                </td>
                                                <td align="center" valign="middle" width="10"></td>
                                                <td align="center" valign="middle" width="25">
                                                    <a href="https://t.me/barocco_by"
                                                        style="border: none; text-decoration: none;" target="_blank"
                                                        title="Telegram">
                                                        <img alt="Telegram"
                                                            src="https://barocco.by/images/emails/telegram.png"
                                                            width="20">
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 0 auto;"
                                        width="600">
                                        <tbody>
                                            <tr>
                                                <td colspan="13" height="7"></td>
                                            </tr>
                                            <tr>
                                                <td width="30"></td>
                                                <td width="170"></td>
                                                <td width="15"></td>
                                                <td align="center" valign="top">
                                                    <a href="https://barocco.by/"
                                                        style="border: none; text-decoration: none;" target="_blank"
                                                        title="Модны Бай">
                                                        <img alt="barocco.by"
                                                            src="https://barocco.by/images/emails/barocco.png"
                                                            width="300">
                                                    </a>
                                                </td>
                                                <td width="15"></td>
                                                <td width="170"></td>
                                                <td width="30"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="13" height="7"></td>
                                            </tr>
                                            <tr>
                                                <td width="30"></td>
                                                <td align="center" valign="middle" width="170">
                                                    <a href="https://barocco.by/catalog"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">КАТАЛОГ</a>
                                                </td>
                                                <td width="15"></td>
                                                <td align="center" valign="middle" width="170">
                                                    <a href="https://barocco.by/online-shopping/installments"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">РАССРОЧКА</a>
                                                </td>
                                                <td width="15"></td>
                                                <td align="center" valign="middle" width="170">
                                                    <a href="https://barocco.by/feedbacks"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">ОТЗЫВЫ</a>
                                                </td>
                                                <td width="30"></td>
                                            </tr>
                                        </tbody>
                                    </table>


                                    {{-- CONTENT --}}
                                    {{-- Приветствие --}}
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        style="border:none; border-collapse:collapse; border-spacing:0;"
                                        width="600">
                                        <tbody>
                                            <tr>
                                                <td colspan="3" height="20"></td>
                                            </tr>
                                            <tr>
                                                <td width="45"></td>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:18px; color:#070707; font-weight:bold;"
                                                    valign="middle">Здравствуйте, {{ $order->first_name }}!</td>
                                                <td width="45"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" height="20"></td>
                                            </tr>
                                            <tr>
                                                <td width="45"></td>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#111111;"
                                                    valign="middle">Спасибо за заказ! После обработки менеджер свяжется
                                                    с Вами в рабочее время пн-пт с 8.00 по 17.00</td>
                                                <td width="45"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" height="30"></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    {{-- MESSAGE --}}

                                    <table style="margin: 0 auto 0 auto;" border="0" cellpadding="0"
                                        cellspacing="0" width="600">
                                        <tbody>
                                            <tr>
                                                <td height="20"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="600" border="0" cellpadding="0" cellspacing="0"
                                        style="border:none; border-collapse:collapse; border-spacing:0; margin: 0 auto;">
                                        <tbody>
                                            <tr>
                                                <td height="10" style="border-top:2px solid #DDDDDD;"></td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="font-family:Roboto, Verdana; font-size:18px; color:#222222; font-weight: bold;">
                                                    Заказ №{{ $order->id }}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="3"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                                                    Розничный заказ на
                                                    {{ DeclensionNoun::make($order->getItemsCount(), 'товар') }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td height="10"></td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                                                    ПОЛУЧАТЕЛЬ
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="320"
                                                    style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                    <b>ФИО</b>: {{ $order->last_name }} {{ $order->first_name }}
                                                    {{ $order->patronymic_name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="320"
                                                    style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                    <b>Email</b>: {{ $order->email }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="320"
                                                    style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                    <b>Телефон</b>: {{ $order->phone }}
                                                </td>
                                            </tr>
                                            @if (!empty($order->country))
                                                <tr>
                                                    <td width="320"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                        <b>Страна</b>: {{ $order->country->name }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($order->city))
                                                <tr>
                                                    <td width="320"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                        <b>Город</b>: {{ $order->city }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td width="320"
                                                    style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                    <b>Адрес</b>: {{ $order->user_addr }}
                                                </td>
                                            </tr>
                                            @if (!empty($order->delivery))
                                                <tr>
                                                    <td width="320"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                        <b>Способ доставки</b>: {{ $order->delivery->name }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($order->payment))
                                                <tr>
                                                    <td width="320"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                        <b>Способ оплаты</b>: {{ $order->payment->name }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($order->comment))
                                                <tr>
                                                    <td width="320"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                                        <b>Комментарий</b>: {{ $order->comment }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td colspan="3" height="20"></td>
                                            </tr>

                                        </tbody>
                                    </table>

                                    <table width="600" style="margin: 0 auto;" cellspacing="0" cellpadding="0"
                                        border="0">
                                        <tbody>

                                            <tr>
                                                <td width="10px"></td>
                                                <td colspan="3" valign="middle" align="center" height="40"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">
                                                    Модель</td>
                                                <td width="150px" valign="middle" align="center" height="40"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">
                                                    Скидка</td>
                                                <td width="120px" valign="middle" align="center" height="40"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">
                                                    Стоимость</td>
                                                <td width="10px" valign="middle" height="40" align="left">
                                                </td>
                                            </tr>

                                            @foreach ($order->data as $item)
                                                <tr>
                                                    <td colspan="7"
                                                        style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD;"
                                                        height="10px"></td>
                                                </tr>
                                                <tr>
                                                    <td style="border-left: 1px solid #DDDDDD" width="10px"></td>
                                                    <td width="100px" valign="top">
                                                        <a href="{{ url($item->product->getUrl()) }}"
                                                            style="border: none; text-decoration: none;"
                                                            title="{{ url($item->product->getUrl()) }}"
                                                            target="_blank">
                                                            <img alt="{{ $item->product->getFullName() }}"
                                                                src="{{ $item->product->getFirstMediaUrl('default', 'thumb') }}"
                                                                width="100" style="display: block;">
                                                        </a>
                                                    </td>
                                                    <td width="10px"></td>
                                                    <td width="200px" valign="middle" align="left"
                                                        style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                                                        <a href="{{ url($item->product->getUrl()) }}"
                                                            target="_blank">{{ $item->product->getFullName() }}</a><br>
                                                        Размер: <b>{{ $item->size->name }}</b><br>
                                                        Код: <b>{{ $item->product_id }}</b>
                                                    </td>
                                                    <td width="150px" valign="middle" align="center"
                                                        style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                                                        @if ($item->discount > 0)
                                                            <span
                                                                style="color: #C0976B;">{{ $item->discount }}%</span>({!! Currency::format($item->old_price - $item->current_price, $order->currency) !!})<br>
                                                        @endif
                                                    </td>
                                                    <td width="120px" valign="middle" align="center"
                                                        style="font-family:Roboto, Verdana; font-size:16px; color:#C0976B; font-weight: bold;">
                                                        @if ($item->old_price > $item->current_price)
                                                            <span
                                                                style="color: #777777; text-decoration: line-through; font-size:14px;">{!! Currency::format($item->old_price, $order->currency) !!}</span><br>
                                                        @endif
                                                        {!! Currency::format($item->current_price, $order->currency) !!}
                                                    </td>
                                                    <td style="border-right: 1px solid #DDDDDD" width="10px"
                                                        valign="top" align="center"></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="7"
                                                        style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD;"
                                                        height="7px"></td>
                                                </tr>


                                                <tr>
                                                    <td colspan="7"
                                                        style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD;"
                                                        height="7px"></td>
                                                </tr>
                                            @endforeach


                                            <tr>
                                                <td colspan="7" height="15px"></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="600" style="margin: 0 auto; border-radius: 2px;" cellspacing="0"
                                        cellpadding="0" border="0">
                                        <tbody>
                                            <tr colspan="5" height="10px;"></tr>
                                            <tr>
                                                <td width="100"></td>
                                                <td width="300" align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                                                    Стоимость моделей <br>
                                                    (без скидки)
                                                </td>
                                                <td width="20"></td>
                                                <td width="170" align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                                                    {!! Currency::format($order->getMaxItemsPrice(), $order->currency) !!}
                                                </td>
                                                <td width="10px"></td>
                                            </tr>

                                            <tr>
                                                <td width="100"></td>
                                                <td width="300" align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                                                    Скидка
                                                </td>
                                                <td width="20px"></td>
                                                <td align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                                                    {!! Currency::format($order->getMaxItemsPrice() - $order->getItemsPrice(), $order->currency) !!}
                                                </td>
                                                <td width="10px"></td>
                                            </tr>

                                            <tr>
                                                <td colspan="5" height="10"></td>
                                            </tr>
                                            <tr>
                                                <td width="200"></td>
                                                <td colspan="3" height="1" style="background-color: #DDDDDD;">
                                                </td>
                                                <td width="10px"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="10"></td>
                                            </tr>
                                            <tr>
                                                <td width="100"></td>
                                                <td width="300" align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:20px; color:#222222; font-weight: bold;">
                                                    ИТОГО</td>
                                                <td width="20px"></td>
                                                <td align="right" valign="middle"
                                                    style="font-family:Roboto, Verdana; font-size:20px; color:#C0976B; font-weight: bold;">
                                                    {!! Currency::format($order->getTotalPrice(), $order->currency) !!}</td>
                                                <td width="10px"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="10px"></td>
                                            </tr>
                                        </tbody>
                                    </table>


                                    {{-- MESSAGE END --}}

                                    {{-- FOOTER --}}
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        style="margin: 0 auto 0 auto;" width="600">
                                        <tbody>
                                            <tr>
                                                <td height="20"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        style="margin: 0 auto 0 auto;" width="600">
                                        <tbody>
                                            <tr>
                                                <td align="center" valign="middle" width="30"></td>
                                                <td align="center" valign="middle" width="180"><a
                                                        href="https://barocco.by/online-shopping/instruction"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">КАК
                                                        ЗАКАЗАТЬ</a></td>
                                                <td align="center" valign="middle" width="180"><a
                                                        href="https://barocco.by/online-shopping/delivery"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">ДОСТАВКА
                                                        И ОПЛАТА</a></td>
                                                <td align="center" valign="middle" width="180"><a
                                                        href="https://barocco.by/online-shopping/return"
                                                        style="font-family:Roboto, Verdana; font-size:14px; color:#111111 !important; text-decoration:none; font-weight: bold;">ВОЗВРАТ</a>
                                                </td>
                                                <td align="center" valign="middle" width="30"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="20"
                                                    style="border-bottom: 1px solid #CCCCCC;"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="30"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        style="margin: 0 auto 0 auto;" width="600">
                                        <tbody>
                                            <tr>
                                                <td align="center" valign="middle" width="255"></td>
                                                <td align="right"
                                                    style="font-family:Roboto, Verdana; font-size:14px; color:#070707 !important; font-weight: bold;"
                                                    valign="middle" width="50">Подпишитесь</td>
                                                <td align="center" valign="middle" width="10"></td>
                                                <td align="center" valign="middle" width="30"><a
                                                        href="https://www.instagram.com/barocco.by/"
                                                        style="border: none; text-decoration: none;" target="_blank"
                                                        title="Instagram"><img alt="Instagram"
                                                            src="https://barocco.by/images/icons/instagram.png"></a>
                                                </td>
                                                <td align="center" valign="middle" width="255"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="10"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0"
                                        style="margin: 0 auto 0 auto;" width="600">
                                        <tbody>
                                            <tr>
                                                <td height="30"></td>
                                            </tr>
                                            <tr>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important; font-weight: bold;"
                                                    valign="middle">barocco.by, 2015-2021</td>
                                            </tr>
                                            <tr>
                                                <td height="10"></td>
                                            </tr>
                                            <tr>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important; font-weight: normal;"
                                                    valign="middle">РЕСПУБЛИКА БЕЛАРУСЬ, 224030, г. Брест, ул.
                                                    Советская, 72</td>
                                            </tr>
                                            <tr>
                                                <td height="30"></td>
                                            </tr>
                                            <tr>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important; font-weight: normal;"
                                                    valign="middle">Вы получили это письмо, так как подписаны на
                                                    рассылку интернет-магазина barocco.by</td>
                                            </tr>
                                            <tr>
                                                <td height="10"></td>
                                            </tr>
                                            <tr>
                                                <td align="center"
                                                    style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important; font-weight: normal;"
                                                    valign="middle">Чтобы письмо не попало в спам добавьте <a
                                                        href="mailto:info@barocco.by"
                                                        style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important;">info@barocco.by</a>
                                                    в адресную книгу.</td>
                                            </tr>

                                            {{-- <tr>
                      <td height="10"></td>
                    </tr>
                    <tr>
                      <td align="center" style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important; font-weight: normal;" valign="middle">Чтобы отписаться от рассылки, перейдите по <a href="javascript:;" style="font-family:Roboto, Verdana; font-size:12px; color:#686464 !important;" target="_self">ссылке</a></td>
                    </tr> --}}

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="25"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
