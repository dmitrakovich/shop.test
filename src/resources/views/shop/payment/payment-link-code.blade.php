@extends('layouts.app')

@section('content')
<style>
  .loader {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
  }

  .loader div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 3px solid #000;
    border-radius: 50%;
    animation: loader 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: #000 transparent transparent transparent;
  }

  .loader div:nth-child(1) {
    animation-delay: -0.45s;
  }

  .loader div:nth-child(2) {
    animation-delay: -0.3s;
  }

  .loader div:nth-child(3) {
    animation-delay: -0.15s;
  }

  @keyframes loader {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }
</style>
<div class="my-5 col-12 text-center">
  <h1 class="text-danger h3">Счёт № {{ $payment->payment_num }}</h1>
  <p class="text-muted">к заказу № {{ $payment->order_id }} от {{ $payment->order->created_at->format('d.m.Y') }}</p>
  <p class="h4">Сумма {{ $payment->amount }} {{ $payment->currency_code }}</p>
  <p class="text-muted text-danger" id="infoText">Пожалуйста, подождите.<br>Мы перенаправляем Вас на страницу платежной системы.</p>
  <div id="loader" class="loader">
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>
</div>
<script>
  var xhr = new XMLHttpRequest();
  var link, message = '';

  function errorMessage() {
    document.getElementById('loader').style.display = "none";
    var element = document.getElementById('infoText');
    element.innerText = 'Ошибка при создании платежа. Свяжитесь с менеджером.';
    element.classList.remove("text-muted");
    element.classList.add("text-danger");
  }

  function successMessage(message = '') {
    document.getElementById('loader').style.display = "none";
    var element = document.getElementById('infoText');
    element.innerText = message;
    element.classList.add("text-success");
  }
  try {
    xhr.open('POST', "{{ route('pay.check-link-code', ['code' => $payment->link_code ?? null, '_token' => csrf_token()]) }}");
    xhr.send();
    xhr.onloadend = function() {
      if (xhr.readyState != 4) {
        errorMessage();
        return;
      }
      if (xhr.status != 200) {
        errorMessage();
      } else {
        if (xhr.responseText) {
          var responseText = JSON.parse(xhr.responseText);
          link = responseText.payment_url ? window.location.replace(responseText.payment_url) : '';
          message = responseText.message ? successMessage(responseText.message) : '';
        } else {
          errorMessage();
        }
      }
    }
  } catch (error) {
    errorMessage();
  }
</script>
@endsection
