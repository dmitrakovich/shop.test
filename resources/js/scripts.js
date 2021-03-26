$(function () {
    //#region боковое меню в мобильной версии

    // скрытие
    $('.overlay').on('click', function () {
        $('#mainMenu, .overlay').removeClass('active');
        $('body').removeClass('modal-open');
        window.history.back();
    });
    $(window).on('popstate', function () {
        // решение чисто для одного элемента
        // если нужен переход по несколким,
        // надо писать функцию
        $('#mainMenu, .overlay').removeClass('active');
        $('body').removeClass('modal-open');
    });
    // показ
    $('.js-show-main-menu').on('click', function () {
        $('#mainMenu, .overlay').addClass('active');
        $('body').addClass('modal-open');
        history.pushState(null, null, '#mainMenuOpen');
    });
    //#endregion

    //#region catalog
    // infinite scroll
    $('.catalog-page ul.pagination').hide();
    $('.catalog-page .scrolling-pagination').jscroll({
        autoTrigger: true,
        padding: 220,
        nextSelector: 'nav .pagination li.active + li a',
        contentSelector: 'div.jscroll-inner',
        callback: function () {
            $('ul.pagination').parent().remove();
            $('.jscroll-added .jscroll-inner .js-product-item').unwrap().unwrap();
        }
    });
    // sorting
    $('select[name="sorting"]').on('change', function () {
        window.location.href = $(this).find('option:selected').data('href');
    });
    // quick view
    $(document).on('click', '.quick-link a', function (e) {
        e.preventDefault();
        let url = $(this).data('src');
        $.fancybox.open({
            type: 'ajax',
            ajax: {
                settings: {
                    url: url,
                    type: "POST"
                }
            },
            afterShow: function () {
                slickRefresh();
            }
        });
    });
    //#endregion

    //#region product

    //#endregion

    //#region cart
    $(document).on('click', 'label.check .checkmark', function () {
        let $checkBox = $(this).siblings('input[type=checkbox]');
        $checkBox.prop("checked", !$checkBox.prop("checked")).trigger('click');
        $(this).parent().toggleClass("checked");
    });
    $(document).on('click', 'button.js-add-to-cart', function () {
        sizesValidate();

        let $form = $('form#product-info');
        $.ajax({
            method: "post",
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function (response) {
                if (response.result != 'ok') {
                    $.fancybox.open('<h3 class="py-5 text-danger">Ошибка добавления в корзину</h3>');
                } else {
                    $.fancybox.open('<h3 class="py-5 text-success">Товар успешно добавлен в корзину</h3>');
                    $('.js-cart-count').text(response.total_count);
                }
            }
        });
    });
    $(document).on('click', 'button.js-buy-one-click', function () {
        if (sizesValidate()) {
            $.fancybox.open($('#buy-one-click'));
        }
    });
    $(document).on('click', 'button#buy-one-click-submit', function () {
        sizesValidate();

        // подтянуть в форму размеры
        $('.js-sizes').clone().appendTo('form#oneclick-form').hide();

        let phone = $('input[name="phone"]').val();
        let name = $('input[name="name"]').val();
        // let address = $('input[name="address"]').val();

        if (phone.length < 4) {
            return $.fancybox.open('<h3 class="py-3 text-danger">Введите корректный номер телефона</h3>');
        }
        if (name.length < 2) {
            return $.fancybox.open('<h3 class="py-3 text-danger">Введите имя</h3>');
        }

        $('form#oneclick-form').trigger('submit');

        /*let $form = $('form#product-info');
        let $modal = $('#buy-one-click .col-12');

        $modal.find('#buy-one-click-submit').prop('disabled', true);
        $.fancybox.getInstance('showLoading');

        $.ajax({
            method: "post",
            url: '/orders',
            data: $form.serialize() + '&phone=' + phone + '&name=' + name + '&user_addr=' + address,
            success: function (response) {
                $modal.html(response).wrapInner('<h3>');
                $.fancybox.getInstance('hideLoading');
            }
        });*/

    });
    //#endregion

});

window.sizesValidate = function () {
    let $sizesBlock = $('.js-sizes').find('input[type=checkbox]:checked');
    if (!$sizesBlock.length) {
        $.fancybox.open('<h3 class="py-4 px-5">Не выбран размер</h3>');
        return false;
    }
    return true;
}
