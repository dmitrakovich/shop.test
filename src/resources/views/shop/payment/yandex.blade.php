@extends('layouts.app')


@section('content')
<div class="mt-3 col-12 text-center">
  <h1 class="text-danger h3">Счёт № {{ $payment->payment_num }}</h1>
  <p class="text-muted">к заказу № {{ $payment->order_id }} от {{ $payment->order->created_at->format('d.m.Y') }}</p>
  <p class="h4">Сумма {{ $payment->amount }} RUB</p>
  <br>
  <p class="text-muted">Нажмите "Оплатить", чтобы перейти на защищенную страницу оплаты сервиса ЮKassa</p>
  <a href="{{ route('pay.link-code', ['code' => $linkCode]) }}" class="btn btn-dark px-5">Оплатить</a>
  <div class="my-5">
    @include('svg.yookassa')
  </div>
</div>
<div class="my-3 col-12">
  <h2 class="h3 mb-3 text-center font-weight-normal">Как оплатить?</h2>
  <div class="row">
    <div class="col-12 col-md-6">
      <h3 class="h4">1-й способ</h3>
      <h3 class="h5">Банковской картой: Visa, Mastercard, Maestro, Мир, JCB, UnionPay</h3>
      <ol>
        <li>Выберите способ оплаты “Банковской картой”</li>
        <li>Введите данные с Вашей карты</li>
        <li>Ваш банк отправит SMS с кодом подтверждения платежа на телефон привязанный к Вашему счету.</li>
        <li>Введите полученный код</li>
        <li>Оплата совершена - ожидайте доставку товара.</li>
      </ol>
    </div>
    <div class="col-12 col-md-6">
      <h3 class="h4">2-й способ</h3>
      <h3 class="h5">Мобильный или интернет-банкинг: SberPay, Альфа-Клик, Тинькофф, СБП</h3>
      <ol>
        <li>Платежная система переведёт Вас в выбранное приложение для оплаты</li>
        <li>Оплачивайте следуя инструкциям приложения</li>
        <li>Оплата совершена - ожидайте доставку товара.</li>
      </ol>
    </div>
  </div>
</div>
@endsection
