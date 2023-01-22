const { default: axios } = require("axios");
import captcha from './../components/captcha';

const feedbackFormId = 'leave-feedback-form';
const submitButtonId = 'leave-feedback-btn';

document.addEventListener('click', function (event) {
  if (event.target.id === submitButtonId) {
    saveFeedback();
  }
});

function saveFeedback() {
  const feedbackForm = document.getElementById(feedbackFormId);
  const submitButton = document.getElementById(submitButtonId);

  submitButton.disabled = true;
  submitButton.classList.add('btn-disabled-load');

  captcha().then((token) => {
    let data = new FormData(feedbackForm);
    data.append('captcha_token', token);

    axios.post('/feedbacks', data).then((response) => {
      feedbackForm.outerHTML = response.data.result;
      dataLayer.push(response.data.dataLayer);
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
}
