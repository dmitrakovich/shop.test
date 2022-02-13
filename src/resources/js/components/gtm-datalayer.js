$(function () {
  $(document).on('click', '[data-gtm-click]', function (event) {
    event.preventDefault();
    let eventName = $(this).data('gtm-click');
    let product = $(this).parents('.js-product-item').data('gtm-product');
    gtmEcomEvent(
      eventName,
      {'click': {'products': [product]}},
      () => document.location = $(this).attr('href')
    );
  });

  $(document).on('click', '[data-gtm-user-event]', function () {
    let eventName = $(this).data('gtm-user-event');
    dataLayer.push({
      'event': 'user_event',
      'event_label': eventName,
      'event_category': 'user',
      'event_action': eventName,
    });
  });
});

/**
 *
 * @param {string} eventName
 * @param {Object} ecommerce
 * @param {Function} eventCallback
 */
function gtmEcomEvent(eventName, ecommerce, eventCallback = () => {}) {
  dataLayer.push({
    'ecommerce': {
      'currencyCode': 'USD',
      ...ecommerce,
    },
    'event': 'ecom_event',
    'event_label': eventName,
    'event_category': 'ecommerce',
    'event_action': eventName,
    'eventCallback': eventCallback
  });
}
