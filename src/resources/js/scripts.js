import Mustache from 'mustache';
import TEMPLATE_ADDED_TO_CART from '../templates/modals/added-to-cart.html';
import { validatePhone } from './components/inputs/phone';


(function ($) {
  $.fn.downCount = function (options, callback) {
    let settings = $.extend({date: null, offset: null}, options);
    if (!settings.date) {
      $.error('Date is not defined.');
    }
    if (!Date.parse(settings.date)) {
      $.error('Incorrect date format, it should look like this, 12/24/2012 12:00:00.');
    }
    let container = this;
    let currentDate = function () {
      let date = new Date();
      let utc = date.getTime() + (date.getTimezoneOffset() * 60000);
      let new_date = new Date(utc + (3600000*settings.offset))
      return new_date;
    };

    function countdown () {
      let target_date = new Date(settings.date),
          current_date = currentDate();
      let difference = target_date - current_date;
      if (difference < 0) {
        clearInterval(interval);
        if (callback && typeof callback === 'function') callback();
          return;
      }
      let _second = 1000,
          _minute = _second * 60,
          _hour = _minute * 60,
          _day = _hour * 24;
      let days = Math.floor(difference / _day),
          hours = Math.floor((difference % _day) / _hour),
          minutes = Math.floor((difference % _hour) / _minute),
          seconds = Math.floor((difference % _minute) / _second);
      hours = (String(hours).length >= 2) ? hours : '0' + hours;
      minutes = (String(minutes).length >= 2) ? minutes : '0' + minutes;
      seconds = (String(seconds).length >= 2) ? seconds : '0' + seconds;

      if(days){
        container.find('.days').text(days);
      }
      container.find('.hours').text(hours);
      container.find('.minutes').text(minutes);
      container.find('.seconds').text(seconds);
    };
    let interval = setInterval(countdown, 1000);
  };
})(jQuery);
function countDownFunc( items, trigger ) {
  items.each( function() {
    let countDown = $(this),
        dateTime = $(this).data('date-time');

    let countDownTrigger = ( trigger ) ? trigger : countDown;
    countDownTrigger.downCount({
        date: dateTime,
        offset: +3
    });
  });
}
countDownFunc($('.js-countdown'));

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
                    $.fancybox.open(Mustache.render(TEMPLATE_ADDED_TO_CART, {
                      text: 'Ошибка добавления в корзину',
                      type: 'danger',
                    }));
                } else {
                    gtmProductAddEvent(productDetail);
                    $.fancybox.open(Mustache.render(TEMPLATE_ADDED_TO_CART, {
                      text: 'Товар успешно добавлен в корзину',
                      type: 'success',
                    }));
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

        if (!validatePhone($('input[name="phone"]'))) {
            return false;
        }
        if ($('input[name="first_name"]').val().length < 2) {
            return $.fancybox.open('<h3 class="py-3 text-danger">Введите имя</h3>');
        }

        $('form#oneclick-form').trigger('submit');
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


