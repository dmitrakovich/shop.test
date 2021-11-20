<html>
<head>
<title>Заказ</title>
</head>
<body style="margin: 0px; padding: 0px; background: rgb(255, 255, 255); cursor: auto;">
<div style="display:none;font-size:1px;color:#FFFFFF;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">Здравствуйте, {{ $order->first_name }}. Спасибо за заказ!</div>
<table border="0" cellpadding="0" cellspacing="0" style="border:none; border-collapse:collapse; border-spacing:0; " width="100%">
  <tbody>
    <tr>
      <td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" style="border:none; border-collapse:collapse; border-spacing:0;" width="600">
          <tbody>
            <tr>
              <td height="25"></td>
            </tr>
            <tr>
              <td align="center" valign="top">


                {{-- Copy /resources/views/emails/order-created.blade.php !!!! --}}


				<table width="600" border="0" cellpadding="0" cellspacing="0" style="border:none; border-collapse:collapse; border-spacing:0; margin: 0 auto;"><tbody>

					<tr>
                        <td style="font-family:Roboto, Verdana; font-size:18px; color:#222222; font-weight: bold;">Заказ №{{ $order->id }}</td>
					</tr>

					<tr>
                        <td colspan="3" style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                            Розничный заказ на {{ DeclensionNoun::make($order->getItemsCount(), 'товар') }}
                        </td>
					</tr>

					<tr><td height="10"></td></tr>

                    <tr>
                        <td style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                            ПОЛУЧАТЕЛЬ
                        </td>
                    </tr>
                    <tr>
                        <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                            <b>ФИО</b>: {{ $order->first_name }}
                        </td>
                    </tr>
                    <tr>
                        <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                            <b>Email</b>: {{ $order->email }}
                        </td>
                    </tr>
                    <tr>
                        <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                            <b>Телефон</b>: {{ $order->phone }}
                        </td>
                    </tr>
                    <tr>
                        <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                            <b>Адрес</b>: {{ $order->user_addr }}
                        </td>
                    </tr>
                    @if (!empty($order->delivery))
                        <tr>
                            <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                <b>Способ доставки</b>: {{ $order->delivery->name }}
                            </td>
                        </tr>
                    @endif
                    @if (!empty($order->payment))
                        <tr>
                            <td width="320" style="font-family:Roboto, Verdana; font-size:14px; color:#222222;">
                                <b>Способ оплаты</b>: {{ $order->payment->name }}
                            </td>
                        </tr>
                    @endif
                    <tr><td colspan="3" height="20"></td></tr>

				</tbody></table>

				<table width="600" style="margin: 0 auto;" cellspacing="0" cellpadding="0" border="0"><tbody>

					<tr>
						<td width="10px"></td>
						<td colspan="3" valign="middle" align="center" height="40" style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">Модель</td>
						<td width="150px" valign="middle" align="center" height="40" style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">Скидка</td>
						<td width="120px" valign="middle" align="center" height="40" style="font-family:Roboto, Verdana; font-size:16px; color:#FFFFFF; font-weight: bold; text-transform: uppercase; background-color: #222222;">Стоимость</td>
						<td width="10px" valign="middle" height="40" align="left"></td>
					</tr>

                    @foreach ($order->data as $item)
                        <tr>
                            <td colspan="7" style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD;" height="10px"></td>
                        </tr>
                        <tr>
                            <td style="border-left: 1px solid #DDDDDD" width="10px"></td>
                            <td width="100px" valign="top">
                                <a href="{{ $item->product->getUrl() }}" style="border: none; text-decoration: none;" title="{{ $item->product->getUrl() }}" target="_blank">
                                    <img alt="{{ $item->product->getFullName() }}" src="{{ $item->product->getFirstMedia()->getUrl('thumb') }}" width="100" style="display: block;">
                                </a>
                            </td>
                            <td width="10px"></td>
                            <td width="200px" valign="middle" align="left" style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                                <a href="{{ $item->product->getUrl() }}" target="_blank">{{ $item->product->getFullName() }}</a><br>
                                Размер: <b>{{ $item->size->name }}</b><br>
                                {{-- {{PROMOCODE}} --}}
                            </td>
                            <td width="150px" valign="middle" align="center" style="font-family:Roboto, Verdana; font-size:16px; color:#222222;">
                                @if ($item->discount > 0)
                                    <span style="color: #C0976B;">{{ $item->discount }}%</span>({!! Currency::format($item->old_price - $item->current_price, $order->currency) !!})<br>
                                @endif
                                {{-- <span style="color: #C0976B;">{{MODEL_PROMO_PERCENT}}</span> ({{MODEL_PROMO_SUMM}}) --}}
                            </td>
                            <td width="120px" valign="middle" align="center" style="font-family:Roboto, Verdana; font-size:16px; color:#C0976B; font-weight: bold;">
                                @if ($item->old_price > $item->current_price)
                                    <span style="color: #777777; text-decoration: line-through; font-size:14px;">{!! Currency::format($item->old_price, $order->currency) !!}</span><br>
                                @endif
                                {!! Currency::format($item->current_price, $order->currency) !!}
                            </td>
                            <td style="border-right: 1px solid #DDDDDD" width="10px" valign="top" align="center"></td>
                        </tr>

                        <tr><td colspan="7" style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD;" height="7px"></td></tr>


                        <tr><td colspan="7" style="border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD;" height="7px"></td></tr>

                    @endforeach


                    <tr><td colspan="7" height="15px"></td></tr>
				</tbody></table>

				<table width="600" style="margin: 0 auto; border-radius: 2px;" cellspacing="0" cellpadding="0" border="0"><tbody>
                    <tr colspan="5" height="10px;"></tr>
                    <tr>
                        <td width="100"></td>
                        <td width="300" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                            Стоимость моделей <br>
                            (без скидки)
                        </td>
                        <td width="20"></td>
                        <td width="170" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                            {!! Currency::format($order->getMaxItemsPrice(), $order->currency) !!}
                        </td>
                        <td width="10px"></td>
                    </tr>

                    <tr>
                        <td width="100"></td>
                        <td width="300" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                            Скидка
                        </td>
                        <td width="20px"></td>
                        <td align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">
                            {!! Currency::format($order->getMaxItemsPrice() - $order->getItemsPrice(), $order->currency) !!}
                        </td>
                        <td width="10px"></td>
                    </tr>

					{{-- <tr>
						<td width="100"></td>
						<td width="300" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">Скидка</td>
						<td width="20px"></td>
						<td align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">{ORDER_SUMM_PROMOCODE}}</td>
						<td width="10px"></td>
					</tr> --}}

					{{-- <tr>
						<td width="100"></td>
						<td width="300" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">Транспортные расходы</td>
						<td width="20px"></td>
						<td align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:16px; color:#222222; font-weight: bold;">{{TRANSPORT_COSTS}}</td>
						<td width="10px"></td>
					</tr> --}}

					<tr><td colspan="5" height="10"></td></tr>
					<tr>
						<td width="200"></td>
						<td colspan="3" height="1" style="background-color: #DDDDDD;"></td>
						<td width="10px"></td>
					</tr><tr><td colspan="5" height="10"></td></tr>
					<tr>
						<td width="100"></td>
						<td width="300" align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:20px; color:#222222; font-weight: bold;">ИТОГО</td>
						<td width="20px"></td>
						<td align="right" valign="middle" style="font-family:Roboto, Verdana; font-size:20px; color:#C0976B; font-weight: bold;">{!! Currency::format($order->getTotalPrice(), $order->currency) !!}</td>
						<td width="10px"></td>
					</tr>
					<tr><td colspan="5" height="10px"></td></tr>
				</tbody></table>


				{{-- MESSAGE END --}}
               </td>
            </tr>
            <tr>
              <td height="25"></td>
            </tr>
          </tbody>
        </table></td>
    </tr>
  </tbody>
</table>
</body>
</html>
