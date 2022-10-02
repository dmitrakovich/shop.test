document.addEventListener("DOMContentLoaded", function () {
  $(".js-range-slider").ionRangeSlider({
    skin: "square",
    type: "double",
    grid: true,
    keyboard: false,
    onFinish: function (data) {
      console.log(data);
    }
  });
});
