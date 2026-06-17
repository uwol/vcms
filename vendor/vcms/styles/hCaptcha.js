const form = document.getElementById('h-captcha-protected-form');

form.addEventListener('submit', function(e) {

    const hCaptcha = form.querySelector('textarea[name=h-captcha-response]').value;

    if (!hCaptcha) {
        e.preventDefault();
        alert("Bitte das Captcha ausfüllen")
        return
    }
});
