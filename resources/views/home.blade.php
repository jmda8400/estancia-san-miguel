@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="hotel-landing">
    <section class="hotel-hero app-card">
        <span class="front-badge">Valle de Calamuchita · Naturaleza viva</span>
        <h1 class="text-4xl md:text-5xl font-semibold mb-3">Estancia San Miguel</h1>
        <p class="text-lg md:text-xl max-w-3xl">Hostería de montaña al pie del Champaquí, rodeada de bosque nativo, río y experiencias auténticas.</p>
    </section>

    <section id="hosteria" class="front-section front-section-split">
        <figure class="front-section-photo">
            <img src="/picture_2.png" alt="Entorno natural de la hostería" loading="lazy" decoding="async">
        </figure>
        <article class="front-section-copy front-split">
            <h2 class="front-block-title">Sobre la Hostería</h2>
            <p>La hostería está ubicada en el Paraje de San Miguel de los Rios, dentro del municipio de Villa Yacanto, en el corazón del Valle de Calamuchita rodeada de sierras, en medio de una añosa arboleda de bosques de distintas especies. A la vera del parque corre el río Tabaquillos a lo largo de 500 m, con acceso a una playa de arena con aguas cristalinas.</p>
            <p>Se encuentra a 8 km del pueblo, al pie del Cerro Champaquí. Cuenta con 7 habitaciones con estilo de montaña con baño privado. En el salón podrán disfrutar de un delicioso desayuno con panes y dulces caseros. La atención es personalizada y se cuida cada detalle para que todos los huéspedes disfruten de una estadía placentera.</p>
        </article>
    </section>

    <section class="front-divider" aria-label="Paisaje de San Miguel">
        <img src="/picture_3.png" alt="Bosques y paisaje serrano" loading="lazy" decoding="async">
    </section>

    <section id="sustentabilidad" class="front-section front-section-split front-section-reverse">
        <article class="front-section-copy front-split">
            <h2 class="front-block-title">Sustentabilidad</h2>
            <p>Desde que conocimos este maravilloso lugar en 2016, entendimos lo importante que era protegerlo y por eso decidimos adoptar prácticas sustentables.</p>
            <p>¿Qué es para nosotros ser sustentable? Cuidar los recursos naturales, utilizar energía solar, reducir, reciclar o reutilizar nuestros residuos, reducir la huella de carbono, poner en valor nuestro entorno cultural y natural.</p>
            <p>Mirá nuestra política de calidad y gestión sustentable; ¡observá que es posible vivir en armonía con la naturaleza!</p>
        </article>
        <figure class="front-section-photo">
            <img src="/picture_4.png" alt="Prácticas sustentables en la hostería" loading="lazy" decoding="async">
        </figure>
    </section>

    <section class="front-divider" aria-label="Vida natural y entorno cultural">
        <img src="/picture_5.png" alt="Rincones naturales del predio" loading="lazy" decoding="async">
    </section>

    <section id="gastronomia" class="front-section front-section-split">
        <figure class="front-section-photo">
            <img src="/picture_6.png" alt="Propuesta gastronómica regional" loading="lazy" decoding="async">
        </figure>
        <article class="front-section-copy front-split">
            <h2 class="front-block-title">Gastronomía</h2>
            <p>La gastronomía de nuestra Hostería está especialmente pensada en los huéspedes que nos visitan, desde el desayuno servido con panes y dulces caseros al almuerzo y la cena. Como parte de nuestra sustentabilidad ponemos en valor los productos locales y originales de esta zona.</p>
            <p>En nuestros platos se encuentran productos como jabalí, ciervo, zapallo, maíz, algarrobo, quinoa, zarzamora, higos y frutas secas, entre otros productos locales y de los pueblos originarios combinados en recetas originales y tradicionales.</p>
            <p>En el menú siempre encontrarás un plato con productos regionales y otro plato saludable para satisfacer todos los gustos. La cava cuenta con una importante variedad de vinos de distintas bodegas para maridar los diferentes platos ofrecidos en el menú.</p>
        </article>
    </section>

    <section class="front-divider" aria-label="Atardecer en Estancia San Miguel">
        <img src="/picture_7.png" alt="Atardecer en Estancia San Miguel" loading="lazy" decoding="async">
    </section>

    <section id="servicios" class="front-section">
        <article class="front-split">
            <h2 class="front-block-title">Estancia San Miguel</h2>
            <h3 class="front-subtitle">Servicios y actividades</h3>
            <h4 class="front-subtitle-small">Servicios</h4>
            <ul class="front-services-list">
                <li>Desayuno</li>
                <li>Blanco de habitación</li>
                <li>Trekking libre</li>
                <li>Trekking a las Tres cascadas revalorizando el valor cultural y natural del lugar</li>
                <li>Libros</li>
                <li>Juegos de mesa</li>
                <li>Visita a la huerta</li>
                <li>Visita al criadero de jabalí</li>
                <li>Lugar de oración</li>
            </ul>
        </article>
    </section>
</div>
@endsection
