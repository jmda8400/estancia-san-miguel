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

    <section id="estancia" class="front-section-block">
        <h2><span>01</span> La Estancia</h2>
        <p>Tradición, calma y paisaje serrano en una experiencia de campo auténtica.</p>
        <a href="#" class="front-internal-btn">Ver más</a>
    </section>
    <div class="front-separator"><img src="/picture_1.png" alt="Separador"></div>

    <section id="gastronomia" class="front-section-block">
        <h2><span>02</span> Gastronomía</h2>
        <p>Sabores regionales y cocina de estación con identidad local.</p>
        <a href="#" class="front-internal-btn">Ver más</a>
    </section>
    <div class="front-separator"><img src="/picture_2.png" alt="Separador"></div>

    <section id="sustentabilidad" class="front-section-block">
        <h2><span>03</span> Sustentabilidad</h2>
        <p>Compromiso con el entorno natural, el uso responsable de recursos y la comunidad.</p>
        <a href="#" class="front-internal-btn">Ver más</a>
    </section>
    <div class="front-separator"><img src="/picture_3.png" alt="Separador"></div>

    <section id="actividades" class="front-section-block">
        <h2><span>04</span> Actividades</h2>
        <p>Caminatas, cabalgatas y propuestas al aire libre para disfrutar cada momento.</p>
        <a href="#" class="front-internal-btn">Ver más</a>
    </section>
    <div class="front-separator"><img src="/picture_4.png" alt="Separador"></div>

    <section id="reservar" class="front-section-block">
        <h2><span>05</span> Reservar</h2>
        <p>Coordiná tu estadía y viví la experiencia Estancia San Miguel.</p>
        <a href="#" class="front-internal-btn">Reservar</a>
    </section>
</div>
@endsection
