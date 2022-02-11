$(function () {

  // $(document).on('click', '[data-gtm-click]', function (event) {
  //   event.preventDefault();
  //   var self = this;
  //   dataLayer.push({
  //     'event': 'productClick',
  //     'ecommerce': {
  //       'click': {
  //         'products': $(self).data('gtm-product')
  //       }
  //     },
  //     'eventCallback': function () {
  //       document.location = $(self).attr('href');
  //     }
  //   });
  // });

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

