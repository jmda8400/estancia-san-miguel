const body = document.body;

if (body.classList.contains('frontpage')) {
    const onReady = () => {
        body.classList.add('page-loaded');
    };

    if (document.readyState === 'complete') {
        onReady();
    } else {
        window.addEventListener('load', onReady);
    }

    let lastScrollY = window.scrollY;

    const toggleHeaderAndFooter = () => {
        const currentScrollY = window.scrollY;
        const isScrollingDown = currentScrollY > lastScrollY;
        const nearBottom = window.innerHeight + currentScrollY >= document.documentElement.scrollHeight - 8;

        body.classList.toggle('header-visible', currentScrollY > 80);
        body.classList.toggle('show-footer', nearBottom && isScrollingDown);

        lastScrollY = currentScrollY;
    };

    window.addEventListener('scroll', toggleHeaderAndFooter, { passive: true });
    toggleHeaderAndFooter();

    const menuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const menuClose = document.querySelector('[data-mobile-menu-close]');
    const menuOverlay = document.querySelector('[data-mobile-overlay]');
    const menuDrawer = document.getElementById('mobileDrawer');
    const menuLinks = menuDrawer ? menuDrawer.querySelectorAll('a') : [];

    const openMobileMenu = () => {
        body.classList.add('mobile-menu-open');
        menuToggle?.setAttribute('aria-expanded', 'true');
        menuDrawer?.setAttribute('aria-hidden', 'false');
        if (menuOverlay) menuOverlay.hidden = false;
    };

    const closeMobileMenu = () => {
        body.classList.remove('mobile-menu-open');
        menuToggle?.setAttribute('aria-expanded', 'false');
        menuDrawer?.setAttribute('aria-hidden', 'true');
        if (menuOverlay) menuOverlay.hidden = true;
    };

    menuToggle?.addEventListener('click', () => {
        if (body.classList.contains('mobile-menu-open')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });

    menuClose?.addEventListener('click', closeMobileMenu);
    menuOverlay?.addEventListener('click', closeMobileMenu);
    menuLinks.forEach((link) => link.addEventListener('click', closeMobileMenu));

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    });

}
