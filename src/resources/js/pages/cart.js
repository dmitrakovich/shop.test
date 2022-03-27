import { validatePhone } from './../components/inputs/phone';

$(function () {
  $(document).on('click', 'button[type="submit"][form="cartData"]', function (event) {
    event.preventDefault();
    const $form = $('#cartData');
    if (validatePhone($form.find('.js-phone-input'))) {
      $form.trigger('submit');
    }
  });
});
