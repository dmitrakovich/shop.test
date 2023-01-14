import captcha from './../components/captcha';

document.addEventListener("DOMContentLoaded", function () {

  const loginForm = document.forms.login_form;
  const submitButton = loginForm?.querySelector('button[type=submit]');
  const resendOtpButton = document.getElementById('resend-otp-button');

  loginForm?.addEventListener('submit', event => {
    event.preventDefault();
    submitLoginForm();
  });

  resendOtpButton?.addEventListener('click', event => {
    document.getElementById('otp')?.remove();
    submitLoginForm();
  });

  function submitLoginForm() {
    submitButton.disabled = true;
    submitButton.classList.add('btn-disabled-load');

    captcha().then((token) => {
      let input = document.createElement("input");
      input.type = "hidden";
      input.name = "captcha_token";
      input.value = token;
      loginForm.appendChild(input);
      loginForm.submit();
    });
  }
});
