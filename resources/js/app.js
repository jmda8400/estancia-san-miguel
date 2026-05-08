const body = document.body;

const toggleFooter = () => {
    const scrollPosition = window.scrollY + window.innerHeight;
    const pageBottom = document.documentElement.scrollHeight - 40;
    body.classList.toggle('show-footer', scrollPosition >= pageBottom);

    if (body.classList.contains('frontpage')) {
        body.classList.toggle('hide-hero-bg', window.scrollY > 80);
        body.classList.toggle('reveal-content', window.scrollY > 40);
    }
};

window.addEventListener('scroll', toggleFooter, { passive: true });
window.addEventListener('resize', toggleFooter);
toggleFooter();
