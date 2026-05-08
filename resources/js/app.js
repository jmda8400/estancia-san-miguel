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

    const toggleHeader = () => {
        body.classList.toggle('header-visible', window.scrollY > 20);
    };

    window.addEventListener('scroll', toggleHeader, { passive: true });
    toggleHeader();
}
