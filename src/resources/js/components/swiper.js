import Swiper, { Navigation, Pagination } from 'swiper';
Swiper.use([Navigation]);
const intiSliders = function() {
  productSliders();
};

const productSliders = function() {
  let productSlider = new Swiper('.js-productSlider', {
    slidesPerView: 1,
    allowTouchMove: true,
    loop: true
  });
  let productSliderThumb = new Swiper('.js-productSliderThumb', {
    direction: "horizontal",
    spaceBetween: 10,
    slidesPerView: 4,
    allowTouchMove: true,
    loop: true,
    // breakpoints: {
    //     1200: {
    //         direction: "vertical",
    //         slidesPerView: 3,
    //         spaceBetween: 20,
    //     },
    // }
  });
  new Swiper('.js-productGroup', {
    slidesPerView: 'auto',
    spaceBetween: 10,
    loop: false,
    navigation: {
      nextEl: ".js-productGroup-next",
      prevEl: ".js-productGroup-prev",
    }
  });


}


export default intiSliders;
export {
  productSliders
};
