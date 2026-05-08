@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="hotel-landing">
    <section class="hotel-hero app-card">
        <p class="front-kicker">Bienvenidos a la sierra</p>
        <h1 class="text-4xl md:text-5xl font-semibold mb-3">El Casco - Estancia San Miguel</h1>
        <p class="text-lg md:text-xl max-w-3xl">Hostería de montaña al pie del Champaquí, rodeada de bosque nativo, río y experiencias auténticas.</p>
        <div class="front-hero-cta">
            <a href="#hosteria" class="app-btn app-btn-active">Conocer la hostería</a>
            <a href="#servicios" class="app-btn">Ver servicios</a>
        </div>
    </section>

    <section class="front-highlights">
        <article class="front-highlight-card">
            <h3>Naturaleza inmersiva</h3>
            <p>500 metros de río, bosque nativo y senderos para reconectar con el entorno serrano.</p>
        </article>
        <article class="front-highlight-card">
            <h3>Descanso con identidad</h3>
            <p>Habitaciones de estilo de montaña y atención personalizada para una estadía serena.</p>
        </article>
        <article class="front-highlight-card">
            <h3>Cocina regional</h3>
            <p>Sabores locales, productos de estación y recetas que cuentan la historia de la zona.</p>
        </article>
    </section>

    <section id="hosteria" class="front-section front-section-split">
        <figure class="front-section-photo">
            <img src="/picture_2.png" alt="Entorno natural de la hostería" loading="lazy" decoding="async">
        </figure>
        <article class="front-section-copy front-split">
            <h2 class="front-block-title">Sobre la Hostería</h2>
            <p>La hostería está ubicada en el Paraje de San Miguel de los Ríos, dentro del municipio de Villa Yacanto, rodeada de sierras y una añosa arboleda de distintas especies.</p>
            <p>A la vera del parque corre el río Tabaquillos con acceso a playa de arena y aguas cristalinas. Estamos a 8 km del pueblo, al pie del Cerro Champaquí.</p>
            <p>Contamos con 7 habitaciones con baño privado, desayunos con panes y dulces caseros y un servicio dedicado a cada huésped.</p>
        </article>
    </section>

    <section class="front-divider" aria-label="Paisaje de San Miguel">
        <img src="/picture_3.png" alt="Bosques y paisaje serrano" loading="lazy" decoding="async">
    </section>

    <section id="sustentabilidad" class="front-section front-section-split front-section-reverse">
        <article class="front-section-copy front-split">
            <h2 class="front-block-title">Sustentabilidad activa</h2>
            <p>Desde 2016 trabajamos para preservar este entorno con decisiones diarias y medibles.</p>
            <p>Implementamos energía solar, reducción y reutilización de residuos y prácticas para disminuir la huella de carbono.</p>
            <p>También promovemos la valoración cultural y natural del territorio en cada experiencia ofrecida.</p>
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
            <h2 class="front-block-title">Gastronomía de origen</h2>
            <p>La propuesta gastronómica está pensada para huéspedes que buscan sabores locales desde el desayuno hasta la cena.</p>
            <p>Trabajamos con productos regionales como zapallo, maíz, quinoa, frutos secos y carnes de la zona para crear platos auténticos.</p>
            <p>El menú integra opciones regionales y saludables, acompañado por una cava seleccionada para cada maridaje.</p>
        </article>
    </section>

    <section class="front-divider" aria-label="Atardecer en Estancia San Miguel">
        <img src="/picture_7.png" alt="Atardecer en Estancia San Miguel" loading="lazy" decoding="async">
    </section>

    <section id="servicios" class="front-section">
        <article class="front-split">
            <h2 class="front-block-title">Servicios y actividades</h2>
            <ul class="front-services-list">
                <li>Desayuno con productos caseros</li>
                <li>Blanco de habitación</li>
                <li>Trekking libre y guiado a las Tres Cascadas</li>
                <li>Libros y juegos de mesa</li>
                <li>Visita a huerta y criadero</li>
                <li>Espacios de contemplación y oración</li>
            </ul>
        </article>
    </section>
</div>
@endsection
