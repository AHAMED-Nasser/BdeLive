const O_pwdConfirmMessage = document.querySelector('.confirm-pwd-message');
const O_pwdInput = document.getElementById('password');
const O_pwdConfirmInput = document.getElementById('confirm-password');

const O_verifyLength = document.getElementById('verifyLength');
const O_verifyLower = document.getElementById('verifyLower');
const O_verifyUpper = document.getElementById('verifyUpper');
const O_verifyDigit = document.getElementById('verifyDigit');
const O_verifySpecial = document.getElementById('verifySpecialChar');

const O_pwdSecurityContainer = document.getElementById('pwd-conditions');

function checkPasswordMatch(pwd, pwdConfirm) {
    if (!pwd.value || !pwdConfirm.value) {
        O_pwdConfirmMessage.textContent = "";
        return
    }

    if (pwd.value === pwdConfirm.value) {
        O_pwdConfirmMessage.style.color = '#10b981';
        O_pwdConfirmMessage.textContent = 'Mot de passe identique';
    } else {
        O_pwdConfirmMessage.style.color = '#e61c1c';
        O_pwdConfirmMessage.textContent = 'Mot de passe différent';
    }
}

function setValid(element, valid) {
    if (valid) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

function checkPasswordSecurity() {
    const pwd = O_pwdInput.value;

    const hasLength = /^.{12,}$/.test(pwd);
    const hasLower = /[a-z]/.test(pwd);
    const hasUpper = /[A-Z]/.test(pwd);
    const hasDigit = /[0-9]/.test(pwd);
    const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

    setValid(O_verifyLength, hasLength);
    setValid(O_verifyLower, hasLower);
    setValid(O_verifyUpper, hasUpper);
    setValid(O_verifyDigit, hasDigit);
    setValid(O_verifySpecial, hasSpecial);

    return hasLength && hasLower && hasUpper && hasDigit && hasSpecial;
}

O_pwdInput.addEventListener('input', () => checkPasswordMatch(O_pwdInput, O_pwdConfirmInput));
O_pwdConfirmInput.addEventListener('input', () => checkPasswordMatch(O_pwdInput, O_pwdConfirmInput));

O_pwdInput.addEventListener('input', () => {
    if (O_pwdInput.value.length > 0) {
        O_pwdSecurityContainer.style.display = 'block';
    } else {
        O_pwdSecurityContainer.style.display = 'none';
    }
    checkPasswordSecurity();
});
