window.tailwind = window.tailwind || {};
window.tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: '#137fec',
                'background-light': '#f6f7f8',
                'background-dark': '#101922',
            },
            fontFamily: {
                display: ['Manrope', 'sans-serif'],
            },
            borderRadius: {
                DEFAULT: '0.25rem',
                lg: '0.5rem',
                xl: '0.75rem',
                full: '9999px',
            },
        },
    },
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const targetId = button.getAttribute('data-password-toggle');
        if (!targetId) {
            return;
        }

        const input = document.getElementById(targetId);
        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const icon = button.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            }
        });
    });
});
