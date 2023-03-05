<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! SeoFacade::generateHead() !!}

    {{-- favicon --}}
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('/favicon-16x16.png') }}" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ asset('/favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('/favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/png" href="{{ asset('/favicon-192x192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('/apple-touch-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('/apple-touch-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('/apple-touch-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('/apple-touch-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('/apple-touch-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('/apple-touch-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('/apple-touch-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('/apple-touch-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon-180x180.png') }}">

    <!-- Scripts -->
    {{-- <script src="{{ mix('js/manifest.js') }}" defer></script>
        <script src="{{ mix('js/vendor.js') }}" defer></script> --}}
    <script src="{{ mix('js/app.js') }}" defer></script>

    {{-- Google Tag Manager --}}
    @include('googletagmanager::head')

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body>
    {{-- Google Tag Manager --}}
    @include('googletagmanager::body')

    @include('includes.scripts')
    @include('includes.header')

    <main class="content">
        <div class="container-fluid">
            <div class="row wrapper justify-content-center">
                <div class="col-12">
                    @yield('breadcrumbs', '')
                </div>
                @yield('content')
            </div>
        </div>
    </main>

    @include('includes.footer')
    <div class="overlay"></div>

    {{-- call center chat --}}
    <script src="https://livechatv2.chat2desk.com/packs/ie-11-support.js"></script>
    <script>
        window.chat24_token = "600d1a4de44330c6024563e06f0db0d8";
        window.chat24_url = "https://livechatv2.chat2desk.com";
        window.chat24_socket_url = "wss://livechatv2.chat2desk.com/widget_ws_new";
        window.chat24_show_new_wysiwyg = "true";
        window.chat24_static_files_domain = "https://storage.chat2desk.com/";
        window.lang = "ru";
        window.fetch("".concat(window.chat24_url, "/packs/manifest.json?nocache=").concat(new Date()
            .getTime())).then(function(res) {
            return res.json();
        }).then(function(data) {
            var chat24 = document.createElement("script");
            chat24.type = "text/javascript";
            chat24.async = true;
            chat24.src = "".concat(window.chat24_url).concat(data["application.js"]);
            document.body.appendChild(chat24);
        });
    </script>

</body>

</html>
