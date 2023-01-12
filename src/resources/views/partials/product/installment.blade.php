<div class="col-12">
    <div class="row">
        <button type="button" class="btn installment-help-block"
            data-toggle="dropdown">
            <span class="border-bottom border-secondary">Условия рассрочки</span>
            <div class="tooltip-trigger ml-2">?</div>
        </button>
        <div class="dropdown-menu p-3">
            @if ($product->getPrice() >= 150)
                <p class="font-size-15">
                    <b>РАССРОЧКА НА 3 ПЛАТЕЖА</b>
                </p>
                <p>
                    Первый взнос<br>
                    <b>{{ $product->getPrice() - $product->getPrice() * 0.6 }}
                        руб.</b><br>
                    Оставшиеся 2 платежа по<br>
                    <b class="border-bottom border-danger font-size-14">
                        {{ $product->getPrice() * 0.3 }} руб. в месяц
                    </b>
                </p>
            @else
                <p class="font-size-15">
                    <b>РАССРОЧКА НА 2 ПЛАТЕЖА</b>
                </p>
                <p>
                    Первый взнос<br>
                    <b>{{ $product->getPrice() - $product->getPrice() * 0.5 }}
                        руб.</b><br>
                    Оставшийся платеж<br>
                    <b class="border-bottom border-danger font-size-14">
                        {{ $product->getPrice() * 0.5 }} руб.
                    </b>
                </p>
            @endif
            &#9989; Без увеличения цены <br>
            &#9989; Без справки о доходах
        </div>
    </div>
</div>
