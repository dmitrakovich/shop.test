import timer from '../components/timer';
import { TABLET_BREAKPOINT } from '../constants';

const { default: axios } = require("axios");
const SCROLL_POSITION_STORAGE_KEY = 'catalog-scroll-position';

document.addEventListener("DOMContentLoaded", function () {
  let productsContainer = document.getElementById('catalog-endless-scroll');

  if (!!productsContainer) {
    let cursor = document.getElementsByName('cursor')[0].value;
    let hasMoreProducts = document.getElementsByName('has_more')[0].value;
    let gtmData = {
      category: document.getElementsByName('gtm_category_name')[0].value,
      search: document.getElementsByName('gtm_search_query')[0].value,
    };
    let isLoading = false;

    document.addEventListener('scroll', function (e) {
      const containerHeight = productsContainer.offsetHeight;
      const scrollBottom = window.pageYOffset + window.innerHeight;

      if (scrollBottom > (containerHeight + 220)) {
        if (isLoading || !hasMoreProducts) {
          return;
        }
        isLoading = true;

        axios.get('/ajax-next-page', { params: { cursor, ...gtmData } })
          .then(function (response) {
            hasMoreProducts = response.data.has_more;
            cursor = response.data.cursor;
            response.data.rendered_products.forEach(productHtml => {
              productsContainer.insertAdjacentHTML('beforeend', productHtml);
            });
            response.data.data_layers.forEach(function (data) {
              dataLayer.push(data);
            });
            isLoading = false;
          })
          .catch(function (error) {
            if (error.response.status == 419) {
              window.scrollTo(0, 0);
              document.location.reload();
            } else {
              console.log(error);
            }
          });
      }
    });
  }

  // save scroll position
  if (document.documentElement.clientWidth >= TABLET_BREAKPOINT) {
    document.addEventListener('click', saveScrollPosition);
    window.scrollTo(0, getScrollPosition());
  }

  // scroll top button
  document.querySelector('button.scroll-top-btn').addEventListener('click', function () {
    window.scrollTo({top: 0, behavior: 'smooth'});
  });

  // quick view
  $(document).on('click', '.quick-link', function (e) {
    e.preventDefault();
    let url = $(this).data('src');
    $.fancybox.open({
      type: 'ajax',
      ajax: {
        settings: {
          url: url,
          type: "POST"
        }
      },
      afterShow: function () {
        timer($('.js-countdown'));
        slickRefresh();
        gtmProductDetailEvent(productDetail);
      }
    });
  });
});

/**
 * @param {PointerEvent} event
 */
const saveScrollPosition = function (event) {
  const filters = document.getElementById("sidebarFilters");
  if (event.target === filters || filters.contains(event.target)) {
    sessionStorage.setItem(SCROLL_POSITION_STORAGE_KEY, window.scrollY);
  } else {
    sessionStorage.removeItem(SCROLL_POSITION_STORAGE_KEY);
  }
}

/**
 * @returns number
 */
const getScrollPosition = function () {
  return +(sessionStorage.getItem(SCROLL_POSITION_STORAGE_KEY) || 0);
}
