(function ($) {
  $.fn.downCount = function (options, callback) {
    let settings = $.extend({date: null, offset: null}, options);
    if (!settings.date) {
      $.error('Date is not defined.');
    }
    if (!Date.parse(settings.date)) {
      $.error('Incorrect date format, it should look like this, 12/24/2012 12:00:00.');
    }
    let container = this;
    let currentDate = function () {
      let date = new Date();
      let utc = date.getTime() + (date.getTimezoneOffset() * 60000);
      let new_date = new Date(utc + (3600000*settings.offset))
      return new_date;
    };

    function countdown () {
      let target_date = new Date(settings.date),
          current_date = currentDate();
      let difference = target_date - current_date;
      if (difference < 0) {
        clearInterval(interval);
        if (callback && typeof callback === 'function') callback();
          return;
      }
      let _second = 1000,
          _minute = _second * 60,
          _hour = _minute * 60,
          _day = _hour * 24;
      let days = Math.floor(difference / _day),
          hours = Math.floor((difference % _day) / _hour),
          minutes = Math.floor((difference % _hour) / _minute),
          seconds = Math.floor((difference % _minute) / _second);
      hours = (String(hours).length >= 2) ? hours : '0' + hours;
      minutes = (String(minutes).length >= 2) ? minutes : '0' + minutes;
      seconds = (String(seconds).length >= 2) ? seconds : '0' + seconds;

      if(days){
        container.find('.days').text(days);
      }
      container.find('.hours').text(hours);
      container.find('.minutes').text(minutes);
      container.find('.seconds').text(seconds);
    };
    let interval = setInterval(countdown, 1000);
  };
})(jQuery);
function countDownFunc( items, trigger ) {
  items.each( function() {
    let countDown = $(this),
        dateTime = $(this).data('date-time');

    let countDownTrigger = ( trigger ) ? trigger : countDown;
    countDownTrigger.downCount({
        date: dateTime,
        offset: +3
    });
  });
}
export default countDownFunc;