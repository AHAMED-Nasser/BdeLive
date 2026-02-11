document.addEventListener('DOMContentLoaded', () => {
    const O_togglePassword = document.getElementById('togglePassword');
    const O_passwordInput = document.getElementById('password');

    if (O_togglePassword && O_passwordInput) {
        O_togglePassword.addEventListener('click', () => {
            const type = O_passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            O_passwordInput.setAttribute('type', type);
            O_togglePassword.innerHTML = type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
        });
    }
})
