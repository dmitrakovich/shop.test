const { default: axios } = require("axios");

document.addEventListener("DOMContentLoaded", function () {
  let productsContainer = document.getElementById('catalog-endless-scroll');

  if (!!productsContainer) {
    let cursor = document.getElementsByName('cursor')[0].value;
    let hasMoreProducts = document.getElementsByName('has_more')[0].value;
    let isLoading = false;

    document.addEventListener('scroll', function (e) {
      const containerHeight = productsContainer.offsetHeight;
      const scrollBottom = window.pageYOffset + window.innerHeight;

      if (scrollBottom > (containerHeight + 220)) {
        if (isLoading || !hasMoreProducts) {
          return;
        }
        isLoading = true;

        axios.get('/ajax-next-page', {params: {cursor}})
          .then(function (response) {
            hasMoreProducts = response.data.has_more;
            cursor = response.data.cursor;
            response.data.rendered_products.forEach(productHtml => {
              productsContainer.insertAdjacentHTML('beforeend', productHtml);
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

  // quick view
  $(document).on('click', '.quick-link a', function (e) {
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
        slickRefresh();
      }
    });
  });
});
