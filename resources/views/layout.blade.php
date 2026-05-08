<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col {{ request()->path() === '/' ? 'frontpage' : 'internal-page' }}">
@if (request()->path() === '/')
@endif
<header class="app-shell app-fixed-header border-b app-shell-transparent">
    <nav class="mx-auto max-w-[78rem] w-full px-4 py-2 flex items-center justify-between gap-3 flex-wrap text-sm">
            <a href="/" aria-label="Volver a la Frontpage"><img src="/logo.png" alt="Estancia San Miguel" class="brand-logo" loading="eager" decoding="async"></a>
            @if (request()->path() === '/')
                <div class="front-header-links">
                    <a class="app-btn app-nav-btn" href="#estancia">La Estancia</a>
                    <a class="app-btn app-nav-btn" href="#gastronomia">Gastronomia</a>
                    <a class="app-btn app-nav-btn" href="#sustentabilidad">Sustentabilidad</a>
                    <a class="app-btn app-nav-btn" href="#actividades">Actividades</a>
                    <a class="app-btn app-nav-btn" href="#reservar">Reservar</a>
                </div>
            @elseif (request()->is('login'))
                <span class="font-semibold">Acceso interno</span>
            @else
                <div class="internal-header-links app-nav-wrap">
                    <a class="app-btn app-nav-btn {{ request()->is('stock*') ? 'app-nav-btn-active' : '' }}" href="/stock">Stock</a>
                    <a class="app-btn app-nav-btn {{ request()->is('comandas*') ? 'app-nav-btn-active' : '' }}" href="/comandas">Comandas</a>
                    <a class="app-btn app-nav-btn {{ request()->is('admin*') ? 'app-nav-btn-active' : '' }}" href="/admin">Administración</a>
                </div>
            @endif
    </nav>
</header>
<main class="{{ request()->path() === '/' ? 'w-full p-0' : 'mx-auto max-w-[78rem] w-full px-4 py-4' }} flex-1 app-main-content">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@yield('scripts')
</body>
</html>
