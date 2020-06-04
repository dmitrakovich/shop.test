$(function () {
//#region боковое меню в мобильной версии

    // !!! сделать скрытие меню при нажатии кнопки назад
    // посмотреть fancebox

    // скрытие
    $('.overlay').on('click', function () {
        $('#mainMenu, .overlay').removeClass('active');
        $('body').removeClass('modal-open');
    });
    // показ
    $('.js-show-main-menu').on('click', function () {
        $('#mainMenu, .overlay').addClass('active');
        $('body').addClass('modal-open');
    });
//#endregion
});