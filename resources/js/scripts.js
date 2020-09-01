$(function () {
//#region боковое меню в мобильной версии

    // посмотреть fancybox

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