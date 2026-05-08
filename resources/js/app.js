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

    const frontpageRoot = document.getElementById('frontpageRoot');
    const updateHeroOpacity = () => {
        if (!frontpageRoot) return;
        const fadeDistance = window.innerHeight * 0.85;
        const progress = Math.min(window.scrollY / fadeDistance, 1);
        frontpageRoot.style.setProperty('--hero-opacity', String(1 - progress));
    };

    const toggleHeaderAndFooter = () => {
        const currentScrollY = window.scrollY;
        const isScrollingDown = currentScrollY > lastScrollY;
        const nearBottom = window.innerHeight + currentScrollY >= document.documentElement.scrollHeight - 8;

        body.classList.toggle('header-visible', currentScrollY > 20);
        body.classList.toggle('show-footer', nearBottom && isScrollingDown);

        lastScrollY = currentScrollY;
    };

    window.addEventListener('scroll', () => {
        toggleHeaderAndFooter();
        updateHeroOpacity();
    }, { passive: true });
    toggleHeaderAndFooter();
    updateHeroOpacity();
}
