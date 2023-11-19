/* eslint-disable no-undef */
import { default as axios } from "axios";
import { FAVORITE } from './../routes';

$(function () {
  $(document).on('click', '.js-favorite:not(.active)', function () {
    $(this).addClass('active');
    const productId = $(this).data('product-id');
    const gtmCatalogProduct = $(this).parents('.js-product-item').data('gtm-product');
    const gtmProduct = $('.p-product .js-product-item').data('gtm-product');
    axios.post(FAVORITE.ADD, { productId })
      .then((response) => gtmProductAddEvent(response.data.event_id, gtmCatalogProduct ?? gtmProduct))
      .catch((error) => console.error(error));
  });

  $(document).on('click', '.js-favorite.active', function () {
    $(this).removeClass('active');
    const productId = $(this).data('product-id');
    const gtmCatalogProduct = $(this).parents('.js-product-item').data('gtm-product');
    const gtmProduct = $('.p-product .js-product-item').data('gtm-product');
    axios.delete(FAVORITE.DEL + productId)
      .then((response) => gtmProductRemoveEvent(gtmCatalogProduct ?? gtmProduct))
      .catch((error) => console.error(error));
  });
});
