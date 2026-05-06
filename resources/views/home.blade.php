@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="hotel-landing">
    <section class="hotel-hero app-card">
        <span class="front-badge">Patagonia · Experiencia Premium</span>
        <h1 class="text-4xl md:text-5xl font-semibold mb-3">Estancia San Miguel</h1>
        <p class="text-lg md:text-xl max-w-3xl">Un refugio de montaña inspirado en hospitalidad boutique: naturaleza, calidez y diseño para descansar en cualquier estación.</p>
    </section>

    <section class="front-grid">
        <article id="hosteria" class="front-split">
            <h2 class="front-block-title">Hostería</h2>
            <p>Lobby cálido, arquitectura de materiales nobles y atención personalizada durante toda la estadía.</p>
        </article>
        <article id="habitaciones" class="front-split">
            <h2 class="front-block-title">Habitaciones</h2>
            <p>Suites amplias con vistas abiertas, ropa de cama premium y detalles pensados para descanso profundo.</p>
        </article>
        <article id="cabanas" class="front-split">
            <h2 class="front-block-title">Cabañas</h2>
            <p>Privacidad y confort para familias o grupos, con cocina equipada, living y conexión con el paisaje.</p>
        </article>
        <article id="gastronomia" class="front-split">
            <h2 class="front-block-title">Gastronomía</h2>
            <p>Cocina de estación con productos locales, panificados artesanales y menú de autor.</p>
        </article>
        <article id="servicios" class="front-split md:col-span-2">
            <h2 class="front-block-title">Servicios & Actividades</h2>
            <p>Spa, caminatas guiadas, experiencias al aire libre y propuestas para disfrutar todo el mes.</p>
        </article>
        <article id="contacto" class="front-split md:col-span-2">
            <h2 class="front-block-title">Contacto</h2>
            <p>Reservas y consultas por WhatsApp, email o redes. Respuesta rápida para organizar tu estadía ideal.</p>
        </article>
    </section>
</div>
@endsection
