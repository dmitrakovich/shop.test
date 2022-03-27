import parsePhoneNumber from "libphonenumber-js";

$(function () {
  $(document).on('click', '.js-phone-country', function (event) {
    event.preventDefault();

    const $component = $(this).parents('.js-phone-component');
    const $input = $component.find('.js-phone-input');
    const $image = $component.find('.js-phone-select-country').children();

    let code = $(this).attr('href');
    let mask = $(this).data('mask');
    let img = $(this).find('img').attr('src');

    $input.val('').attr('placeholder', mask).data('code', code);
    $image.attr('src', img);
  });

  $(document).on('blur', '.js-phone-input', function () {
    validatePhone($(this));
  });
});

const validatePhone = function ($input) {
  let phone = $input.val();
  let code = $input.data('code');

  $input.removeClass('is-invalid')
    .siblings('.invalid-feedback')
    .remove();

  const phoneNumber = parsePhoneNumber(phone, code);

  if (phoneNumber) {
    if (phoneNumber.isValid()) {
      $input.val(phoneNumber.format("E.164"));
      return true;
    }
  }
  $input.addClass('is-invalid').after(
    $('<span></span>')
      .addClass('invalid-feedback')
      .attr({role: 'alert'})
      .html('<strong>Введите корректный номер телефона</strong>')
  );
  return false;
}

export { validatePhone }
