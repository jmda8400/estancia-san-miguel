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
}
