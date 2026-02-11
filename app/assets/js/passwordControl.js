const O_pwdConfirmMessage = document.querySelector('.confirm-pwd-message');
const O_pwdInput = document.getElementById('password');
const O_pwdConfirmInput = document.getElementById('confirm-password');

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

O_pwdInput.addEventListener('input', () => checkPasswordMatch(O_pwdInput, O_pwdConfirmInput));
O_pwdConfirmInput.addEventListener('input', () => checkPasswordMatch(O_pwdInput, O_pwdConfirmInput));