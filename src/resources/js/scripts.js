import Cookies from './cookies';
import Mustache from 'mustache';
import TEMPLATE_ADDED_TO_CART from '../templates/modals/added-to-cart.html';
import { validatePhone } from './components/inputs/phone';
import timer from './components/timer';
import intiSliders from './components/swiper';
import { SESSION_TIME_KEY } from './constants';

intiSliders();

timer($('.js-countdown'));

let sessionTime = sessionStorage.getItem(SESSION_TIME_KEY) ?? 0;
setInterval(() => sessionStorage.setItem(SESSION_TIME_KEY, ++sessionTime), 1000);

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
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
  $('.js-showMainMenu').on('click', function () {
    $('#mainMenu, .overlay').addClass('active');
    $('body').addClass('modal-open');
    history.pushState(null, null, '#mainMenuOpen');
  });


  $(document).on('click', 'label.check .checkmark', function () {
    const $checkBox = $(this).siblings('input[type=checkbox]');
    $checkBox.prop('checked', !$checkBox.prop('checked')).trigger('click');
    $(this).parent().toggleClass('checked');
  });
  $(document).on('click', 'button.js-add-to-cart', function () {
    // eslint-disable-next-line no-undef
    sizesValidate();

    const $form = $('form#product-info');
    $.ajax({
      method: 'post',
      url: $form.attr('action'),
      data: $form.serialize(),
      success: function (response) {
        if (response.result !== 'ok') {
          $.fancybox.open(Mustache.render(TEMPLATE_ADDED_TO_CART, {
            text: 'Ошибка добавления в корзину',
            type: 'danger',
          }));
        } else {
          // eslint-disable-next-line no-undef
          gtmProductAddEvent(productDetail);
          $.fancybox.open(Mustache.render(TEMPLATE_ADDED_TO_CART, {
            text: 'Товар успешно добавлен в корзину',
            type: 'success',
          }));
          $('.js-cartCount').text(response.total_count);
        }
      },
    });
  });
  $(document).on('click', 'button.js-buy-one-click', function () {
    // eslint-disable-next-line no-undef
    if (sizesValidate()) {
      // подтянуть в форму размеры
      $('form#oneclick-form').find('.js-sizes').remove();
      $('.js-sizes').clone().appendTo('form#oneclick-form').hide();
      $.fancybox.open($('#buy-one-click'));
    }
  });
  $(document).on('click', 'button#buy-one-click-submit', function () {
    // eslint-disable-next-line no-undef
    sizesValidate();
    if (!validatePhone($('input[name="phone"]'))) {
      return false;
    }
    if ($('input[name="first_name"]').val().length < 2) {
      return $.fancybox.open('<h3 class="py-3 text-danger">Введите имя</h3>');
    }

    $('form#oneclick-form').trigger('submit');
  });
});

window.sizesValidate = function () {
  const $sizesBlock = $('.js-sizes').find('input[type=checkbox]:checked');
  if (!$sizesBlock.length) {
    $.fancybox.open('<h3 class="py-4 px-5">Не выбран размер</h3>');
    return false;
  }
  return true;
}

try {
  document.addEventListener("DOMContentLoaded", function () {
    const currentLocation = window?.location?.href;
    const currentLocationUrl = new URL(currentLocation);
    const locationReferrer = currentLocationUrl?.searchParams?.get('referrer');
    const referrerUrl = document?.referrer ? new URL(document.referrer) : null;
    if ((referrerUrl?.host === 'modny.by') || locationReferrer === 'modny.by') {
      if (!Cookies.get('modnyRedirectPopupShowed')) {
        $.fancybox.open({
          src: '/images/popup_redirect_modnyby.jpg',
          maxWidth: '90%',
          maxHeight: '90%',
          width: '500px',
        });
        Cookies.set('modnyRedirectPopupShowed', 1, 1);
      }
    }
  });
} catch (error) {
  console.log(error);
}
