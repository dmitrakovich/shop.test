@if (!empty($pendingPromocode))
    <script>
        window.onload = function() {
            $.fancybox.open(
                '<p>Вы получили промокод <b>{{ $pendingPromocode->code }}</b></p>' +
                '<p>Для его активации зарегистрируйтесь на сайте.</p>' +
                '<a href="{{ route('login') }}" class="btn btn-dark">Авторизоваться</a>'
            );
        }
    </script>
@endif
