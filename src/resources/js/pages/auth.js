import captcha from './../components/captcha';

document.addEventListener("DOMContentLoaded", function () {

  const registerForm = document.forms.register_form;
  const submitButton = registerForm?.querySelector('button[type=submit]')

  registerForm?.addEventListener('submit', event => {
    event.preventDefault();
    submitButton.disabled = true;
    submitButton.classList.add('btn-disabled-load');

    captcha().then((token) => {
      let input = document.createElement("input");
      input.type = "hidden";
      input.name = "captcha_token";
      input.value = token;
      registerForm.appendChild(input);
      registerForm.submit();
    });
  });

});
