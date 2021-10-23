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
        feedbackForm.outerHTML = response.data;
      }).catch((error) => {
        if (error.response.status != 422) {
          return false;
        }
        const errors = error.response.data.errors;
        let errorMessages = [];
        for (const inputName in errors) {
          errorMessages.push(errors[inputName]);
        }
        alert(errorMessages.join('\n'))
      }).finally(() => {
        submitButton.disabled = false;
        submitButton.classList.remove('btn-disabled-load');
      });
    });
  });

});
