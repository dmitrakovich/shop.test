import { default as axios } from "axios";
import { FAVORITE } from './../routes';

$(function () {
  $(document).on('click', '.js-favorite:not(.active)', function () {
    $(this).addClass('active');
    let productId = $(this).data('product-id');
    let gtmProduct = $(this).parents('.js-product-item').data('gtm-product');
    axios.post(FAVORITE.ADD, { productId })
      .then((response) => gtmProductAddEvent(gtmProduct))
      .catch((error) => console.error(error));
  });

  $(document).on('click', '.js-favorite.active', function () {
    $(this).removeClass('active');
    let productId = $(this).data('product-id');
    let gtmProduct = $(this).parents('.js-product-item').data('gtm-product');
    axios.delete(FAVORITE.DEL + productId)
      .then((response) => gtmProductRemoveEvent(gtmProduct))
      .catch((error) => console.error(error));
  });
});
