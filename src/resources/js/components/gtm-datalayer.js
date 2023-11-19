/* eslint-disable no-undef */
$(function () {
  if (typeof productDetail !== 'undefined') {
    gtmProductDetailEvent(productDetail);
  }

  $(document).on('click', '[data-gtm-click]', function (event) {
    event.preventDefault();
    const eventName = $(this).data('gtm-click');
    const product = $(this).parents('.js-product-item').data('gtm-product');
    gtmEcomEvent(
      eventName,
      { click: { products: [product] } },
      () => { document.location = $(this).attr('href') },
    );
  });

  $(document).on('click', '[data-gtm-user-event]', function () {
    const eventName = $(this).data('gtm-user-event');
    dataLayer.push({
      event: 'user_event',
      event_label: eventName,
      event_category: 'user',
      event_action: eventName,
    });
  });
});

/**
 * @param {string} eventName
 * @param {Object} ecommerce
 * @param {Function} eventCallback
 */
gtmEcomEvent = function (eventName, ecommerce, eventCallback = () => {}, eventId = null) {
  dataLayer.push({
    ecommerce: {
      currencyCode: 'USD',
      ...ecommerce,
    },
    event: 'ecom_event',
    event_label: eventName,
    event_category: 'ecommerce',
    event_action: eventName,
    eventCallback,
  });
}

/**
 * @param {string} eventName
 * @param {string} eventId
 * @param {Object} ecommerce
 * @param {Function} eventCallback
 */
gtmEcomEventWithId = function (eventName, eventId, ecommerce, eventCallback = () => {}) {
  dataLayer.push({
    ecommerce: {
      currencyCode: 'USD',
      ...ecommerce,
    },
    event: 'ecom_event',
    event_id: eventId,
    event_label: eventName,
    event_category: 'ecommerce',
    event_action: eventName,
    eventCallback,
  });
}

/**
 * @param {Object} product
 */
gtmProductDetailEvent = function (product) {
  gtmEcomEvent('productDetail', {
    detail: { products: [product] },
  });
}

/**
 * @param {Object} product
 * @param {Number} quantity
 */
gtmProductAddEvent = function (eventId, product, quantity = 1) {
  product.quantity = quantity;
  gtmEcomEventWithId('productAdd', eventId, {
    add: { products: [product] },
  });
}

/**
 * @param {Object} product
 * @param {Number} quantity
 */
gtmProductRemoveEvent = function (product, quantity = 1) {
  product.quantity = quantity;
  gtmEcomEvent('productRemove', {
    remove: { products: [product] },
  });
}
