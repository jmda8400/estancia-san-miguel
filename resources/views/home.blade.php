@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="hotel-landing">
    <section class="hotel-hero app-card">
        <h1 class="text-4xl font-semibold mb-3">Estancia San Miguel</h1>
        <p class="text-lg">Un refugio de montaña diseñado para el descanso, la naturaleza y la experiencia patagónica.</p>
    </section>

    @php
        $lorem = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer luctus magna sed placerat fermentum.',
            'Vivamus non sem eros. Aenean ultrices magna ut dictum congue, lorem justo eleifend tortor.',
            'Suspendisse potenti. Quisque placerat, dolor ut tempus feugiat, est est dictum libero, non imperdiet nisi purus a odio.',
            'Mauris quis consequat velit. Integer nec lacus sodales, aliquet nisl quis, dictum urna.',
            'Donec viverra orci vitae malesuada ultrices. Curabitur et sem in velit facilisis congue.'
        ];
    @endphp

    <section id="hosteria" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Hostería</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
    <section id="habitaciones" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Habitaciones</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
    <section id="cabanas" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Cabañas</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
    <section id="gastronomia" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Gastronomía</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
    <section id="servicios" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Servicios & Actividades</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
    <section id="contacto" class="app-card">
        <h2 class="text-2xl font-semibold mb-4">Contacto</h2>
        @foreach ($lorem as $paragraph) <p class="mb-3">{{ $paragraph }}</p> @endforeach
    </section>
</div>
@endsection
