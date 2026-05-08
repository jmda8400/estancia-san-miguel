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
<div class="frontpage-hero-bg" aria-hidden="true"></div>
@endif
<header class="app-shell app-fixed-header border-b app-shell-transparent">
    <nav class="mx-auto max-w-7xl w-full px-4 py-3 flex items-center justify-between gap-4 flex-wrap text-sm">
        <div class="flex w-full items-center justify-between gap-4 flex-wrap">
            <a href="/" aria-label="Volver a la Frontpage"><img src="/logo.svg" alt="Estancia San Miguel" class="brand-logo" loading="eager" decoding="async"></a>
            @if (request()->path() === '/')
                <div class="front-header-links">
                    <a class="app-btn" href="#hosteria">Hostería</a>
                    <a class="app-btn" href="#habitaciones">Habitaciones</a>
                    <a class="app-btn" href="#cabanas">Cabañas</a>
                    <a class="app-btn" href="#gastronomia">Gastronomía</a>
                    <a class="app-btn" href="#servicios">Servicios & Actividades</a>
                    <a class="app-btn" href="#contacto">Contacto</a>
                </div>
            @elseif (request()->is('login'))
                <span class="font-semibold">Acceso interno</span>
            @else
                <div class="internal-header-links">
                    <a class="app-btn" href="/">Frontpage</a>
                    <a class="app-btn" href="/stock">Stock</a>
                    <a class="app-btn" href="/comandas">Comandas</a>
                    <a class="app-btn" href="/admin">Administración</a>
                </div>
            @endif
        </div>
    </nav>
</header>
<main class="mx-auto max-w-7xl w-full px-4 py-6 flex-1 app-main-content">
    @yield('content')
</main>
<footer id="page-footer" class="app-shell app-fixed-footer border-t text-sm py-4">
    <div class="mx-auto max-w-7xl px-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <img src="/logo_footer.svg" alt="Logo footer Estancia San Miguel" class="h-8 w-auto">
            <span>© {{ date('Y') }} Estancia San Miguel</span>
        </div>
        <div class="flex items-center gap-4 text-lg">
            <a href="#" title="WhatsApp" aria-label="WhatsApp" class="social-logo whatsapp-logo">WA</a>
            <a href="#" title="Instagram" aria-label="Instagram" class="social-logo instagram-logo">IG</a>
        </div>
    </div>
</footer>
<a href="https://wa.me/5490000000000" class="floating-whatsapp" aria-label="Contactar por WhatsApp" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 0 0-8.66 15l-1.1 4 4.1-1.08A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.06-1.11l-.29-.17-2.43.64.65-2.37-.19-.3A8 8 0 1 1 12 20Zm4.24-5.7c-.23-.11-1.37-.67-1.58-.75s-.37-.11-.52.11-.6.75-.74.9-.28.17-.52.06a6.6 6.6 0 0 1-1.94-1.2 7.38 7.38 0 0 1-1.37-1.7c-.14-.24 0-.37.1-.49.1-.1.23-.27.35-.4a1.5 1.5 0 0 0 .23-.38.43.43 0 0 0 0-.41c-.06-.11-.52-1.26-.71-1.73-.19-.45-.38-.39-.52-.4h-.44a.85.85 0 0 0-.61.29 2.55 2.55 0 0 0-.8 1.89 4.43 4.43 0 0 0 .93 2.32 10.09 10.09 0 0 0 3.88 3.43 13.2 13.2 0 0 0 1.3.48 3.15 3.15 0 0 0 1.45.09 2.38 2.38 0 0 0 1.56-1.1 1.94 1.94 0 0 0 .13-1.1c-.05-.09-.21-.14-.44-.26Z"/></svg>
</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@yield('scripts')
</body>
</html>
