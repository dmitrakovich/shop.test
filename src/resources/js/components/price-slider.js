var $range = $('.js-range-slider');
var $inputFrom = $('#price-range-form input[name="price_from"]');
var $inputTo = $('#price-range-form input[name="price_to"]');
const priceMin = +$range.data('min');
const priceMax = +$range.data('max');
var instance;

document.addEventListener("DOMContentLoaded", function () {
  $range.ionRangeSlider({
    skin: "square",
    type: "double",
    grid: true,
    grid_num: 2,
    keyboard: false,
    onStart: updateInputs,
    onChange: updateInputs,
    onFinish: updateInputs
  });
  instance = $range.data("ionRangeSlider");
});

/**
 * set iputs values
 * @param {object} data
 */
function updateInputs(data) {
  from = data.from;
  to = data.to;

  $inputFrom.prop("value", from);
  $inputTo.prop("value", to);
}

/**
 * input price_from
 */
$inputFrom.on("change", function () {
  var val = $(this).prop("value");
  var to = instance.old_to;
  // validate
  if (val < priceMin) {
    val = priceMin;
  } else if (val > to) {
    val = to;
  }
  instance.update({ from: val });
  $(this).prop("value", val);
});

/**
 * input price_to
 */
$inputTo.on("change", function () {
  var val = $(this).prop("value");
  var from = instance.old_from;
  // validate
  if (val < from) {
    val = from;
  } else if (val > priceMax) {
    val = priceMax;
  }
  instance.update({ to: val });
  $(this).prop("value", val);
});
