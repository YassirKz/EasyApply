import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const darkMode = localStorage.getItem('darkMode') === 'true';
    html.classList.toggle('dark', darkMode);
});

Alpine.start();
