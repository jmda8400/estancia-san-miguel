@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div id="frontpageRoot" class="frontpage-simple">
    <div id="frontpagePreloader" class="frontpage-preloader" aria-hidden="true">
        <img src="/logo_2.png" alt="Estancia San Miguel" class="frontpage-preloader-logo" loading="eager" decoding="async">
    </div>

    <section id="inicio" class="front-hero">
        <img src="/picture_hero.png" alt="Vista panorámica de Estancia San Miguel" class="front-hero-image" loading="eager" decoding="async">
        <div class="front-hero-overlay"></div>
        <div class="front-hero-content">
            <img src="/logo.png" alt="Logo Estancia San Miguel" class="front-hero-logo" loading="eager" decoding="async">
            <h1>Estancia San Miguel</h1>
            <p>El Casco</p>
        </div>
    </section>
    <div class="front-section-separator"><img src="/picture_7.png" alt="Separador visual"></div>

    <section id="estancia" class="front-section-block front-section-row">
        <div class="front-section-media">
            <img src="/picture_2.png" alt="La Estancia">
        </div>
        <div class="front-section-content">
            <h2><span>01</span> La Estancia</h2>
            <p>La Estancia está ubicada en el Paraje de San Miguel de los Rios, dentro del municipio de Villa Yacanto, en el corazón del Valle de Calamuchita rodeada de sierras, en medio  de una añosa arboleda de bosques de distintas especies. A la vera del parque corre el río Tabaquillos a lo largo de 500 m, con acceso a una playa de arena con aguas cristalinas. Se encuentra a 8 km del pueblo, al pie del Cerro Champaquí. Cuenta con 7 habitaciones con estilo de montaña con baño privado. En el salón podrán disfrutar de un delicioso desayuno con panes y dulces caseros. La atención es personalizada y se cuida cada detalle para que todos los huéspedes disfruten de una estadía placentera.</p>
            <a href="#" class="front-internal-btn">Ver más</a>
        </div>
    </section>
    <div class="front-section-separator"><img src="/picture_8.jpg" alt="Separador visual"></div>

    <section id="gastronomia" class="front-section-block front-section-row front-section-row-reverse">
        <div class="front-section-media">
            <img src="/picture_3.png" alt="Gastronomía">
        </div>
        <div class="front-section-content">
            <h2><span>02</span> Gastronomía</h2>
            <p>La gastronomía de nuestra Hostería está especialmente pensada en los huéspedes que nos visitan, desde el desayuno servido con panes y dulces caseros al almuerzo y la cena. Como parte de nuestra sustentabilidad ponemos en valor los productos locales y originales de esta zona, por eso en nuestros platos se encuentran productos como jabalí, ciervo, zapallo, maíz, algarrobo, quinoa, zarzamora, higos, frutas secas entre otros productos locales y de los pueblos originarios combinados en recetas originales y tradicionales. En el menú siempre encontrarás un plato con productos regionales y otro plato saludable para satisfacer todos los gustos. La cava cuenta con una importante variedad de vinos de distintas bodegas para maridar los diferentes platos ofrecidos en el menú.</p>
            <a href="#" class="front-internal-btn">Ver más</a>
        </div>
    </section>
    <div class="front-section-separator"><img src="/picture_9.jpg" alt="Separador visual"></div>

    <section id="sustentabilidad" class="front-section-block front-section-row">
        <div class="front-section-media">
            <img src="/picture_4.png" alt="Sustentabilidad">
        </div>
        <div class="front-section-content">
            <h2><span>03</span> Sustentabilidad</h2>
            <p>Desde que conocimos este maravilloso lugar en 2016, entendimos lo importante que era protegerlo y por eso decidimos adoptar prácticas sustentables.</p>
            <p>¿Qué es para nosotros ser sustentable? Cuidar los recursos naturales, utilizar energía solar, reducir, reciclar o reutilizar nuestros residuos, reducir la huella de carbono, poner en valor nuestro entorno cultural y natural. Mirá nuestra política de calidad y gestión sustentable;¡Observá que es posible vivir en armonía con la naturaleza!</p>
            <a href="#" class="front-internal-btn">Ver más</a>
        </div>
    </section>
    <div class="front-section-separator"><img src="/picture_10.jpg" alt="Separador visual"></div>

    <section id="actividades" class="front-section-block front-section-row front-section-row-reverse">
        <div class="front-section-media">
            <img src="/picture_5.png" alt="Actividades">
        </div>
        <div class="front-section-content">
            <h2><span>04</span> Actividades</h2>
            <p>Caminatas, cabalgatas y propuestas al aire libre para disfrutar cada momento.</p>
            <a href="#" class="front-internal-btn">Ver más</a>
        </div>
    </section>
    <div class="front-section-separator"><img src="/picture_11.jpg" alt="Separador visual"></div>

    <section id="reservar" class="front-section-block front-section-row">
        <div class="front-section-media">
            <img src="/picture_6.png" alt="Reservar">
        </div>
        <div class="front-section-content">
            <h2><span>05</span> Reservar</h2>
            <p>Coordiná tu estadía y viví la experiencia Estancia San Miguel.</p>
            <a href="#" class="front-internal-btn">Reservar</a>
        </div>
    </section>

    <div class="front-footer-separator" aria-hidden="true"></div>
    <footer class="frontpage-footer">
        <div class="frontpage-footer-inner">2026 - El Casco, Estancia San Miguel</div>
    </footer>

    <a href="https://wa.me/5490000000000" class="floating-whatsapp" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.5 0 .15 5.35.15 11.93c0 2.1.55 4.16 1.6 5.98L0 24l6.27-1.64a11.93 11.93 0 0 0 5.8 1.48h.01c6.57 0 11.92-5.35 11.92-11.92a11.84 11.84 0 0 0-3.48-8.44ZM12.08 21.8h-.01a9.88 9.88 0 0 1-5.03-1.37l-.36-.21-3.72.97 1-3.62-.24-.37a9.84 9.84 0 0 1-1.52-5.27c0-5.45 4.43-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.89a9.81 9.81 0 0 1 2.9 6.99c0 5.45-4.44 9.88-9.9 9.88Zm5.42-7.42c-.3-.15-1.78-.88-2.06-.98-.27-.1-.47-.15-.67.15-.2.3-.77.98-.95 1.19-.17.2-.35.22-.65.08-.3-.15-1.27-.47-2.42-1.5a9.04 9.04 0 0 1-1.67-2.08c-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.03-.53-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.08-.8.38-.28.3-1.05 1.03-1.05 2.5 0 1.48 1.08 2.91 1.23 3.11.15.2 2.12 3.25 5.14 4.56.72.31 1.28.5 1.72.64.72.23 1.38.2 1.9.12.58-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.17-1.43-.08-.12-.28-.2-.58-.35Z"/>
        </svg>
    </a>

</div>
@endsection
