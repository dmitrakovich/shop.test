$(function () {
//#region боковое меню в мобильной версии

    // скрытие
    $('.overlay').on('click', function () {
        $('#mainMenu, .overlay').removeClass('active');
        $('body').removeClass('modal-open');
        window.history.back();
    });
    $(window).on('popstate', function() {
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
        callback: function() {
            $('ul.pagination').parent().remove();
            $('.jscroll-added .jscroll-inner .js-product-item').unwrap().unwrap();
        }
    });
    // sorting
    $('select[name="sorting"]').on('change', function () {
        window.location.href = $(this).find('option:selected').data('href');
    });
//#endregion


//#region cart
$('button.js-add-to-cart').on('click', function () {
    $.ajax({
        method: "post",
        url: "/add-to-cart",
        data: {id: $('#product_id').val()},
        // dataType: "dataType",
        success: function (response) {
            if (response.result != 'ok') {
                alert('ошибка добавления в корзину');
            } else {
                alert('товар успешно добавлен в корзину');
                $('.js-cart-count').text(response.total_count);
            }
        }
    });
});
//#endregion

});




// набросок для isotope
// данный пример заменяет имеющиеся товары!

// https://www.npmjs.com/package/imagesloaded


/*
$('.product_grid').html($data);

$('.product_grid').isotope('destroy');

$('.product_grid').imagesLoaded(function () {
    var $grid = $('.product_grid').isotope({
		itemSelector: '.product',
        layoutMode: 'fitRows',
        fitRows: {
            gutter: 30
        }
	});
});

*/
