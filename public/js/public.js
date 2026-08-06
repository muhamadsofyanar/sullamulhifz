(() => {
    const header = document.querySelector('[data-public-header]');
    const button = document.querySelector('[data-public-menu]');
    const nav = document.querySelector('[data-public-nav]');

    const updateHeader = () => header?.classList.toggle('scrolled', window.scrollY > 8);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    button?.addEventListener('click', () => {
        const open = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!open));
        nav?.classList.toggle('open', !open);
    });

    nav?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            button?.setAttribute('aria-expanded', 'false');
            nav?.classList.remove('open');
        });
    });
})();
