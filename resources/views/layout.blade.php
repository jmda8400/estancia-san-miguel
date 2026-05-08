<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col app-has-fixed-bars {{ request()->path() === '/' ? 'frontpage' : 'internal-page' }}">
@if (request()->path() === '/')
@endif
<header class="app-shell app-fixed-header border-b app-shell-transparent">
    <nav class="mx-auto max-w-7xl w-full px-4 py-3 flex items-center justify-between gap-4 flex-wrap text-sm">
            <a href="/" aria-label="Volver a la Frontpage"><img src="/logo_2.png" alt="Estancia San Miguel" class="brand-logo" loading="eager" decoding="async"></a>
            @if (request()->path() === '/')
                <div class="front-header-links">
                    <a class="app-btn app-nav-btn" href="#estancia">01 La Estancia</a>
                    <a class="app-btn app-nav-btn" href="#gastronomia">02 Gastronomia</a>
                    <a class="app-btn app-nav-btn" href="#sustentabilidad">03 Sustentabilidad</a>
                    <a class="app-btn app-nav-btn" href="#actividades">04 Actividades</a>
                    <a class="app-btn app-nav-btn" href="#reservar">05 Reservar</a>
                </div>
            @elseif (request()->is('login'))
                <span class="font-semibold">Acceso interno</span>
            @else
                <div class="internal-header-links">
                    <a class="app-btn app-nav-btn" href="/">Frontpage</a>
                    <a class="app-btn app-nav-btn" href="/stock">Stock</a>
                    <a class="app-btn app-nav-btn" href="/comandas">Comandas</a>
                    <a class="app-btn app-nav-btn" href="/admin">Administración</a>
                </div>
            @endif
    </nav>
</header>
<main class="{{ request()->path() === '/' ? 'w-full p-0' : 'mx-auto max-w-7xl w-full px-4 py-6' }} flex-1 app-main-content">
    @yield('content')
</main>
@if (request()->path() !== '/')
<footer id="footer" class="app-shell app-fixed-footer border-t text-sm py-3 app-shell-transparent">
    <div class="mx-auto max-w-7xl px-4 flex items-center justify-center gap-3 footer-legend">
        <span>2026 - El Casco, Estancia San Miguel</span>
    </div>
</footer>
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@yield('scripts')
</body>
</html>
