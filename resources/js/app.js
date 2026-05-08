const body = document.body;

if (body.classList.contains('frontpage')) {
    const toggleFooter = () => {
        const scrollPosition = window.scrollY + window.innerHeight;
        const pageBottom = document.documentElement.scrollHeight - 40;
        body.classList.toggle('show-footer', scrollPosition >= pageBottom);
        body.classList.toggle('hide-hero-bg', window.scrollY > 80);
    };

    window.addEventListener('scroll', toggleFooter, { passive: true });
    window.addEventListener('resize', toggleFooter);
    toggleFooter();
}
