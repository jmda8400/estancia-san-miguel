@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="frontpage-modern">
    <section id="inicio" class="relative min-h-[88vh] overflow-hidden rounded-3xl border border-stone-200/70">
        {{-- Reemplazar /picture_hero.png por imagen final del hero --}}
        <img src="/picture_hero.png" alt="Vista panorámica de Estancia San Miguel" class="absolute inset-0 h-full w-full object-cover" loading="eager" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/35 to-black/55"></div>

        <div class="relative z-20 flex min-h-[88vh] flex-col">
            <header class="px-6 pt-6 md:px-10 lg:px-14">
                <nav class="rounded-2xl border border-white/25 bg-white/85 px-4 py-3 shadow-xl backdrop-blur md:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="#inicio" class="inline-flex items-center gap-3 text-stone-900 font-semibold">
                            <img src="/logo_3.png" alt="Estancia San Miguel" class="h-11 w-auto" loading="eager" decoding="async">
                        </a>
                        <div class="flex flex-wrap items-center gap-2 text-sm md:gap-3">
                            <a href="#estancia" class="front-nav-link">La Estancia</a>
                            <a href="#footer" class="front-nav-link">Contacto</a>
                        </div>
                    </div>
                </nav>
            </header>

            <div class="mx-auto flex w-full max-w-6xl flex-1 items-center px-6 pb-16 pt-8 md:px-10 lg:px-14">
                <div class="max-w-2xl text-stone-50">
                    <p class="mb-4 text-sm uppercase tracking-[0.24em] text-stone-100/90">El Casco</p>
                    <h1 class="text-4xl font-semibold leading-tight md:text-6xl">Estancia San Miguel</h1>
                    <p class="mt-4 text-lg text-stone-100/95 md:text-xl">Diseñada para vivir el campo con calma, naturaleza y hospitalidad auténtica.</p>
                    <a href="#estancia" class="mt-7 inline-flex rounded-full bg-amber-100 px-7 py-3 text-base font-semibold text-stone-900 shadow-lg transition hover:bg-amber-50">Descubrir la estancia</a>
                </div>
            </div>

            <aside class="relative z-20 mx-6 mb-6 md:mx-10 lg:mx-14">
                <div class="grid gap-3 rounded-2xl border border-white/30 bg-black/35 p-4 text-sm text-stone-100 backdrop-blur md:grid-cols-4">
                    <div class="front-quick-item">Restaurante</div>
                    <div class="front-quick-item">Sobre la Hosteria</div>
                    <div class="front-quick-item">Sustentabilidad</div>
                    <div class="front-quick-item">Actividades</div>
                </div>
            </aside>
        </div>
    </section>

    <section id="estancia" class="py-16 md:py-20">
        <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
            <article>
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-600">Sección 01</p>
                <h2 class="text-3xl font-semibold text-stone-900 md:text-4xl">La Estancia</h2>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-stone-700 md:text-lg">Entre montaña y campo, Estancia San Miguel ofrece una experiencia íntima de descanso, sabores regionales y conexión con el paisaje natural. Cada detalle combina elegancia cálida con el ritmo sereno de la vida serrana.</p>
            </article>
            <figure class="overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 shadow-sm">
                {{-- Reemplazar /picture_6.png por imagen final de la sección La Estancia --}}
                <img src="/picture_6.png" alt="Paisaje de Estancia San Miguel" class="h-full min-h-[320px] w-full object-cover" loading="lazy" decoding="async">
            </figure>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <article class="front-feature-card">
                <h3>Paisaje natural</h3>
                <p>Sierras, bosque nativo y río para reconectar con la naturaleza.</p>
            </article>
            <article class="front-feature-card">
                <h3>Atención personalizada</h3>
                <p>Hospitalidad cercana, servicios cuidados y estadías a medida.</p>
            </article>
            <article class="front-feature-card">
                <h3>Experiencia de campo</h3>
                <p>Ritmo pausado, actividades al aire libre y esencia rural premium.</p>
            </article>
        </div>
    </section>
</div>
@endsection
