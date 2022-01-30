import { default as axios } from "axios";
import { FAVORITE } from './../routes';

$(function () {
  $(document).on('click', '.js-favorite:not(.active)', function () {
    $(this).addClass('active');
    let productId = $(this).data('product-id');
    axios.post(FAVORITE.ADD, { productId })
      // .then((response) => console.log(response.data))
      .catch((error) => console.error(error));
  });

  $(document).on('click', '.js-favorite.active', function () {
    $(this).removeClass('active');
    let productId = $(this).data('product-id');
    axios.delete(FAVORITE.DEL + productId)
      // .then((response) => console.log(response.data))
      .catch((error) => console.error(error));
  });
});
