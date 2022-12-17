<!doctype html>
<html>

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="HandheldFriendly" content="true">
  <meta name="apple-mobile-web-app-capable" content="YES">
  <meta name="viewport" content="width=device-width">

  <title>Выберите мессенджер для канала BAROCCO</title>
  <meta name="description" content="BAROCCO - брендовая обувь с примеркой по Беларуси">

  <style type="text/css">
    body {
      background-color: #000000;
      color: #222222;
      font-family: Roboto, sans-serif;
      font-size: 1.5rem;
      font-weight: normal;
      line-height: 1.2;
      margin: 0;
      padding: 0;
      text-align: center;
    }

    .container {
      position: fixed;
      top: 20vh;
      left: 5vw;
      width: calc(90vw - 2rem);
      max-width: 640px;
      background-color: #ffffff;
      border-radius: 1rem;
      padding: 1rem;
    }

    @media all and (screen) {
      .container {
        position: fixed;
        top: 20vh;
        left: 5vw;
        margin: 30vh auto;
      }
    }

    .logo {
      margin: 1.5rem auto;
      width: 70%;
      max-width: 500px;
    }

    .logo img {
      max-width: 100%;
      height: auto;
    }

    .text {
      margin: 1.5rem auto;
    }

    .box {
      margin: 2rem auto;
      display: flex;
      justify-content: center;
    }

    .button {
      flex: 0 1 30%;
      padding: 0.5rem;
      height: 3.5rem;
      line-height: 3.5rem;
      font-size: 0.8rem;
      border-radius: 0.5rem;
      color: #222222;
      display: block;
      text-decoration: none;
    }

    @media all and (screen) {
      a.button {
        color: #ffffff;
      }
    }

    .button img {
      max-height: 100%;
      width: auto;
      margin-right: 1rem;
      vertical-align: middle;
    }

    .button_vb {
      background-color: #7d539f;
    }

    .button_tg {
      background-color: #018ccf;
      margin-right: 10%;
    }
  </style>

  <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      "pageType": "landingChannel",
      "event": "view_page"
    });

    function leadComplete(label) {
      try {
        window.dataLayer.push({
          'event': 'user_event',
          'event_label': label,
          'event_category': 'user',
          'event_action': 'AddToChannel'
        });
      } catch (e) {
        console.log('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
      }
    };
  </script>
  <!-- Google Tag Manager -->
  <script>
    (function(w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
      });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
      j.async = true;
      j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-5PPN2WH');
  </script>
  <!-- End Google Tag Manager -->

</head>

<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5PPN2WH" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->


  <div class="container">
    <div class="logo">
      <img src="{{ url('/uploads/channel/barocco_logo.png') }}" alt="BAROCCO">
    </div>

    <div class="text">Выберите приложение<br>для подписки на канал:</div>

    <div class="box">
      <a class="button button_tg" href="https://t.me/barocco_by" title="канал Telegram" onClick="leadComplete('telegram_subscribe');"><img src="{{ url('/uploads/channel/tg_white.png') }}" alt="канал Telegram">Telegram</a>
      <a class="button button_vb" href="https://invite.viber.com/?g2=AQB8PUyG5C7u507fc5vO1d9qgVguM3f2bR1PvGloHCrNNZJU4SGHHigjhYWQF5D2&lang=ru" title="канал Viber" onClick="leadComplete('viber_subscribe');"><img src="{{ url('/uploads/channel/vb_white.png') }}" alt="канал Viber">Viber</a>
    </div>
  </div>
</body>

</html>
