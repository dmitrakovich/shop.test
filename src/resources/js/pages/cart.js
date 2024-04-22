import { isCartPage } from '../routes';
import { validatePhone } from './../components/inputs/phone';

$(function () {
  $(document).on('click', 'button[type="submit"][form="cartData"]', function (event) {
    event.preventDefault();
    const $form = $('#cartData');
    if (validatePhone($form.find('.js-phone-input'))) {
      $form.trigger('submit');
    }
  });

  // temporary shitcode for price
  if (isCartPage) {
    $(document).on('change', 'input[name="payment_id"]', function () {
      if (+$(this).val() === 4) {
        $('.js-normal-price').hide();
        $('.js-without-user-sale-price').show();
      } else {
        $('.js-normal-price').show();
        $('.js-without-user-sale-price').hide();
      }
    });
    $(document).find('input[name="delivery_id"]:not(:disabled)').first().prop('checked', true);
    $(document).on('change', 'input[name="delivery_id"]', function () {
      if (+$(this).val() === 6) {
        $('#payment-1').prop('disabled', true);
      } else {
        $('#payment-1').prop('disabled', false);
      }
    });
  }
  // end temporary shitcode for price
});
