document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.password-container');

    containers.forEach(container => {
        const input = container.querySelector('input');
        const toggle = container.querySelector('.password-toggle');

        if (!input || !toggle) return;

        toggle.addEventListener('click', () => {
            const newType = input.type === 'password' ? 'text' : 'password';
            input.type = newType;

            toggle.innerHTML = newType === 'password'
                ? '<i class="fa-regular fa-eye"></i>'
                : '<i class="fa-regular fa-eye-slash"></i>';
        });
    });
});
