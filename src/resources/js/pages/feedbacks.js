const { default: axios } = require("axios");
import captcha from './../components/captcha';

document.addEventListener("DOMContentLoaded", function () {

  const feedbackForm = document.querySelector('form#leave-feedback');
  const submitButton = document.querySelector('.js-leave-feedback-btn');

  submitButton?.addEventListener('click', event => {
    submitButton.disabled = true;
    submitButton.classList.add('btn-disabled-load');

    captcha().then((token) => {
      let data = new FormData(feedbackForm);
      data.append('captcha_token', token);

      axios.post('/feedbacks', data).then((response) => {
        // console.log(response.data);
        feedbackForm.outerHTML = response.data;
      });
    });
  });

});
